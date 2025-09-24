<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Travel List</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Top Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
            
            <!-- Left Side: Search + Filters -->
            <form action="{{ route('travellist.index') }}" method="GET" class="flex flex-wrap gap-2 w-full sm:w-auto">
                <!-- Search -->
                <div class="form-control w-full sm:w-64">
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by request"
                        class="input input-bordered w-full" />
                </div>

                <!-- Status Filter -->
                <div class="form-control w-full sm:w-48">
                    <select name="status" class="select select-bordered w-full">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                

                <button type="submit" class="btn btn-success">Search</button>
            </form>

            <!-- Right Side: Add Travel Button -->
            <button class="btn btn-success w-full sm:w-auto" 
                onclick="document.getElementById('add-travel-modal').showModal()">
                + Add Travel
            </button>
        </div>


        <!-- Travel Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="checkbox" /></th>
                        <th>#</th>
                        <th>Date</th>
                        <th>Request</th>
                        <th>Purpose</th>
                        <th>Destination</th>
                        <th>Means</th>
                        <th>Conditionalities</th>
                        <th>Approver</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travel_lists as $index => $travel)
                        <tr>
                            <td><input type="checkbox" class="checkbox row-checkbox" /></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $travel->travel_date }}</td>
                            <td>{{ $travel->request }}</td>
                            <td>{{ $travel->purpose }}</td>
                            <td>{{ $travel->destination }}</td>
                            <td>{{ $travel->transportation->transportvehicle ?? 'No Transportation' }}</td>
                            <td>{{ $travel->conditionalities }}</td>
                            <td>{{ $travel->faculty->facultyname ?? 'No Approver' }}</td>
                            <td>
                                <span class="badge 
                                    @if($travel->status == 'Pending') badge-warning
                                    @elseif($travel->status == 'Approved') badge-success
                                    @elseif($travel->status == 'Completed') badge-info
                                    @else badge-error
                                    @endif">
                                    {{ $travel->status }}
                                </span>
                            </td>
                            <td class="flex gap-2">
                                <!-- View -->
                                <button class="btn btn-sm btn-success" onclick="document.getElementById('view-travel-{{ $travel->id }}').showModal()">View</button>
                                <!-- Edit -->
                                <button class="btn btn-sm btn-outline" onclick="document.getElementById('edit-travel-{{ $travel->id }}').showModal()">Edit</button>
                                <!-- Delete -->
                                <button class="btn btn-sm btn-error" onclick="document.getElementById('delete-travel-{{ $travel->id }}').showModal()">Delete</button>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <dialog id="view-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box rounded-2xl shadow-2xl p-6 bg-gradient-to-br from-white to-gray-50">
                                <!-- Header -->
                                <div class="flex justify-between items-center border-b pb-3 mb-4">
                                    <h3 class="font-bold text-xl text-gray-800">✈️ Travel Request Details</h3>
                                    <form method="dialog">
                                        <button class="btn btn-sm btn-circle btn-ghost">✕</button>
                                    </form>
                                </div>

                                <!-- Details -->
                                <div class="space-y-3 text-gray-700">
                                    <p><span class="font-semibold text-gray-900">📅 Date:</span> {{ $travel->travel_date }}</p>
                                    <p><span class="font-semibold text-gray-900">👤 Request:</span> {{ $travel->request }}</p>
                                    <p><span class="font-semibold text-gray-900">🎯 Purpose:</span> {{ $travel->purpose }}</p>
                                    <p><span class="font-semibold text-gray-900">📍 Destination:</span> {{ $travel->destination }}</p>
                                    <p><span class="font-semibold text-gray-900">🚗 Means:</span> {{ $travel->transportation->transportvehicle ?? 'N/A' }}</p>
                                    <p><span class="font-semibold text-gray-900">📝 Conditionalities:</span> {{ $travel->conditionalities ?? 'N/A' }}</p>
                                    <p><span class="font-semibold text-gray-900">📝 Approver:</span> {{ $travel->faculty->facultyname ?? 'N/A' }}</p>
                                    <p>
                                        <span class="font-semibold text-gray-900">📌 Status:</span> 
                                        <span class="badge 
                                            @if($travel->status == 'Approved') badge-success 
                                            @elseif($travel->status == 'Pending') badge-warning 
                                            @else badge-error @endif">
                                            {{ $travel->status }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Footer -->
                                <div class="modal-action mt-6">
                                    <form method="dialog">
                                        <button class="btn btn-success">Close</button>
                                        @if($travel->status == 'Approved')
                                            <a href="{{ route('travellist.download', $travel->id) }}" 
                                            class="btn btn-success ml-2">
                                                ⬇️ Download
                                            </a>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </dialog>


                        <!-- Edit Modal -->
                        <dialog id="edit-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Edit Travel</h3>
                                <form id="edit-form-{{ $travel->id }}" action="{{ route('travellist.update', $travel->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    @php($role = auth()->user()->role ?? null)
                                    @switch($role)
                                        @case('Admin')
                                        @case('CEO')
                                        @case('Supervisor')
                                            <div class="form-control mb-3">
                                                <label class="label">Date</label>
                                                <input type="date" name="travel_date" value="{{ $travel->travel_date }}" class="input input-bordered w-full" required />
                                            </div>
                                            @break
                                    @endswitch

                                    <div class="form-control mb-3">
                                        <label class="label">Request</label>
                                        <input type="text" name="request" value="{{ $travel->request }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Purpose</label>
                                        <input type="text" name="purpose" value="{{ $travel->purpose }}" class="input input-bordered w-full" required />
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label">Destination</label>
                                        <input type="text" name="destination" value="{{ $travel->destination }}" class="input input-bordered w-full" required />
                                    </div>
                                    
                                    <div class="form-control mb-3">
                                        <label class="label">Transportation</label>
                                        <select name="transportation_id" class="select select-bordered w-full" required>
                                            @foreach($transportations as $transport)
                                                <option value="{{ $transport->id }}" 
                                                    {{ $travel->transportation_id == $transport->id ? 'selected' : '' }}>
                                                    {{ $transport->transportvehicle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Conditionalities</label>
                                        <select name="conditionalities" class="select select-bordered w-full" required>
                                            <option value="On Official Business" {{ $travel->conditionalities == 'On Official Business' ? 'selected' : '' }}>On Official Business</option>
                                            <option value="On Official Time" {{ $travel->conditionalities == 'On Official Time' ? 'selected' : '' }}>On Official Time</option>
                                            <option value="On Official Business and Time" {{ $travel->conditionalities == 'On Official Business and Time' ? 'selected' : '' }}>On Official Business and Time</option>
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Approver</label>
                                        <select name="faculty_id" class="select select-bordered w-full" required>
                                            @foreach($faculties as $faculty)
                                                <option value="{{ $faculty->id }}" 
                                                    {{ $travel->faculty_id == $faculty->id ? 'selected' : '' }}>
                                                    {{ $faculty->facultyname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @switch($role)
                                        @case('Admin')
                                        @case('CEO')
                                        @case('Supervisor')
                                            <div class="form-control mb-3">
                                                <label class="label">Status</label>
                                                <select name="status" class="select select-bordered w-full" required>
                                                    <option value="Pending" {{ $travel->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="Approved" {{ $travel->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="Completed" {{ $travel->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="Cancelled" {{ $travel->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </div>
                                            @break
                                    @endswitch
                                </form>

                                <div class="modal-action">
                                    <!-- Cancel just closes modal -->
                                    <form method="dialog">
                                        <button class="btn">Cancel</button>
                                    </form>

                                    <!-- Update explicitly submits the form -->
                                    <button type="submit" form="edit-form-{{ $travel->id }}" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </dialog>


                        <!-- Delete Modal -->
                        <dialog id="delete-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Delete Travel</h3>
                                <p>Are you sure you want to delete the travel request <b>{{ $travel->purpose }}</b>?</p>
                                <div class="modal-action">
                                    <form method="dialog">
                                        <button class="btn">Cancel</button>
                                    </form>
                                    <form action="{{ route('travellist.destroy', $travel->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </dialog>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No travel records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

       <!-- Add Travel Modal -->
                <dialog id="add-travel-modal" class="modal">
                    <div class="modal-box">
                        <h3 class="font-bold text-lg mb-4">Add Travel</h3>

                        <!-- Form only for Save -->
                        <form id="add-form" action="{{ route('travellist.store') }}" method="POST">
                            @csrf
                            <div class="form-control mb-3">
                                <label class="label">Date</label>
                                <input type="date" name="travel_date" class="input input-bordered w-full" required />
                            </div>
                            <div class="form-control mb-3">
                                <label class="label">Request</label>
                                <input type="text" name="request" class="input input-bordered w-full" required />
                            </div>
                            <div class="form-control mb-3">
                                <label class="label">Purpose</label>
                                <input type="text" name="purpose" class="input input-bordered w-full" required />
                            </div>
                            <div class="form-control mb-3">
                                <label class="label">Destination</label>
                                <input type="text" name="destination" class="input input-bordered w-full" required />
                            </div>
                            
                            <div class="form-control mb-3">
                                <label class="label">Transportation</label>
                                <select name="transportation_id" class="select select-bordered w-full" required>
                                    <option disabled selected>Choose transportation</option>
                                    @foreach($transportations as $transport)
                                        <option value="{{ $transport->id }}">{{ $transport->transportvehicle }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control mb-3">
                                <label class="label">Conditionalities</label>
                                <select name="conditionalities" class="select select-bordered w-full" required>
                                    <option disabled selected>Choose Conditionalities</option>
                                    <option value="On Official Business">On Official Business</option>
                                    <option value="On Official Time">On Official Time</option>
                                    <option value="On Official Business and Time">On Official Business and Time</option>
                                </select>
                            </div>

                            <div class="form-control mb-3">
                                <label class="label">Approver</label>
                                <select name="faculty_id" class="select select-bordered w-full" required>
                                    <option disabled selected>Choose Approver</option>
                                    @foreach($faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->facultyname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        <!-- Action Buttons -->
                        <div class="modal-action">
                            <!-- Cancel just closes modal, no validation -->
                            <form method="dialog">
                                <button class="btn">Cancel</button>
                            </form>

                            <!-- Save explicitly submits the form -->
                            <button type="submit" form="add-form" class="btn btn-success">Save</button>
                        </div>
                    </div>
                </dialog>

    </div>

    {{-- JavaScript for Master Checkbox functionality --}}
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
