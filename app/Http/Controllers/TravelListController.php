<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Travel_Lists;
use App\Models\Transportation;
use App\Models\Faculty;
use App\Models\TravelRequestParty;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class TravelListController extends Controller
{
    public function index(Request $request)
    {
        $query = Travel_Lists::with(['transportation', 'requestParties']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('requestParties', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $travel_lists = $query->get();
        $totalTravelOrders = Travel_Lists::count();
        $transportations = Transportation::all();
        $faculties = Faculty::all();

        $userTravelCount = 0;
        if (Auth::check()) {
            $user = Auth::user();
            $userTravelCount = Travel_Lists::whereHas('requestParties', function ($q) use ($user) {
                $q->where('name', $user->name);
            })->count();
        }

        return view('travellist.travellist', compact(
            'travel_lists',
            'transportations',
            'totalTravelOrders',
            'faculties',
            'userTravelCount'
        ));
    }

    public function create()
    {
        $transportations = Transportation::all();
        $faculties = Faculty::all();
        return view('travellist.create', compact('transportations', 'faculties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'travel_date' => 'required|date',
            'request_parties' => 'required|string',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            // 'conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time',
            'transportation_id' => 'required|exists:transportations,id',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $travel = Travel_Lists::create([
            'travel_date' => $validated['travel_date'],
            'purpose' => $validated['purpose'],
            'destination' => $validated['destination'],
            'conditionalities' => null,
            'transportation_id' => $validated['transportation_id'],
            'faculty_id' => $validated['faculty_id'],
            'status' => 'Pending',
        ]);

        $names = preg_split('/\r\n|\r|\n/', trim($validated['request_parties']));
        foreach ($names as $name) {
            if (!empty(trim($name))) {
                TravelRequestParty::create([
                    'travel_list_id' => $travel->id,
                    'name' => trim($name),
                ]);
            }
        }

        return redirect()->route('travellist.index')->with('success', 'Travel list created successfully.');
    }

    public function edit($id)
    {
        $travel_list = Travel_Lists::with('requestParties')->findOrFail($id);
        $transportations = Transportation::all();
        $faculties = Faculty::all();
        return view('travellist.edit', compact('travel_list', 'transportations', 'faculties'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'travel_date' => 'required|date',
            'request_parties' => 'required|string',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time',
            'transportation_id' => 'required|exists:transportations,id',
            'faculty_id' => 'required|exists:faculties,id',
            'status' => 'required|string|max:50',
        ]);

        $travelData = $validated;
        unset($travelData['request_parties']); // remove non-existent column
        $travel = Travel_Lists::findOrFail($id);
        $travel->update($travelData);

        $travel->requestParties()->delete();
        $names = preg_split('/\r\n|\r|\n/', trim($validated['request_parties']));
        foreach ($names as $name) {
            if (!empty(trim($name))) {
                TravelRequestParty::create([
                    'travel_list_id' => $travel->id,
                    'name' => trim($name),
                ]);
            }
        }

        return redirect()->route('travellist.index')->with('success', 'Travel list updated successfully.');
    }

    public function destroy($id)
    {
        $travel_list = Travel_Lists::findOrFail($id);
        $travel_list->delete();
        return redirect()->route('travellist.index')->with('success', 'Travel list deleted successfully.');
    }

    public function supervisorApproval(Request $request, $id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if ($request->status === 'Supervisor Approved') {
            if ($request->hasFile('supervisor_signature')) {
                $path = $request->file('supervisor_signature')->store('signatures', 'public');
                $travel->supervisor_signature = $path;
            }
            $travel->status = 'Supervisor Approved';
        } else {
            $travel->status = 'Declined by Supervisor';
        }

        $travel->save();
        return back()->with('success', 'Supervisor action recorded.');
    }

    public function ceoApproval(Request $request, $id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if ($request->status === 'CEO Approved') {
            $request->validate([
                'conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time',
            ]);

            if ($request->hasFile('ceo_signature')) {
                $path = $request->file('ceo_signature')->store('signatures', 'public');
                $travel->ceo_signature = $path;
            }

            $travel->conditionalities = $request->conditionalities;
            $travel->status = 'CEO Approved';
        } else {
            $travel->status = 'Declined by CEO';
        }

        $travel->save();
        return back()->with('success', 'CEO action recorded.');
    }

    
    public function download($id)
    {
        $travel = Travel_Lists::with('requestParties', 'transportation')->findOrFail($id);

        if ($travel->status !== 'CEO Approved') {
            return redirect()->back()->with('error', 'Only approved travel orders can be downloaded.');
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'marginTop' => 1200,
            'marginLeft' => 1200,
            'marginRight' => 1200,
            'marginBottom' => 1200,
        ]);

        // ✅ Logo
        $section->addImage(
            public_path('img/csulogo.png'),
            [
                'width' => 100,
                'height' => 100,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]
        );

        $section->addText('Travel Order', ['bold' => true, 'size' => 18], ['alignment' => 'center', 'spaceAfter' => 200]);

        // ✅ Table
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
        ]);

        $table->addRow();
        $table->addCell(3000)->addText('Date');
        $table->addCell(6000)->addText($travel->travel_date);

        $table->addRow();
        $table->addCell(3000)->addText('Requesting Parties');
        $table->addCell(6000)->addText($travel->requestParties->pluck('name')->join(', '));

        $table->addRow();
        $table->addCell(3000)->addText('Purpose');
        $table->addCell(6000)->addText($travel->purpose);

        $table->addRow();
        $table->addCell(3000)->addText('Destination');
        $table->addCell(6000)->addText($travel->destination);

        $table->addRow();
        $table->addCell(3000)->addText('Conditionalities');
        $table->addCell(6000)->addText($travel->conditionalities);

        $table->addRow();
        $table->addCell(3000)->addText('Means');
        $table->addCell(6000)->addText($travel->transportation->transportvehicle ?? 'N/A');

        // ✅ Add CEO Signature
        if ($travel->ceo_signature) {
            $section->addTextBreak(2);
            $section->addText('Approved by:', ['bold' => true]);

            // ✅ Correct path (go through storage/app/public)
            $signaturePath = storage_path('app/public/' . $travel->ceo_signature);

            if (file_exists($signaturePath)) {
                $section->addImage(
                    $signaturePath,
                    [
                        'width' => 120,
                        'height' => 50,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
                    ]
                );
                $section->addText('CEO Signature', ['italic' => true, 'size' => 10]);
            } else {
                $section->addText('[Signature file missing]', ['italic' => true, 'color' => 'FF0000']);
            }
        }

        $fileName = 'TravelOrder_' . $travel->id . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

}
