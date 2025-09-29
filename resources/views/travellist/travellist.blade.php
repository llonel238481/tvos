<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Travel Order List</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Top Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
            <form action="{{ route('travellist.index') }}" method="GET" class="flex flex-wrap gap-2 w-full sm:w-auto">
                <div class="form-control w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by request or purpose" class="input input-bordered w-full" />
                </div>

                <div class="form-control w-full sm:w-48">
                    <select name="status" class="select select-bordered w-full">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Supervisor Approved" {{ request('status') == 'Supervisor Approved' ? 'selected' : '' }}>Supervisor Approved</option>
                        <option value="CEO Approved" {{ request('status') == 'CEO Approved' ? 'selected' : '' }}>CEO Approved</option>
                        <option value="Declined" {{ request('status') == 'Declined' ? 'selected' : '' }}>Declined</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Search</button>
            </form>

            @if(in_array(auth()->user()->role, ['Admin', 'Employee']))
                <button class="btn btn-success w-full sm:w-auto" onclick="document.getElementById('add-travel-modal').showModal()">
                    + Add Travel Order
                </button>
            @endif 
        </div>

        <!-- Travel Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Purpose</th>
                        <th>Requesting Parties</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travel_lists as $index => $travel)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $travel->travel_date }}</td>
                            <td>{{ $travel->purpose }}</td>

                            <!-- Requesting Parties -->
                            <td>
                                @if($travel->requestParties && $travel->requestParties->isNotEmpty())
                                    <ul class="list-disc pl-4">
                                        @foreach($travel->requestParties as $party)
                                            <li>{{ $party->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="italic text-gray-500">None</span>
                                @endif
                            </td>

                            <!-- Destination -->
                            <td>{{ $travel->destination }}</td>

                            <td>
                                <span class="inline-block px-2 py-1 text-xs sm:text-sm font-semibold rounded 
                                    @if($travel->status == 'Pending') bg-yellow-500 text-white
                                    @elseif($travel->status == 'Supervisor Approved') bg-blue-500 text-white
                                    @elseif($travel->status == 'CEO Approved') bg-green-500 text-white
                                    @elseif($travel->status == 'Declined') bg-red-500 text-white
                                    @else bg-gray-400 text-white
                                    @endif
                                    truncate max-w-[100px] sm:max-w-[150px] text-center">
                                    {{ $travel->status }}
                                </span>

                            </td>

                            <!-- Actions -->
                            <td class="flex gap-2">
                                <!-- View - available to all -->
                                <button class="btn btn-sm btn-success" onclick="document.getElementById('view-travel-{{ $travel->id }}').showModal()">View</button>

                                <!-- Supervisor: Review when Pending -->
                                @if(auth()->user()->role === 'Supervisor' && $travel->status === 'Pending')
                                    <button class="btn btn-sm btn-primary" onclick="document.getElementById('supervisor-approve-{{ $travel->id }}').showModal()">Review</button>
                                @endif

                                <!-- CEO: Review when Supervisor Approved -->
                                @if(auth()->user()->role === 'CEO' && $travel->status === 'Supervisor Approved')
                                    <button class="btn btn-sm btn-primary" onclick="document.getElementById('ceo-approve-{{ $travel->id }}').showModal()">Review</button>
                                @endif

                                <!-- User: download when CEO Approved -->
                                @if(in_array(auth()->user()->role, ['Admin','User']) && $travel->status === 'CEO Approved')
                                    <a href="{{ route('travellist.download', $travel->id) }}" class="btn btn-sm btn-success" target="_blank">Download</a>
                                @endif

                                <!-- Edit/Delete for Admin -->
                                @if(auth()->user()->role === 'Admin')
                                    <button class="btn btn-sm btn-outline" onclick="document.getElementById('edit-travel-{{ $travel->id }}').showModal()">Edit</button>
                                    <button class="btn btn-sm btn-error" onclick="document.getElementById('delete-travel-{{ $travel->id }}').showModal()">Delete</button>
                                @endif
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <dialog id="view-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box max-w-3xl bg-base-100 text-base-content rounded-lg">
                                <h3 class="font-bold text-xl mb-4 text-center border-b border-base-300 pb-2 dark:border-base-100">Travel Order Details</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Date -->
                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Date</p>
                                        <p>{{ $travel->travel_date }}</p>
                                    </div>

                                    <!-- Status -->
                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Status</p>
                                        <p>{{ $travel->status }}</p>
                                    </div>

                                    <!-- Requesting Parties -->
                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold mb-1">Requesting Parties</p>
                                        @if($travel->requestParties && $travel->requestParties->isNotEmpty())
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach($travel->requestParties as $party)
                                                    <li>{{ $party->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="italic text-gray-500 dark:text-gray-400">No requesting parties listed</p>
                                        @endif
                                    </div>

                                    <!-- Purpose -->
                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Purpose</p>
                                        <p>{{ $travel->purpose }}</p>
                                    </div>

                                    <!-- Destination -->
                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Destination</p>
                                        <p>{{ $travel->destination }}</p>
                                    </div>

                                    <!-- Transportation -->
                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Transportation</p>
                                        <p>{{ $travel->transportation->transportvehicle ?? 'No Transportation' }}</p>
                                    </div>

                                    <!-- Conditionalities -->
                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold mb-1">Conditionalities</p>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 border {{ $travel->conditionalities === 'On Official Business' || $travel->conditionalities === 'On Official Business and Time' ? 'bg-green-700 border-green-800' : 'border-gray-400 dark:border-gray-500' }}"></div>
                                                <span>On Official Business</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 border {{ $travel->conditionalities === 'On Official Time' || $travel->conditionalities === 'On Official Business and Time' ? 'bg-green-700 border-green-800' : 'border-gray-400 dark:border-gray-500' }}"></div>
                                                <span>On Official Time</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Approver -->
                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Approver</p>
                                        <p>{{ $travel->faculty->facultyname ?? 'No Approver' }}</p>
                                    </div>

                                    <!-- Signatures -->
                                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                        <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100 text-center">
                                            <p class="font-semibold mb-2">Supervisor Signature</p>
                                            @if(!empty($travel->supervisor_signature))
                                                <img src="{{ asset('storage/' . $travel->supervisor_signature) }}" alt="Supervisor signature" class="max-h-28 border border-base-300 dark:border-base-100 mx-auto" />
                                            @else
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Not provided</p>
                                            @endif
                                        </div>
                                        <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100 text-center">
                                            <p class="font-semibold mb-2">CEO Signature</p>
                                            @if(!empty($travel->ceo_signature))
                                                <img src="{{ asset('storage/' . $travel->ceo_signature) }}" alt="CEO signature" class="max-h-28 border border-base-300 dark:border-base-100 mx-auto" />
                                            @else
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Not provided</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-action mt-6">
                                    <form method="dialog"><button class="btn">Close</button></form>

                                    @if($travel->status === 'CEO Approved')
                                        <a href="{{ route('travellist.download', $travel->id) }}" class="btn btn-success" target="_blank">Download</a>
                                    @endif
                                </div>
                            </div>
                        </dialog>


                       <!-- Supervisor Review Modal -->
                        <dialog id="supervisor-approve-{{ $travel->id }}" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-3">Supervisor Review</h3>

                                <form id="supervisor-form-{{ $travel->id }}" action="{{ route('travellist.supervisor.approve', $travel->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-control mb-3">
                                        <label class="label">Signature (image) <span class="text-red-500">*</span></label>
                                        <input type="file" name="supervisor_signature" accept="image/*" class="file-input file-input-bordered w-full" required />
                                    </div>

                                    <input type="hidden" name="status" id="supervisor-status-{{ $travel->id }}" value="Supervisor Approved" />

                                    <div class="modal-action">
                                        <button type="button" class="btn btn-success"
                                            onclick="document.getElementById('supervisor-status-{{ $travel->id }}').value='Supervisor Approved'; document.getElementById('supervisor-form-{{ $travel->id }}').requestSubmit();">
                                            Approve
                                        </button>


                                        <!-- Cancel closes modal without submitting, no validation triggered -->
                                        <button type="button" class="btn" onclick="document.getElementById('supervisor-approve-{{ $travel->id }}').close();">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </dialog>


                        <!-- CEO Review Modal -->
                        <dialog id="ceo-approve-{{ $travel->id }}" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-3">CEO Review</h3>

                                <form id="ceo-form-{{ $travel->id }}" action="{{ route('travellist.ceo.approve', $travel->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-control mb-3">
                                        <label class="label">Signature (image)</label>
                                        <input type="file" name="ceo_signature" accept="image/*" class="file-input file-input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Conditionalities</label>
                                        <select name="conditionalities" class="select select-bordered w-full" required>
                                            <option value="">Select Conditionalities</option>
                                            <option value="On Official Business">On Official Business</option>
                                            <option value="On Official Time">On Official Time</option>
                                            <option value="On Official Business and Time">On Official Business and Time</option>
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label">Notes (optional)</label>
                                        <textarea name="ceo_notes" class="textarea textarea-bordered w-full" rows="3" placeholder="Reason for decline or notes..."></textarea>
                                    </div>

                                    <input type="hidden" name="status" id="ceo-status-{{ $travel->id }}" value="CEO Approved" />

                                    <div class="modal-action">
                                        <button type="button" class="btn btn-success"
                                            onclick="document.getElementById('ceo-status-{{ $travel->id }}').value='CEO Approved'; document.getElementById('ceo-form-{{ $travel->id }}').requestSubmit();">
                                            Approve
                                        </button>

                                        <button type="button" class="btn btn-error"
                                            onclick="document.getElementById('ceo-status-{{ $travel->id }}').value='Declined'; document.getElementById('ceo-form-{{ $travel->id }}').requestSubmit();">
                                            Decline
                                        </button>

                                        <button type="button" class="btn" onclick="document.getElementById('ceo-approve-{{ $travel->id }}').close();">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </dialog>

                       <!-- Edit & Delete modals (Admin) -->
                        <dialog id="edit-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box bg-base-100 text-base-content shadow-lg border border-base-300">
                                <h3 class="font-bold text-lg mb-4 text-center">Edit Travel</h3>

                                <form id="edit-form-{{ $travel->id }}" action="{{ route('travellist.update', $travel->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Date</label>
                                        <input type="date" name="travel_date" value="{{ $travel->travel_date }}" 
                                            class="input input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Requesting Parties (one per line)</label>
                                        <textarea name="request_parties" class="textarea textarea-bordered w-full" rows="4">{{ $travel->requestParties->pluck('name')->implode("\n") }}</textarea>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Purpose</label>
                                        <input type="text" name="purpose" value="{{ $travel->purpose }}" 
                                            class="input input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Destination</label>
                                        <input type="text" name="destination" value="{{ $travel->destination }}" 
                                            class="input input-bordered w-full" required />
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Transportation</label>
                                        <select name="transportation_id" class="select select-bordered w-full" required>
                                            @foreach($transportations as $transport)
                                                <option value="{{ $transport->id }}" {{ $travel->transportation_id == $transport->id ? 'selected' : '' }}>
                                                    {{ $transport->transportvehicle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Conditionalities</label>
                                        <select name="conditionalities" class="select select-bordered w-full" required>
                                            <option value="On Official Business" {{ $travel->conditionalities == 'On Official Business' ? 'selected' : '' }}>On Official Business</option>
                                            <option value="On Official Time" {{ $travel->conditionalities == 'On Official Time' ? 'selected' : '' }}>On Official Time</option>
                                            <option value="On Official Business and Time" {{ $travel->conditionalities == 'On Official Business and Time' ? 'selected' : '' }}>On Official Business and Time</option>
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Approver</label>
                                        <select name="faculty_id" class="select select-bordered w-full" required>
                                            @foreach($faculties as $faculty)
                                                <option value="{{ $faculty->id }}" {{ $travel->faculty_id == $faculty->id ? 'selected' : '' }}>
                                                    {{ $faculty->facultyname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Status</label>
                                        <select name="status" class="select select-bordered w-full" required>
                                            <option value="Pending" {{ $travel->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Supervisor Approved" {{ $travel->status == 'Supervisor Approved' ? 'selected' : '' }}>Supervisor Approved</option>
                                            <option value="CEO Approved" {{ $travel->status == 'CEO Approved' ? 'selected' : '' }}>CEO Approved</option>
                                            <option value="Declined" {{ $travel->status == 'Declined' ? 'selected' : '' }}>Declined</option>
                                        </select>
                                    </div>
                                </form>

                                <div class="modal-action">
                                    <form method="dialog"><button class="btn">Cancel</button></form>
                                    <button type="submit" form="edit-form-{{ $travel->id }}" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </dialog>


                        <dialog id="delete-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg mb-4">Delete Travel</h3>
                                <p>Are you sure you want to delete <b>{{ $travel->purpose }}</b>?</p>
                                <div class="modal-action">
                                    <form method="dialog"><button class="btn">Cancel</button></form>
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
                            <td colspan="5" class="text-center">No travel records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Travel Modal -->
        <dialog id="add-travel-modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Add Travel Order</h3>
                <form id="add-form" action="{{ route('travellist.store') }}" method="POST">
                    @csrf

                    <div class="form-control mb-3">
                        <label class="label">Date</label>
                        <input type="date" name="travel_date" class="input input-bordered w-full" required />
                    </div>

                    <div class="form-control mb-3">
                        <label class="label">Requesting Parties (one name per line)</label>
                        <textarea name="request_parties" class="textarea textarea-bordered w-full" rows="4" placeholder="Enter names...&#10;One per line" required></textarea>
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

                    @if(auth()->user()->role === 'CEO')
                        <div class="form-control mb-3">
                            <label class="label">Conditionalities</label>
                            <select name="conditionalities" class="select select-bordered w-full" required>
                                <option disabled selected>Choose Conditionalities</option>
                                <option value="On Official Business">On Official Business</option>
                                <option value="On Official Time">On Official Time</option>
                                <option value="On Official Business and Time">On Official Business and Time</option>
                            </select>
                        </div>
                    @endif

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

                <div class="modal-action">
                    <form method="dialog"><button class="btn">Cancel</button></form>
                    <button type="submit" form="add-form" class="btn btn-success">Save</button>
                </div>
            </div>
        </dialog>

    </div>

    <!-- JS -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectAll = document.getElementById("select-all");
        if (selectAll) {
            const checkboxes = document.querySelectorAll(".row-checkbox");
            selectAll.addEventListener("change", function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }
    });
    </script>
</x-app-layout>
