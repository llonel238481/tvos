<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Department Panel</h1>

        <!-- Success Message -->
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

        <!-- Top Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
            <!-- Search -->
            <form action="{{ route('departments.index') }}" method="GET" class="flex gap-2 flex-wrap w-full sm:w-auto">
                <div class="form-control w-full sm:w-64">
                    <input type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search department..." 
                        class="input input-bordered w-full" />
                </div>
                <button type="submit" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></button>
            </form>

            <!-- Add Department Button -->
            <label for="add-department-modal" class="btn btn-success w-full sm:w-auto">+ Add Department</label>
        </div>

        <!-- Departments Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all" class="checkbox" />
                        </th>
                        <th>#</th>
                        <th>Department Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $index => $department)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox" /></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $department->departmentname }}</td>
                            <td class="flex gap-2">
                                <!-- Edit Button -->
                                <label for="edit-department-{{ $department->id }}" class="btn btn-sm btn-primary w-full sm:w-auto"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg></label>

                                <!-- Delete Button -->
                                <label for="delete-department-{{ $department->id }}" class="btn btn-sm btn-error"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></label>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <input type="checkbox" id="edit-department-{{ $department->id }}" class="modal-toggle" />
                        <div class="modal" onclick="if(event.target === this) document.getElementById('edit-department-{{ $department->id }}').checked = false;">
                            <div class="modal-box relative">
                                <!-- Top-right Close button -->
                                <label for="edit-department-{{ $department->id }}" 
                                    class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>

                                <h3 class="font-bold text-lg mb-4">Edit Department</h3>
                                <form action="{{ route('departments.update', $department->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-control mb-3">
                                        <label class="label">Department Name</label>
                                        <input type="text" name="departmentname" value="{{ $department->departmentname }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="modal-action">
                                        <label for="edit-department-{{ $department->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <input type="checkbox" id="delete-department-{{ $department->id }}" class="modal-toggle" />
                        <div class="modal" onclick="if(event.target === this) document.getElementById('delete-department-{{ $department->id }}').checked = false;">
                            <div class="modal-box relative">
                                <!-- Top-right Close button -->
                                <label for="delete-department-{{ $department->id }}" 
                                    class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>

                                <h3 class="font-bold text-lg mb-4">Delete Department</h3>
                                <p>Are you sure you want to delete <b>{{ $department->departmentname }}</b>?</p>
                                <div class="modal-action">
                                    <label for="delete-department-{{ $department->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('departments.destroy', $department->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Department Modal -->
        <input type="checkbox" id="add-department-modal" class="modal-toggle" />
        <div class="modal" onclick="if(event.target === this) document.getElementById('add-department-modal').checked = false;">
            <div class="modal-box relative">
                <!-- Top-right Close button -->
                <label for="add-department-modal" 
                    class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>

                <h3 class="font-bold text-lg mb-4">Add Department</h3>
                <form action="{{ route('departments.store') }}" method="POST">
                    @csrf
                    <div class="form-control mb-3">
                        <label class="label">Department Name</label>
                        <input type="text" name="departmentname" class="input input-bordered w-full" required />
                    </div>
                    <div class="modal-action">
                        <label for="add-department-modal" class="btn">Cancel</label>
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
