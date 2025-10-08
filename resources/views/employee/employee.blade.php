<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Employee Panel</h1>

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

        <!-- Search and Filter Section -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
            <form action="{{ route('getEmployees') }}" method="GET" class="flex gap-2 flex-wrap w-full sm:w-auto">
                <div class="form-control w-full sm:w-1/2">
                    <input type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by name..." 
                        class="input input-bordered w-full" />
                </div>

                <div class="form-control w-full sm:w-1/5">
                    <select name="classification" class="select select-bordered w-full" onchange="this.form.submit()">
                        <option value="All">All</option>
                        <option value="Admin" {{ request('classification') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Employee" {{ request('classification') == 'Employee' ? 'selected' : '' }}>Employee</option>
                        <option value="CEO" {{ request('classification') == 'CEO' ? 'selected' : '' }}>CEO</option>
                        <option value="Supervisor" {{ request('classification') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></button>
            </form>

            <label for="add-employee-modal" class="btn btn-success w-full sm:w-auto">+ Add Employee</label>
        </div>

        <!-- Employees Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="checkbox" /></th>
                        <th>#</th>
                        <th>Firstname</th>
                        <th>Middlename</th>
                        <th>Lastname</th>
                        <th>Email</th>
                        <th>Sex</th>
                        <th>Department</th>
                        <th>Extensionname</th>
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
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->sex }}</td>
                            <td>{{ $employee->department->departmentname ?? 'No Department' }}</td>
                            <td>{{ $employee->extensionname }}</td>
                            <td class="flex gap-2">
                                <label for="edit-employee-{{ $employee->id }}" class="btn btn-sm btn-primary w-full sm:w-auto"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg></label>
                                <label for="delete-employee-{{ $employee->id }}" class="btn btn-sm btn-error"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></label>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <input type="checkbox" id="edit-employee-{{ $employee->id }}" class="modal-toggle" />
                        <div class="modal" onclick="if(event.target === this) document.getElementById('edit-employee-{{ $employee->id }}').checked = false;">
                            <div class="modal-box relative">
                                <label for="edit-employee-{{ $employee->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
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
                                        <label class="label">Email</label>
                                        <input type="email" name="email" value="{{ $employee->email }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Sex</label>
                                        <select name="sex" class="select select-bordered w-full" required>
                                            <option value="Male" {{ $employee->sex == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ $employee->sex == 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Department</label>
                                        <select name="department_id" class="select select-bordered w-full" required>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>{{ $department->departmentname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Extensionname</label>
                                        <input type="text" name="extensionname" value="{{ $employee->extensionname }}" class="input input-bordered w-full" />
                                    </div>

                                    <div class="modal-action">
                                        <label for="edit-employee-{{ $employee->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <input type="checkbox" id="delete-employee-{{ $employee->id }}" class="modal-toggle" />
                        <div class="modal" onclick="if(event.target === this) document.getElementById('delete-employee-{{ $employee->id }}').checked = false;">
                            <div class="modal-box relative">
                                <label for="delete-employee-{{ $employee->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                                <h3 class="font-bold text-lg mb-4">Delete Employee</h3>
                                <p>Are you sure you want to delete <b>{{ $employee->firstname }} {{ $employee->lastname }}</b>?</p>
                                <div class="modal-action">
                                    <label for="delete-employee-{{ $employee->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('employee.destroy', $employee->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Employee Modal -->
        <input type="checkbox" id="add-employee-modal" class="modal-toggle" />
        <div class="modal" onclick="if(event.target === this) document.getElementById('add-employee-modal').checked = false;">
            <div class="modal-box relative">
                <label for="add-employee-modal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
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
                        <label class="label">Email</label>
                        <input type="email" name="email" class="input input-bordered w-full" required />
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Sex</label>
                        <select name="sex" class="select select-bordered w-full" required>
                            <option disabled selected>Choose Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Department</label>
                        <select name="department_id" class="select select-bordered w-full" required>
                            <option disabled selected>Choose department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->departmentname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Extensionname</label>
                        <input type="text" name="extensionname" class="input input-bordered w-full" />
                    </div>

                    <div class="modal-action">
                        <label for="add-employee-modal" class="btn">Cancel</label>
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
