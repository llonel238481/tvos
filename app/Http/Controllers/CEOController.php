<?php

namespace App\Http\Controllers;

use App\Models\CEO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CEOController extends Controller
{
    public function index(Request $request)
    {
        $query = CEO::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $ceos = $query->latest()->get();
        return view('ceo.ceo', compact('ceos'));
    }

    public function create()
    {
        return view('ceo.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:c_e_o_s,email|unique:users,email',
            'contact'   => 'nullable|string|max:20',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ✅ Handle signature upload
        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('signatures', 'public');
            $validated['signature'] = $path;
        }

        // ✅ Create User first (Role = CEo)
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => 'CEO',
            'password' => Hash::make('password123'), // random initial password
            'email_verified_at' => now(),
            'remember_token'    => Str::random(60),
        ]);

        // ✅ Link User ID to CEO
        $validated['user_id'] = $user->id;

        CEO::create($validated);

        return redirect()->route('ceos.index')->with('success', 'CEO created successfully and linked to a new user account.');
    }

    public function show(CEO $ceo)
    {
        return view('ceo.show', compact('ceo'));
    }

    public function edit(CEO $ceo)
    {
        return view('ceo.edit', compact('ceo'));
    }

    public function update(Request $request, CEO $ceo)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:c_e_o_s,email,' . $ceo->id . '|unique:users,email,' . $ceo->user_id,
            'contact'   => 'nullable|string|max:20',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ✅ Update signature if changed
        if ($request->hasFile('signature')) {
            if ($ceo->signature && Storage::disk('public')->exists($ceo->signature)) {
                Storage::disk('public')->delete($ceo->signature);
            }
            $path = $request->file('signature')->store('signatures', 'public');
            $validated['signature'] = $path;
        }

        // ✅ Update CEO info
        $ceo->update($validated);

        // ✅ Sync changes to linked User
        if ($ceo->user_id) {
            $user = User::find($ceo->user_id);
            if ($user) {
                $user->update([
                    'name'  => $validated['name'],
                    'email' => $validated['email'],
                ]);
            }
        }

        return redirect()->route('ceos.index')->with('success', 'CEO and linked user updated successfully.');
    }

    public function destroy(CEO $ceo)
    {
        // ✅ Delete signature file
        if ($ceo->signature && Storage::disk('public')->exists($ceo->signature)) {
            Storage::disk('public')->delete($ceo->signature);
        }

        // ✅ Delete linked User if exists
        if ($ceo->user_id) {
            $user = User::find($ceo->user_id);
            if ($user) {
                $user->delete();
            }
        }

        $ceo->delete();

        return redirect()->route('ceos.index')->with('success', 'CEO and linked user deleted successfully.');
    }
}
