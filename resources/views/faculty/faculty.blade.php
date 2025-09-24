<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Faculty Panel</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search and Filter Section -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
            <!-- Search -->
        <form action="{{ route('faculties.index') }}" method="GET" class="mb-4 flex gap-2 flex-wrap">
            <div class="form-control w-full sm:w-1/2">
                <input type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search faculty..." 
                    class="input input-bordered w-full" />
            </div>

            <button type="submit" class="btn btn-success">Search</button>
        </form>

            <!-- Add Faculty Button -->
            <label for="add-faculty-modal" class="btn btn-success w-full sm:w-auto">+ Add Faculty</label>
        </div>

        <!-- Faculty Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>
                            <!-- Master Checkbox -->
                            <input type="checkbox" id="select-all" class="checkbox" />
                        </th>
                        <th>#</th>
                        <th>Faculty Name</th>
                        <th>Email</th>
                        <th>Contact</th>
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
                            <td class="flex gap-2">
                                <!-- Edit Button -->
                                <label for="edit-faculty-{{ $faculty->id }}" class="btn btn-sm btn-outline">Edit</label>

                                <!-- Delete Button -->
                                <label for="delete-faculty-{{ $faculty->id }}" class="btn btn-sm btn-error">Delete</label>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <input type="checkbox" id="edit-faculty-{{ $faculty->id }}" class="modal-toggle" />
                        <div class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Edit Faculty</h3>

                                <form action="{{ route('faculties.update', $faculty->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <!-- Faculty Name -->
                                    <div class="form-control mb-3">
                                        <label class="label">Faculty Name</label>
                                        <input type="text" 
                                            name="facultyname" 
                                            value="{{ old('facultyname', $faculty->facultyname) }}" 
                                            class="input input-bordered w-full" 
                                            required />
                                    </div>

                                    <!-- Email -->
                                    <div class="form-control mb-3">
                                        <label class="label">Email</label>
                                        <input type="email" 
                                            name="email" 
                                            value="{{ old('email', $faculty->email) }}" 
                                            class="input input-bordered w-full" 
                                            required />
                                    </div>

                                    <!-- Contact -->
                                    <div class="form-control mb-3">
                                        <label class="label">Contact</label>
                                        <input type="text" 
                                            name="contact" 
                                            value="{{ old('contact', $faculty->contact) }}" 
                                            class="input input-bordered w-full" 
                                            required />
                                    </div>

                                    <!-- Actions -->
                                    <div class="modal-action">
                                        <label for="edit-faculty-{{ $faculty->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>


                        <!-- Delete Modal -->
                        <input type="checkbox" id="delete-faculty-{{ $faculty->id }}" class="modal-toggle" />
                        <div class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Delete Faculty</h3>
                                <p>Are you sure you want to delete <b>{{ $faculty->facultyname }}</b>?</p>
                                <div class="modal-action">
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
                            <td colspan="7" class="text-center">No faculty found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Faculty Modal -->
        <input type="checkbox" id="add-faculty-modal" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Add Faculty</h3>
                <form action="{{ route('faculties.store') }}" method="POST">
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

                    <div class="modal-action">
                        <label for="add-faculty-modal" class="btn">Cancel</label>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Script for Select All -->
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
