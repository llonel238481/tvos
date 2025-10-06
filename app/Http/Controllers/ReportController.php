<?php

namespace App\Http\Controllers;

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use App\Models\Travel_Lists;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function download($id)
    {
        $travel = Travel_Lists::with('requestParties', 'transportation', 'ceo')->findOrFail($id);

        if ($travel->status !== 'CEO Approved') {
            return redirect()->back()->with('error', 'Only approved travel orders can be downloaded.');
        }

        // Render the Blade view to HTML
        $html = View::make('travellist.tvlreport', compact('travel'))->render();

        // Temporary file path
        $fileName = 'TravelOrder_' . $travel->id . '.pdf';
        $tempPath = storage_path('app/public/' . $fileName);

        // Generate PDF with Browsershot
        Browsershot::html($html)
            ->format('Legal')
            ->margins(2, 2, 2, 2)
            ->showBackground()
            ->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

  public function preview($id)
    {
        $travel = Travel_Lists::with(['transportation', 'requestParties', 'faculty', 'ceo'])->findOrFail($id);

        // Temporary file name and path
        $fileName = 'TravelOrder_' . $travel->id . '.pdf';
        $tempPath = storage_path('app/public/' . $fileName);

        // Render Blade to HTML
        $html = view('travellist.request', compact('travel'))->render();

        // Generate PDF with Browsershot
        Browsershot::html($html)
            ->format('A4')
            ->margins(20, 20, 20, 20)
            ->showBackground()
            ->save($tempPath);

        // Return PDF as download and delete after send
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

   
}
