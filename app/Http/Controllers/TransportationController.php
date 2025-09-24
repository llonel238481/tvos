<?php

namespace App\Http\Controllers;
use App\Models\Transportation;
use Illuminate\Http\Request;

class TransportationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $transportations = Transportation::when($search, function ($query, $search) {
            return $query->where('transportvehicle', 'like', "%{$search}%");
        })->get();

        return view('transportation.transportation', compact('transportations'));
    }

    public function create()
    {
        return view('transportation.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'transportvehicle' => 'required|string|max:255',
        ]);

        Transportation::create([
            'transportvehicle' => $request->transportvehicle,
        ]);

        return redirect()->route('transportation.index')
                         ->with('success', 'Transport vehicle created successfully.');
    }

    public function show(Transportation $transportation)
    {
        return view('transportation.show', compact('transportation'));
    }

    public function edit(Transportation $transportation)
    {
        return view('transportation.edit', compact('transportation'));
    }

    public function update(Request $request, Transportation $transportation)
    {
        $request->validate([
            'transportvehicle' => 'required|string|max:255',
        ]);

        $transportation->update([
            'transportvehicle' => $request->transportvehicle,
        ]);

        return redirect()->route('transportation.index')
                         ->with('success', 'Transport vehicle updated successfully.');
    }

    public function destroy(Transportation $transportation)
    {
        $transportation->delete();

        return redirect()->route('transportation.index')
                         ->with('success', 'Transport vehicle deleted successfully.');
    }   
}
