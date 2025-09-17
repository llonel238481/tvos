<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Employee Panel</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search and Filter Section -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
            <!-- Search -->
            <form action="{{ route('getEmployees') }}" method="GET" class="flex gap-2 flex-wrap">
                <div class="form-control w-full sm:w-1/2">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by name..." 
                           class="input input-bordered w-full" />
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <!-- Filter by Department (dummy) -->
            <div class="form-control w-full sm:w-1/5">
                <select class="select select-bordered w-full">
                    <option disabled selected>Filter by Department</option>
                    <option>All</option>
                    <option>IT</option>
                    <option>HR</option>
                    <option>Finance</option>
                    <option>Operations</option>
                </select>
            </div>

            <!-- Add Employee Button -->
            <label for="add-employee-modal" class="btn btn-primary w-full sm:w-auto">+ Add Employee</label>
        </div>

        <!-- Employees Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>
                        <!-- Master Checkbox -->
                            <input type="checkbox" id="select-all" class="checkbox" />
                        </th>
                        <th>#</th>
                        <th>Firstname</th>
                        <th>Middlename</th>
                        <th>Lastname</th>
                        <th>Extensionname</th>
                        <th>Classification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $index => $employee)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox" /></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $employee->firstname }}</td>
                            <td>{{ $employee->middlename }}</td>
                            <td>{{ $employee->lastname }}</td>
                            <td>{{ $employee->extensionname }}</td>
                            <td>{{ $employee->classification }}</td>
                            <td class="flex gap-2">
                                <!-- Edit Button -->
                                <label for="edit-employee-{{ $employee->id }}" class="btn btn-sm btn-outline">Edit</label>

                                <!-- Delete Button -->
                                <label for="delete-employee-{{ $employee->id }}" class="btn btn-sm btn-error">Delete</label>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
<input type="checkbox" id="edit-employee-{{ $employee->id }}" class="modal-toggle" />
<div class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Edit Employee</h3>

        <form action="{{ route('employee.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-control mb-3">
                <label class="label">Firstname</label>
                <input type="text" name="firstname" value="{{ $employee->firstname }}" class="input input-bordered w-full" required />
            </div>
            <div class="form-control mb-3">
                <label class="label">Middlename</label>
                <input type="text" name="middlename" value="{{ $employee->middlename }}" class="input input-bordered w-full" required />
            </div>
            <div class="form-control mb-3">
                <label class="label">Lastname</label>
                <input type="text" name="lastname" value="{{ $employee->lastname }}" class="input input-bordered w-full" required />
            </div>
            <div class="form-control mb-3">
                <label class="label">Extensionname</label>
                <input type="text" name="extensionname" value="{{ $employee->extensionname }}" class="input input-bordered w-full" />
            </div>
            <div class="form-control mb-3">
                <label class="label">Classification</label>
                <select name="classification" class="select select-bordered w-full" required>
                    <option value="Admin" {{ $employee->classification == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Employee" {{ $employee->classification == 'Employee' ? 'selected' : '' }}>Employee</option>
                    <option value="CEO" {{ $employee->classification == 'CEO' ? 'selected' : '' }}>CEO</option>
                    <option value="Supervisor" {{ $employee->classification == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                </select>
            </div>

            <div class="modal-action">
                <!-- Cancel just closes the modal -->
                <label for="edit-employee-{{ $employee->id }}" class="btn">Cancel</label>
                <!-- Submit works as normal -->
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>


                        <!-- Delete Modal -->
                            <input type="checkbox" id="delete-employee-{{ $employee->id }}" class="modal-toggle" />
                            <div class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Delete Employee</h3>
        <p>Are you sure you want to delete <b>{{ $employee->firstname }} {{ $employee->lastname }}</b>?</p>
        <div class="modal-action">
            <label for="delete-employee-{{ $employee->id }}" class="btn">Cancel</label>
            <form action="{{ route('employee.destroy', $employee->id) }}" method="POST" 
                  onsubmit="closeModal('delete-employee-{{ $employee->id }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
</div>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Employee Modal -->
        <input type="checkbox" id="add-employee-modal" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Add Employee</h3>
                <form action="{{ route('employee.store') }}" method="POST">
                    @csrf
                    <div class="form-control mb-3">
                        <label class="label">Firstname</label>
                        <input type="text" name="firstname" class="input input-bordered w-full" required />
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Middlename</label>
                        <input type="text" name="middlename" class="input input-bordered w-full" required />
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Lastname</label>
                        <input type="text" name="lastname" class="input input-bordered w-full" required />
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Extensionname</label>
                        <input type="text" name="extensionname" class="input input-bordered w-full" />
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Classification</label>
                        <select name="classification" class="select select-bordered w-full" required>
                            <option disabled selected>Choose classification</option>
                            <option value="Admin">Admin</option>
                            <option value="Employee">Employee</option>
                            <option value="CEO">CEO</option>
                            <option value="Supervisor">Supervisor</option>
                        </select>
                    </div>

                    <div class="modal-action">
                        <label for="add-employee-modal" class="btn">Cancel</label>
                        <button type="submit" class="btn btn-primary">Save</button>
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

    <!-- Script to Close Modal on Form Submit -->
    <script>
        function closeModal(id) {
            document.getElementById(id).checked = false;
        }
    </script>



</x-app-layout>
