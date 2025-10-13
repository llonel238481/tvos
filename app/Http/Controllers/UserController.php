<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Faculty;
use App\Models\CEO;
use App\Models\Employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 🔍 Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 🧪 Filter by role (if not "All")
        if ($request->filled('role') && $request->role !== 'All') {
            $query->where('role', $request->role);
        }

        // 📄 Paginate results (10 per page)
        $users = $query->orderBy('name')->paginate(5)->appends($request->query());

        return view('users.user', compact('users'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'role'     => 'required|in:Admin,Employee,CEO,Supervisor',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(60),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role'     => 'required|in:Admin,Employee,CEO,Supervisor',
            'password' => 'nullable|string|min:8',
        ]);

        // ✅ Update the user
        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => $request->filled('password')
                ? Hash::make($validated['password'])
                : $user->password,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'remember_token'    => $user->remember_token ?? Str::random(60),
        ]);

        // ✅ Sync to Faculty if exists
        $faculty = Faculty::where('user_id', $user->id)->first();
        if ($faculty) {
            $faculty->update([
                'facultyname' => $validated['name'],
                'email'       => $validated['email'],
            ]);
        }

        // ✅ Sync to CEO if exists
        $ceo = CEO::where('user_id', $user->id)->first();
        if ($ceo) {
            $ceo->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        // ✅ Sync to Employee if exists (FIXED)
        $employee = Employees::where('user_id', $user->id)->first();
        if ($employee) {
            $nameParts = explode(' ', $validated['name']);
            $firstname = $nameParts[0] ?? '';
            $middlename = $nameParts[1] ?? '';
            $lastname = $nameParts[2] ?? '';

            $employee->update([
                'firstname'  => $firstname,
                'middlename' => $middlename,
                'lastname'   => $lastname,
                'email'      => $validated['email'],
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User and linked records updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // 🧹 Delete related Faculty if exists
        $faculty = Faculty::where('user_id', $user->id)->first();
        if ($faculty) {
            if ($faculty->signature && file_exists(public_path('storage/' . $faculty->signature))) {
                unlink(public_path('storage/' . $faculty->signature));
            }
            $faculty->delete();
        }

        // 🧹 Delete related CEO if exists
        $ceo = CEO::where('user_id', $user->id)->first();
        if ($ceo) {
            if ($ceo->signature && file_exists(public_path('storage/' . $ceo->signature))) {
                unlink(public_path('storage/' . $ceo->signature));
            }
            $ceo->delete();
        }

        // 🧹 Delete related Employee if exists (FIXED)
        $employee = Employees::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->delete();
        }

        // ✅ Finally delete the user
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User and linked records deleted successfully.');
    }
}
