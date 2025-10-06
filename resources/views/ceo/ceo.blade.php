<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">CEO Panel</h1>

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
            <form action="{{ route('ceos.index') }}" method="GET" class="flex gap-2 flex-wrap w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search CEO..." class="input input-bordered w-full sm:w-64">
                <button type="submit" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></button>
            </form>
            <label for="add-ceo-modal" class="btn btn-success w-full sm:w-auto">+ Add CEO</label>
        </div>

        {{-- CEO Table --}}
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="checkbox"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Signature</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ceos as $index => $ceo)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox"></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $ceo->name }}</td>
                            <td>{{ $ceo->email }}</td>
                            <td>{{ $ceo->contact }}</td>
                            <td>
                                @if($ceo->signature)
                                    <img src="{{ asset('storage/' . $ceo->signature) }}" class="h-10">
                                @endif
                            </td>
                            <td class="flex gap-2">
                                {{-- <label for="view-ceo-{{ $ceo->id }}" class="btn btn-sm btn-info">View</label> --}}
                                <label for="edit-ceo-{{ $ceo->id }}" class="btn btn-sm btn-primary w-full sm:w-auto"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg></label>
                                <label for="delete-ceo-{{ $ceo->id }}" class="btn btn-sm btn-error"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></label>
                            </td>
                        </tr>

                        {{-- View Modal --}}
                        {{-- <input type="checkbox" id="view-ceo-{{ $ceo->id }}" class="modal-toggle">
                        <div class="modal">
                            <div class="modal-box relative">
                                <label for="view-ceo-{{ $ceo->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">{{ $ceo->name }}</h3>
                                <p><b>Email:</b> {{ $ceo->email }}</p>
                                <p><b>Contact:</b> {{ $ceo->contact }}</p>
                                @if($ceo->signature)
                                    <img src="{{ asset('storage/' . $ceo->signature) }}" class="mt-2 w-32 h-auto">
                                @endif
                            </div>
                        </div> --}}

                        {{-- Edit Modal --}}
                        <input type="checkbox" id="edit-ceo-{{ $ceo->id }}" class="modal-toggle">
                        <div class="modal">
                            <div class="modal-box relative">
                                <label for="edit-ceo-{{ $ceo->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">Edit CEO</h3>
                                <form action="{{ route('ceos.update', $ceo->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-control mb-3">
                                        <label class="label">Name</label>
                                        <input type="text" name="name" value="{{ old('name', $ceo->name) }}" class="input input-bordered w-full" required>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $ceo->email) }}" class="input input-bordered w-full" required>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Contact</label>
                                        <input type="text" name="contact" value="{{ old('contact', $ceo->contact) }}" class="input input-bordered w-full">
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Signature</label>
                                        <input type="file" name="signature" class="file-input file-input-bordered w-full">
                                        @if($ceo->signature)
                                            <img src="{{ asset('storage/' . $ceo->signature) }}" class="mt-2 h-10 w-auto">
                                        @endif
                                    </div>

                                    <div class="modal-action">
                                        <label for="edit-ceo-{{ $ceo->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Delete Modal --}}
                        <input type="checkbox" id="delete-ceo-{{ $ceo->id }}" class="modal-toggle">
                        <div class="modal">
                            <div class="modal-box relative">
                                <label for="delete-ceo-{{ $ceo->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">Delete CEO</h3>
                                <p>Are you sure you want to delete <b>{{ $ceo->name }}</b>?</p>
                                <div class="modal-action flex gap-2">
                                    <label for="delete-ceo-{{ $ceo->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('ceos.destroy', $ceo->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No CEOs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add CEO Modal --}}
        <input type="checkbox" id="add-ceo-modal" class="modal-toggle">
        <div class="modal">
            <div class="modal-box relative">
                <label for="add-ceo-modal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h3 class="font-bold text-lg mb-4">Add CEO</h3>
                <form action="{{ route('ceos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-control mb-3">
                        <label class="label">Name</label>
                        <input type="text" name="name" class="input input-bordered w-full" required>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Email</label>
                        <input type="email" name="email" class="input input-bordered w-full" required>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Contact</label>
                        <input type="text" name="contact" class="input input-bordered w-full">
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Signature</label>
                        <input type="file" name="signature" class="file-input file-input-bordered w-full">
                    </div>

                    <div class="modal-action">
                        <label for="add-ceo-modal" class="btn">Cancel</label>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
   <script>
document.addEventListener("DOMContentLoaded", function () {
    // Select All
    const selectAll = document.getElementById("select-all");
    const checkboxes = document.querySelectorAll(".row-checkbox");
    selectAll?.addEventListener("change", () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    });

    // Close modals on clicking outside modal-box
    document.querySelectorAll('.modal').forEach(modal => {
        const checkbox = modal.previousElementSibling; // the input.modal-toggle should be previous sibling
        if (!checkbox) return;

        modal.addEventListener('click', e => {
            // If click is outside modal-box
            if (!e.target.closest('.modal-box')) {
                checkbox.checked = false;
            }
        });

        // Stop clicks inside modal-box from closing
        modal.querySelector('.modal-box').addEventListener('click', e => {
            e.stopPropagation();
        });
    });
});
</script>

</x-app-layout>
