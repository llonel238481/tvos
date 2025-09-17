<?php

namespace App\Http\Controllers;
use App\Models\Travel_Lists;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class TravelListController extends Controller
{
    public function index()
    {
        $travel_lists = Travel_Lists::all();
        return view('travellist.travellist', compact('travel_lists'));
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
            'means' => 'required|string|max:255',
            
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
            'means'       => 'required|string|max:255',
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
    $table->addCell(3000)->addText('Means');
    $table->addCell(6000)->addText($travel->means);

    // Output file
    $fileName = 'TravelOrder_' . $travel->id . '.docx';
    $tempFile = tempnam(sys_get_temp_dir(), 'word');
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);

    return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
}

}
