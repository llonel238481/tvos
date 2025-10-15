<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Immediate Supervisor Panel</h1>

        {{-- Success Message --}}
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

            {{-- Auto-close Add Modal --}}
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const addModal = document.getElementById('add-faculty-modal');
                    if(addModal) addModal.checked = false;
                });
            </script>
        

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
            <form action="{{ route('faculties.index') }}" method="GET" class="mb-4 flex gap-2 flex-wrap w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search faculty..." class="input input-bordered w-full sm:w-64" />
                <button type="submit" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></button>
            </form>
            <label for="add-faculty-modal" class="btn btn-success w-full sm:w-auto">+ Add Supervisor</label>
        </div>

        {{-- Faculty Table --}}
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="checkbox" /></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Department</th>
                        <th>Signature</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faculties as $index => $faculty)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox" /></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $faculty->facultyname }}</td>
                            <td>{{ $faculty->email }}</td>
                            <td>{{ $faculty->contact }}</td>
                            <td>{{ $faculty->department->departmentname ?? '__' }}</td>
                            <td>
                                @if($faculty->signature)
                                    <img src="{{ asset('storage/' . $faculty->signature) }}" alt="Signature" class="w-24 h-auto">
                                @endif
                            </td>
                            <td class="flex gap-2">
                                <label for="edit-faculty-{{ $faculty->id }}" class="btn btn-sm btn-primary w-full sm:w-auto"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg></label>
                                <label for="delete-faculty-{{ $faculty->id }}" class="btn btn-sm btn-error"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></label>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <input type="checkbox" id="edit-faculty-{{ $faculty->id }}" class="modal-toggle" />
                        <div class="modal" onclick="if(event.target === this) document.getElementById('edit-faculty-{{ $faculty->id }}').checked = false;">
                            <div class="modal-box relative">
                                <label for="edit-faculty-{{ $faculty->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">Edit Faculty</h3>
                                <form action="{{ route('faculties.update', $faculty->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-control mb-3">
                                        <label class="label">Faculty Name</label>
                                        <input type="text" name="facultyname" value="{{ old('facultyname', $faculty->facultyname) }}" class="input input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $faculty->email) }}" class="input input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Contact</label>
                                        <input type="text" name="contact" value="{{ old('contact', $faculty->contact) }}" class="input input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Department</label>
                                        <select name="department_id" class="select select-bordered w-full">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ $faculty->department_id == $department->id ? 'selected' : '' }}>
                                                    {{ $department->departmentname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Signature</label>
                                        <input type="file" name="signature" class="file-input file-input-bordered w-full" accept="image/*" />
                                        @if($faculty->signature)
                                            <img src="{{ asset('storage/' . $faculty->signature) }}" class="mt-2 w-24 h-auto">
                                        @endif
                                    </div>

                                    <div class="modal-action">
                                        <label for="edit-faculty-{{ $faculty->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Delete Modal --}}
                        <input type="checkbox" id="delete-faculty-{{ $faculty->id }}" class="modal-toggle" />
                        <div class="modal" onclick="if(event.target === this) document.getElementById('delete-faculty-{{ $faculty->id }}').checked = false;">
                            <div class="modal-box relative">
                                <label for="delete-faculty-{{ $faculty->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">Delete Faculty</h3>
                                <p>Are you sure you want to delete <b>{{ $faculty->facultyname }}</b>?</p>
                                <div class="modal-action flex gap-2">
                                    <label for="delete-faculty-{{ $faculty->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('faculties.destroy', $faculty->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No supervisor found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add Faculty Modal --}}
        <input type="checkbox" id="add-faculty-modal" class="modal-toggle" />
        <div class="modal" onclick="if(event.target === this) document.getElementById('add-faculty-modal').checked = false;">
            <div class="modal-box relative">
                <label for="add-faculty-modal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h3 class="font-bold text-lg mb-4">Add Dean</h3>
                <form action="{{ route('faculties.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-control mb-3">
                        <label class="label">Faculty Name</label>
                        <input type="text" name="facultyname" class="input input-bordered w-full" required />
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Email</label>
                        <input type="email" name="email" class="input input-bordered w-full" required />
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Contact</label>
                        <input type="text" name="contact" class="input input-bordered w-full" required />
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Department</label>
                        <select name="department_id" class="select select-bordered w-full">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->departmentname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Signature</label>
                        <input type="file" name="signature" class="file-input file-input-bordered w-full" accept="image/*" />
                    </div>

                    <div class="modal-action">
                        <label for="add-faculty-modal" class="btn">Cancel</label>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Select All Script --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAll = document.getElementById("select-all");
            const checkboxes = document.querySelectorAll(".row-checkbox");

            selectAll.addEventListener("change", function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        });
    </script>
</x-app-layout>
