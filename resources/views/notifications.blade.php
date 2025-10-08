<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Notifications</h1>

        {{-- ✅ Success Message --}}
        @if(session('success'))
            <div id="success-alert" class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>

            <script>
                // Wait 3 seconds then fade out
                setTimeout(() => {
                    const alert = document.getElementById('success-alert');
                    if(alert) {
                        alert.style.transition = "opacity 0.5s ease";
                        alert.style.opacity = '0';
                        // Optional: remove from DOM after fading
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 3000); // 3000ms = 3 seconds
            </script>
        @endif

        {{-- ❌ Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-error shadow-lg mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 📜 Notification Feed --}}
        <div class="space-y-3">
            @forelse($notifications as $notification)
                <div class="flex items-start justify-between p-4 bg-base-200 rounded-lg shadow hover:bg-base-300 transition">
                    <div class="flex gap-3">
                        {{-- 🔵 Icon (Unread / Read) --}}
                        @if($notification->status === 'unread')
                            <div class="mt-1 w-3 h-3 bg-blue-500 rounded-full"></div>
                        @else
                            <div class="mt-1 w-3 h-3 bg-gray-400 rounded-full"></div>
                        @endif

                        {{-- 📝 Content --}}
                        <div>
                            <p class="font-semibold">{{ $notification->title }}</p>
                            <p class="text-sm text-gray-600">{{ $notification->message }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    {{-- 🗑 Delete Button --}}
                    <label for="delete-notification-{{ $notification->id }}" class="btn btn-sm btn-error tooltip" data-tip="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
                            <path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </label>
                </div>

                {{-- 🧾 Delete Modal --}}
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
                <div class="p-4 text-center text-gray-500 bg-base-200 rounded-lg">
                    No notifications found.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 🧠 Script to auto-close modal when clicking outside --}}
    <script>
        document.addEventListener('click', (e) => {
            document.querySelectorAll('.modal').forEach(modal => {
                const modalBox = modal.querySelector('.modal-box');
                const checkbox = modal.previousElementSibling;

                if (checkbox?.checked && !modalBox.contains(e.target) && modal.contains(e.target)) {
                    checkbox.checked = false;
                }
            });
        });
    </script>
</x-app-layout>
