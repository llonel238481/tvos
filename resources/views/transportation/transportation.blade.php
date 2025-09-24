<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Transportation Panel</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Top Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
            <!-- Search -->
            <form action="{{ route('transportation.index') }}" method="GET" class="flex gap-2 flex-wrap w-full sm:w-auto">
                <div class="form-control w-full sm:w-64">
                    <input type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search vehicle..." 
                        class="input input-bordered w-full" />
                </div>
                <button type="submit" class="btn btn-success">Search</button>
            </form>

            <!-- Add Transport Button -->
            <label for="add-transport-modal" class="btn btn-success w-full sm:w-auto">+ Add Vehicle</label>
        </div>

        <!-- Transportation Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all" class="checkbox" />
                        </th>
                        <th>#</th>
                        <th>Vehicle Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transportations as $index => $transportation)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox" /></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $transportation->transportvehicle }}</td>
                            <td class="flex gap-2">
                                <!-- Edit Button -->
                                <label for="edit-transport-{{ $transportation->id }}" class="btn btn-sm btn-outline">Edit</label>

                                <!-- Delete Button -->
                                <label for="delete-transport-{{ $transportation->id }}" class="btn btn-sm btn-error">Delete</label>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <input type="checkbox" id="edit-transport-{{ $transportation->id }}" class="modal-toggle" />
                        <div class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Edit Vehicle</h3>
                                <form action="{{ route('transportation.update', $transportation->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-control mb-3">
                                        <label class="label">Vehicle Name</label>
                                        <input type="text" name="transportvehicle" value="{{ $transportation->transportvehicle }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="modal-action">
                                        <label for="edit-transport-{{ $transportation->id }}" class="btn">Cancel</label>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <input type="checkbox" id="delete-transport-{{ $transportation->id }}" class="modal-toggle" />
                        <div class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Delete Vehicle</h3>
                                <p>Are you sure you want to delete <b>{{ $transportation->transportvehicle }}</b>?</p>
                                <div class="modal-action">
                                    <label for="delete-transport-{{ $transportation->id }}" class="btn">Cancel</label>
                                    <form action="{{ route('transportation.destroy', $transportation->id) }}" method="POST" onsubmit="closeModal('delete-transport-{{ $transportation->id }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No vehicles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Transport Modal -->
        <input type="checkbox" id="add-transport-modal" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Add Vehicle</h3>
                <form action="{{ route('transportation.store') }}" method="POST">
                    @csrf
                    <div class="form-control mb-3">
                        <label class="label">Vehicle Name</label>
                        <input type="text" name="transportvehicle" class="input input-bordered w-full" required />
                    </div>
                    <div class="modal-action">
                        <label for="add-transport-modal" class="btn">Cancel</label>
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

    <!-- Script to Close Modal on Form Submit -->
    <script>
        function closeModal(id) {
            document.getElementById(id).checked = false;
        }
    </script>
</x-app-layout>
