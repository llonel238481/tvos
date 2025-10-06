<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Notifications Panel</h1>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-error shadow-lg mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search and Add --}}
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
            <form action="{{ route('notifications.index') }}" method="GET" class="flex gap-2 flex-wrap w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search notifications..." class="input input-bordered w-full sm:w-64">
                <button type="submit" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
                </button>
            </form>
            
        </div>

        {{-- Notifications Table --}}
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="checkbox"></th>
                        <th>#</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $index => $notification)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox"></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $notification->title }}</td>
                            <td>{{ Str::limit($notification->message, 50) }}</td>
                            <td>
                                @if($notification->status === 'unread')
                                    <span class="badge badge-warning">Unread</span>
                                @else
                                    <span class="badge badge-success">Read</span>
                                @endif
                            </td>
                            <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                            <td class="flex gap-2">
                                
                                <label for="delete-notification-{{ $notification->id }}" class="btn btn-sm btn-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </label>
                            </td>
                        </tr>

                       

                        {{-- Delete Modal --}}
                        <input type="checkbox" id="delete-notification-{{ $notification->id }}" class="modal-toggle">
                        <div class="modal">
                            <div class="modal-box relative">
                                <label for="delete-notification-{{ $notification->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">Delete Notification</h3>
                                <p>Are you sure you want to delete <b>{{ $notification->title }}</b>?</p>
                                <div class="modal-action flex gap-2">
                                    <label for="delete-notification-{{ $notification->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No notifications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add Notification Modal --}}
        <input type="checkbox" id="add-notification-modal" class="modal-toggle">
        <div class="modal">
            <div class="modal-box relative">
                <label for="add-notification-modal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h3 class="font-bold text-lg mb-4">Add Notification</h3>
                <form action="{{ route('notifications.store') }}" method="POST">
                    @csrf
                    <div class="form-control mb-3">
                        <label class="label">Title</label>
                        <input type="text" name="title" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Message</label>
                        <textarea name="message" class="textarea textarea-bordered w-full" rows="3" required></textarea>
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                        </select>
                    </div>
                    <div class="modal-action">
                        <label for="add-notification-modal" class="btn">Cancel</label>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const selectAll = document.getElementById("select-all");
            const checkboxes = document.querySelectorAll(".row-checkbox");

            selectAll?.addEventListener("change", () => {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });

            document.querySelectorAll('.modal').forEach(modal => {
                const checkbox = modal.previousElementSibling;
                if (!checkbox) return;

                modal.addEventListener('click', e => {
                    if (!e.target.closest('.modal-box')) {
                        checkbox.checked = false;
                    }
                });
                modal.querySelector('.modal-box').addEventListener('click', e => e.stopPropagation());
            });
        });
    </script>
</x-app-layout>
