<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">User Panel</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search and Filter Section -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
        <form action="{{ route('users.index') }}" method="GET" class="flex gap-2 flex-wrap">
            <div class="form-control w-full sm:w-1/2">
                <input type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search name or email..." 
                    class="input input-bordered w-full" />
            </div>

            <div class="form-control w-full sm:w-1/4">
                <select name="role" class="select select-bordered w-full">
                    <option value="All" {{ request('role') == 'All' ? 'selected' : '' }}>All</option>
                    <option value="Employee" {{ request('role') == 'Employee' ? 'selected' : '' }}>Employee</option>
                    <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="CEO" {{ request('role') == 'CEO' ? 'selected' : '' }}>CEO</option>
                    <option value="Supervisor" {{ request('role') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Search</button>
        </form>

            <!-- Add User Button -->
            <label for="add-user-modal" class="btn btn-success w-full sm:w-auto">+ Add User</label>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>
                            <!-- Master Checkbox -->
                            <input type="checkbox" id="select-all" class="checkbox" />
                        </th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox" /></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td class="flex gap-2">
                                <label for="edit-user-{{ $user->id }}" class="btn btn-sm btn-outline">Edit</label>
                                <label for="delete-user-{{ $user->id }}" class="btn btn-sm btn-error">Delete</label>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <input type="checkbox" id="edit-user-{{ $user->id }}" class="modal-toggle" />
                        <div class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Edit User</h3>
                                <form action="{{ route('users.update', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-control mb-3">
                                        <label class="label">Name</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Role</label>
                                        <select name="role" class="select select-bordered w-full" required>
                                            <option value="" disabled>Select Role</option>
                                            <option value="Employee" {{ $user->role == 'Employee' ? 'selected' : '' }}>Employee</option>
                                            <option value="Admin" {{ $user->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="CEO" {{ $user->role == 'CEO' ? 'selected' : '' }}>CEO</option>
                                            <option value="Supervisor" {{ $user->role == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                                        </select>
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Password</label>
                                        <div class="relative">
                                            <input type="password" 
                                                   name="password" 
                                                   id="password-edit-{{ $user->id }}" 
                                                   placeholder="Leave blank to keep current" 
                                                   class="input input-bordered w-full pr-10" />
                                            <button type="button" 
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                                    onclick="togglePassword('password-edit-{{ $user->id }}', this)">
                                                <!-- Eye Icon -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 
                                                                                         4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal-action">
                                        <label for="edit-user-{{ $user->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <input type="checkbox" id="delete-user-{{ $user->id }}" class="modal-toggle" />
                        <div class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Delete User</h3>
                                <p>Are you sure you want to delete <b>{{ $user->name }}</b>?</p>
                                <div class="modal-action">
                                    <label for="delete-user-{{ $user->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add User Modal -->
        <input type="checkbox" id="add-user-modal" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Add User</h3>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="form-control mb-3">
                        <label class="label">Name</label>
                        <input type="text" name="name" class="input input-bordered w-full" required />
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Email</label>
                        <input type="email" name="email" class="input input-bordered w-full" required />
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Role</label>
                        <select name="role" class="select select-bordered w-full" required>
                            <option disabled selected>Select Role</option>
                            <option value="Employee">Employee</option>
                            <option value="Admin">Admin</option>
                            <option value="CEO">CEO</option>
                            <option value="Supervisor">Supervisor</option>
                        </select>
                    </div>
                    <div class="form-control mb-3">
                        <label class="label">Password</label>
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   id="password-add" 
                                   class="input input-bordered w-full pr-10" required />
                            <button type="button" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                    onclick="togglePassword('password-add', this)">
                                <!-- Eye Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 
                                                                                 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="modal-action">
                        <label for="add-user-modal" class="btn">Cancel</label>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script for Select All & Toggle Password -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAll = document.getElementById("select-all");
            const checkboxes = document.querySelectorAll(".row-checkbox");

            if (selectAll) {
                selectAll.addEventListener("change", function () {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                });
            }
        });

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);

            if (input.type === "password") {
                input.type = "text";
                btn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7 
                                 0.51-1.624 1.42-3.047 2.625-4.125M6.18 6.18A9.978 9.978 0 0112 5c4.477 0 
                                 8.268 2.943 9.542 7-.267.85-.65 1.642-1.125 2.347M15 12a3 3 0 
                                 11-6 0 3 3 0 016 0zM3 3l18 18" />
                    </svg>`;
            } else {
                input.type = "password";
                btn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 
                                                                                     9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 
                                                                                     0-8.268-2.943-9.542-7z" />
                    </svg>`;
            }
        }
    </script>
</x-app-layout>
