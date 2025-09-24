<?php

namespace App\Http\Controllers;
use App\Models\Travel_Lists;
Use App\Models\Transportation;
use App\Models\Faculty;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class TravelListController extends Controller
{
    public function index(Request $request)
{
    $query = Travel_Lists::with('transportation');

    // 🔍 Search by request or purpose
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('request', 'like', "%{$search}%")
              ->orWhere('purpose', 'like', "%{$search}%");
        });
    }

    // 🎯 Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $travel_lists = $query->get();
    $totalTravelOrders = Travel_Lists::count();
    $transportations = Transportation::all();
    $faculties = Faculty::all();

    // ✅ Pass $transportations to view
    return view('travellist.travellist', compact('travel_lists', 'transportations', 'totalTravelOrders', 'faculties'));
}



    public function show($id)
    {
        $travel_list = Travel_Lists::findOrFail($id);
        return view('travellist.travellist', compact('travel_list'));
    }

    public function create()
    {
        return view('travellist.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'travel_date' => 'required|date',
            'request' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time',
            'transportation_id' => 'required|exists:transportations,id',
            'faculty_id' => 'required|exists:faculties,id',
            
        ]);

        Travel_Lists::create($validated);
        return redirect()->route('travellist.index')->with('success', 'Travel list created successfully.');
    }

    public function edit($id)
    {
        $travel_list = Travel_Lists::findOrFail($id);
        return view('travellist.edit', compact('travel_list'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'travel_date' => 'required|date',
            'request'     => 'required|string|max:255',
            'purpose'     => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time',
            'transportation_id' => 'required|exists:transportations,id',
            'faculty_id' => 'required|exists:faculties,id',
            'status'      => 'required|string|max:50',
        ]);

        $travel_list = Travel_Lists::findOrFail($id);
        $travel_list->update($validated);
        return redirect()->route('travellist.index')->with('success', 'Travel list updated successfully.');
    }

    public function destroy($id)
    {
        $travel_list = Travel_Lists::findOrFail($id);
        $travel_list->delete();
        return redirect()->route('travellist.index')->with('success', 'Travel list deleted successfully.');
    }

    // Download Travel Order as Word Document
    public function download($id)
{
    $travel = Travel_Lists::findOrFail($id);

    if ($travel->status !== 'Approved') {
        return redirect()->back()->with('error', 'Only approved travel orders can be downloaded.');
    }

    $phpWord = new PhpWord();
    $section = $phpWord->addSection([
        'orientation' => 'portrait',   // or 'landscape'
        'marginTop' => 1200,
        'marginLeft' => 1200,
        'marginRight' => 1200,
        'marginBottom' => 1200,
    ]);

    // Title
    $section->addText(
        'Travel Order',
        ['bold' => true, 'size' => 18],
        ['alignment' => 'center', 'spaceAfter' => 200]
    );

    // Details in a table
    $table = $section->addTable([
        'borderSize' => 6,
        'borderColor' => '999999',
        'cellMargin' => 80,
    ]);

    $table->addRow();
    $table->addCell(3000)->addText('Date');
    $table->addCell(6000)->addText($travel->travel_date);

    $table->addRow();
    $table->addCell(3000)->addText('Request');
    $table->addCell(6000)->addText($travel->request);

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

    // Output file
    $fileName = 'TravelOrder_' . $travel->id . '.docx';
    $tempFile = tempnam(sys_get_temp_dir(), 'word');
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);

    return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
}

}
