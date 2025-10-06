<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Show notifications for the logged-in user
    public function index()
{
    $user = Auth::user();

    $notifications = Notification::query()
        ->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('role', $user->role);
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->unique('id'); // makes sure no duplicates if a row somehow matches both conditions

    return view('notifications', compact('notifications'));
}

public static function deleteRelatedNotifications($travelOrderId)
{
    Notification::where('related_id', $travelOrderId)
        ->delete();
}


    public static function send($title, $message = null, $userId = null, $role = null)
    {
        Notification::create([
            'title' => $title,
            'message' => $message,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }


    // Mark a notification as read
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== Auth::id() && $notification->role !== Auth::user()->role) {
            abort(403, 'Unauthorized action.');
        }

        $notification->update(['is_read' => true]);

        return back();
    }

    // Admin or system can send notifications
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'nullable|string',
            'role' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Notification::create($request->all());

        return back()->with('success', 'Notification sent!');
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);

        // Optional: Ensure user is allowed to delete it
        if ($notification->user_id !== Auth::id() && $notification->role !== Auth::user()->role) {
            abort(403, 'Unauthorized action.');
        }

        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clearAll()
    {
        $user = Auth::user();

        Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('role', $user->role);
        })->delete();

        return back()->with('success', 'All notifications cleared.');
    }
}
