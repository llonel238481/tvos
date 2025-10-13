<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Travel Order List</h1>

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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
            <form action="{{ route('travellist.index') }}" method="GET" class="flex flex-wrap gap-2 w-full sm:w-auto">
                <div class="form-control w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by request or purpose" class="input input-bordered w-full" />
                </div>

                <div class="form-control w-full sm:w-48">
                    <select name="status" class="select select-bordered w-full">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Recommended for Approval" {{ request('status') == 'Recommended for Approval' ? 'selected' : '' }}>Recommended for Approval</option>
                        <option value="CEO Approved" {{ request('status') == 'CEO Approved' ? 'selected' : '' }}>CEO Approved</option>       
                    </select>
                </div>

                <button type="submit" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></button>
            </form>

            @if(in_array(auth()->user()->role, ['Admin', 'Employee']))
                <button class="btn btn-success w-full sm:w-auto" onclick="document.getElementById('add-travel-modal').showModal()">
                    + Request Travel Order
                </button>
            @endif 
        </div>

        <!-- Travel Table -->
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Departure</th>
                        <th>Return</th>
                        <th>Purpose</th>
                        <th>Requesting Parties</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Approver</th>
                        <th>CEO</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travel_lists as $index => $travel)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">{{ $travel->travel_from }}</td>
                            <td class="whitespace-nowrap">{{ $travel->travel_to }}</td>
                            <td class="whitespace-nowrap">{{ $travel->purpose }}</td>

                            <!-- Requesting Parties -->
                            <td class="whitespace-nowrap">
                                @if($travel->requestParties && $travel->requestParties->isNotEmpty())
                                    <ol class="list-decimal pl-5">
                                        @foreach($travel->requestParties as $party)
                                            <li>{{ $party->name }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <span class="italic text-gray-500">None</span>
                                @endif
                            </td>


                            <!-- Destination -->
                            <td class="whitespace-nowrap">{{ $travel->destination }}</td>

                            <!-- Status -->
                            <td >
                                <span class="inline-block px-2 py-1 text-xs sm:text-sm font-semibold rounded 
                                    @if($travel->status == 'Pending') bg-yellow-500 text-white
                                    @elseif($travel->status == 'Recommended for Approval') bg-blue-500 text-white
                                    @elseif($travel->status == 'CEO Approved') bg-green-500 text-white
                                    @elseif($travel->status == 'Declined') bg-red-500 text-white
                                    @else bg-gray-400 text-white
                                    @endif
                                    text-center">
                                    {{ $travel->status }}
                                </span>
                            </td>

                            <!-- Approver -->
                            <td class="whitespace-nowrap">{{ $travel->faculty->facultyname ?? 'N/A' }}</td>

                            <!-- CEO -->
                            <td class="whitespace-nowrap">{{ $travel->ceo->name ?? 'N/A' }}</td>

                            <!-- Actions -->
                            <td class="px-4 py-2 flex flex-col sm:flex-row gap-2">
                                <button class="btn btn-sm btn-outline w-full sm:w-auto" 
                                    onclick="document.getElementById('view-travel-{{ $travel->id }}').showModal()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg></button>

                                @if(auth()->user()->role === 'Supervisor' && $travel->status === 'Pending')
                                    <form action="{{ route('travellist.supervisor.approve', $travel->id) }}" method="POST" class="w-full sm:w-auto">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary w-full"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg></button>
                                    </form>
                                @endif

                                @if(auth()->user()->role === 'CEO' && $travel->status === 'Recommended for Approval')
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button class="btn btn-sm btn-primary w-full sm:w-auto"
                                            onclick="document.getElementById('ceo-review-{{ $travel->id }}').showModal()">
                                            Review
                                        </button>

                                        <!-- 🟥 Cancel Button -->
                                        <button class="btn btn-sm btn-error w-full sm:w-auto" 
                                            onclick="document.getElementById('cancel-travel-{{ $travel->id }}').showModal()">
                                            Cancel
                                        </button>
                                    </div>
                                @endif

                                @if(auth()->user()->role === 'Admin')
                                    <button class="btn btn-sm btn-primary w-full sm:w-auto" 
                                        onclick="document.getElementById('edit-travel-{{ $travel->id }}').showModal()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg></button>
                                    <button class="btn btn-sm btn-error w-full sm:w-auto" 
                                        onclick="document.getElementById('delete-travel-{{ $travel->id }}').showModal()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                @endif

                                 @if($travel->status === 'CEO Approved')
                                    <a href="{{ route('report.download', $travel->id) }}" 
                                    class="btn btn-sm btn-success w-full sm:w-auto text-center" 
                                    target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg></a>
                                @endif
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <dialog id="view-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box max-w-3xl bg-base-100 text-base-content rounded-lg relative">
                                <button type="button" class="btn btn-sm btn-circle absolute right-2 top-2" onclick="document.getElementById('view-travel-{{ $travel->id }}').close();">✕</button>
                                <h3 class="font-bold text-xl mb-4 text-center border-b border-base-300 pb-2 dark:border-base-100">Travel Order Details</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Date of Travel</p>
                                        <p>{{ $travel->travel_from }} to {{ $travel->travel_to }}</p>
                                    </div>

                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Status</p>
                                        <p>{{ $travel->status }}</p>
                                    </div>

                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold mb-1">Requesting Parties</p>
                                        @if($travel->requestParties && $travel->requestParties->isNotEmpty())
                                            <ul class="list-decimal pl-5 space-y-1">
                                                @foreach($travel->requestParties as $party)
                                                    <li>{{ $party->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="italic text-gray-500 dark:text-gray-400">No requesting parties listed</p>
                                        @endif
                                    </div>

                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Purpose</p>
                                        <p>{{ $travel->purpose }}</p>
                                    </div>

                                    <div class="md:col-span-2 p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Destination</p>
                                        <p>{{ $travel->destination }}</p>
                                    </div>

                                    <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100">
                                        <p class="font-semibold">Transportation</p>
                                        <p>{{ $travel->transportation->transportvehicle ?? 'No Transportation' }}</p>
                                    </div>

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

                                    <!-- Signatures -->
                                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                        
                                        <!-- Supervisor Signature -->
                                        <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100 text-center">
                                            <p class="font-semibold text-left mb-3">Recommend Approver:</p>
                                            <p class="underline">{{ $travel->faculty->facultyname ?? 'No Approver' }}</p>

                                            @php
                                                $user = Auth::user();
                                            @endphp
                                            {{-- 🧾 Supervisor Signature (Visible only for non-Employees) --}}
                                            @if($user->role !== 'Employee')
                                                <p class="font-semibold mt-2 mb-2 text-left">Supervisor Signature</p>
                                                @if($travel->supervisor_signature)
                                                    <img src="{{ asset('storage/' . $travel->supervisor_signature) }}" 
                                                        alt="Supervisor Signature" 
                                                        class="max-h-28 mx-auto border border-base-300 dark:border-base-100 rounded-md">
                                                @else
                                                    <p class="text-sm text-gray-500 italic">No Supervisor Signature</p>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- CEO Signature -->
                                        <div class="p-3 bg-base-200 dark:bg-base-300 rounded-lg border border-base-300 dark:border-base-100 text-center">
                                            <p class="font-semibold text-left">CEO: </p>
                                            <p class="underline">{{ $travel->ceo->name ?? 'No CEO Assigned' }}</p>

                                            @php
                                                $user = Auth::user();
                                            @endphp

                                            {{-- 🖋 CEO Signature (Hidden from Employees) --}}
                                            @if($user->role !== 'Employee')
                                                <p class="font-semibold mt-2 mb-2 text-left">CEO Signature</p>
                                                @if($travel->ceo_signature)
                                                    <img src="{{ asset('storage/' . $travel->ceo_signature) }}" 
                                                        alt="CEO Signature" 
                                                        class="max-h-28 mx-auto border border-base-300 dark:border-base-100 rounded-md">
                                                @else
                                                    <p class="text-sm text-gray-500 italic">No CEO Signature</p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @auth
                                    @if(auth()->user()->role === 'Supervisor' || auth()->user()->role === 'Admin')
                                        <div class="modal-action mt-6">
                                            <form method="dialog">
                                                <button class="btn" title="Close">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x">
                                                        <circle cx="12" cy="12" r="10"/>
                                                        <path d="m15 9-6 6"/>
                                                        <path d="m9 9 6 6"/>
                                                    </svg>
                                                </button>
                                            </form> 

                                            <a href="{{ route('report.preview', $travel->id) }}" class="btn btn-success" target="_blank" title="Download Travel Order">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-down">
                                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                                    <path d="M12 18v-6"/>
                                                    <path d="m9 15 3 3 3-3"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                @endauth
                                
                               
                            </div>
                        </dialog>
                        

                        <!-- Edit Travel Modal -->
                        <dialog id="edit-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box max-w-2xl bg-base-100 text-base-content shadow-xl rounded-2xl border border-base-300">
                                <!-- Close Button -->
                                <button type="button"
                                    class="btn btn-sm btn-circle absolute right-3 top-3"
                                    onclick="document.getElementById('edit-travel-{{ $travel->id }}').close();">
                                    ✕
                                </button>

                                <!-- Header -->
                                <div class="mb-5 text-center">
                                    <h3 class="text-2xl font-bold">Edit Travel Order</h3>
                                    <p class="text-sm text-gray-500">Update the details below and save your changes</p>
                                </div>

                                <!-- Form -->
                                <form id="edit-form-{{ $travel->id }}" action="{{ route('travellist.update', $travel->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <!-- Dates -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="label font-semibold">Date From</label>
                                            <input type="date" name="travel_from" value="{{ $travel->travel_from }}" class="input input-bordered w-full" required>
                                        </div>
                                        <div>
                                            <label class="label font-semibold">Date To</label>
                                            <input type="date" name="travel_to" value="{{ $travel->travel_to }}" class="input input-bordered w-full" required>
                                        </div>
                                    </div>

                                    <!-- Requesting Parties -->
                                    <div>
                                        <label class="label font-semibold">Requesting Parties</label>
                                        <textarea name="request_parties" class="textarea textarea-bordered w-full" rows="4" placeholder="Enter one name per line">{{ $travel->requestParties->pluck('name')->implode("\n") }}</textarea>
                                    </div>

                                    <!-- Purpose -->
                                    <div>
                                        <label class="label font-semibold">Purpose</label>
                                        <input type="text" name="purpose" value="{{ $travel->purpose }}" class="input input-bordered w-full" placeholder="Purpose of travel" required>
                                    </div>

                                    <!-- Destination -->
                                    <div>
                                        <label class="label font-semibold">Destination</label>
                                        <input type="text" name="destination" value="{{ $travel->destination }}" class="input input-bordered w-full" placeholder="Enter destination" required>
                                    </div>

                                    <!-- Transportation / Approver / CEO -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="label font-semibold">Transportation</label>
                                            <select name="transportation_id" class="select select-bordered w-full" required>
                                                @foreach($transportations as $transport)
                                                    <option value="{{ $transport->id }}" {{ $travel->transportation_id == $transport->id ? 'selected' : '' }}>
                                                        {{ $transport->transportvehicle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="label font-semibold">Approver</label>
                                            <select name="faculty_id" class="select select-bordered w-full" required>
                                                @foreach($faculties as $faculty)
                                                    <option value="{{ $faculty->id }}" {{ $travel->faculty_id == $faculty->id ? 'selected' : '' }}>
                                                        {{ $faculty->facultyname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="label font-semibold">CEO</label>
                                            <select name="ceo_id" class="select select-bordered w-full">
                                                @foreach($ceos as $ceo)
                                                    <option value="{{ $ceo->id }}" {{ $travel->ceo_id == $ceo->id ? 'selected' : '' }}>
                                                        {{ $ceo->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </form>

                                <!-- Actions -->
                                <div class="modal-action mt-6">
                                    <button type="button" class="btn" onclick="document.getElementById('edit-travel-{{ $travel->id }}').close();">
                                        Cancel
                                    </button>
                                    <button type="submit" form="edit-form-{{ $travel->id }}" class="btn btn-success">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </dialog>


                        <!-- CEO Review Modal -->
                        <dialog id="ceo-review-{{ $travel->id }}" class="modal">
                            <div class="modal-box relative bg-base-100 text-base-content shadow-lg border border-base-300">
                                <button type="button" class="btn btn-sm btn-circle absolute right-2 top-2" 
                                    onclick="document.getElementById('ceo-review-{{ $travel->id }}').close();">✕</button>
                                <h3 class="font-bold text-lg mb-4 text-center">CEO Review</h3>

                                <form action="{{ route('travellist.ceo.approve', $travel->id) }}" method="POST">
                                    @csrf

                                    <div class="form-control mb-3">
                                        <label class="label font-semibold">Conditionalities</label>
                                        <select name="conditionalities" class="select select-bordered w-full" required>
                                            <option disabled selected>Choose conditionalities</option>
                                            <option value="On Official Business" 
                                                {{ $travel->conditionalities === 'On Official Business' ? 'selected' : '' }}>
                                                On Official Business
                                            </option>
                                            <option value="On Official Time" 
                                                {{ $travel->conditionalities === 'On Official Time' ? 'selected' : '' }}>
                                                On Official Time
                                            </option>
                                            <option value="On Official Business and Time" 
                                                {{ $travel->conditionalities === 'On Official Business and Time' ? 'selected' : '' }}>
                                                On Official Business and Time
                                            </option>
                                        </select>
                                    </div>

                                    <div class="modal-action">
                                        <button type="button" class="btn" 
                                            onclick="document.getElementById('ceo-review-{{ $travel->id }}').close();">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </div>
                                </form>
                            </div>
                        </dialog>

                        <!-- Delete Modal -->
                        <dialog id="delete-travel-{{ $travel->id }}" class="modal">
                            <div class="modal-box relative">
                                <button type="button" class="btn btn-sm btn-circle absolute right-2 top-2" onclick="document.getElementById('delete-travel-{{ $travel->id }}').close();">✕</button>
                                <h3 class="font-bold text-lg mb-4">Delete Travel Order</h3>
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

                     <!-- Cancel Modal -->
                    <dialog id="cancel-travel-{{ $travel->id }}" class="modal">
                    <div class="modal-box w-full max-w-md">
                        <!-- Header -->
                        <h3 class="font-bold text-lg text-error mb-2">Cancel Travel Request</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Please provide a reason before cancelling. This action cannot be undone.
                        </p>

                        <!-- Reason Form -->
                        <form method="POST" action="{{ route('travellist.cancel', $travel->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-control mb-4">
                            <label class="label" for="reason-{{ $travel->id }}">
                            <span class="label-text font-semibold">Reason for Cancellation</span>
                            </label>
                            <textarea
                            name="reason"
                            id="reason-{{ $travel->id }}"
                            class="textarea textarea-bordered w-full"
                            rows="3"
                            placeholder="Enter reason..."
                            required
                            ></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="modal-action">
                            <button type="button"
                            class="btn btn-ghost"
                            onclick="document.getElementById('cancel-travel-{{ $travel->id }}').close();">
                            Keep Request
                            </button>

                            <button type="submit" class="btn btn-error text-white">
                            Confirm Cancel
                            </button>
                        </div>
                        </form>
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

        <script>
        document.querySelectorAll('[id^="cancel-travel-"]').forEach(modal => {
            modal.addEventListener('show', () => {
                modal.querySelector('textarea')?.focus();
            });
        });
        </script>

        <div class="flex justify-end items-center mb-6">
            <a href="{{ route('travellist.history') }}"
            class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm md:text-base transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 10h8l3-10h4" />
                </svg>
                View Travel History
            </a>
        </div>

        <!-- 🌿 Pagination -->
        <div class="mt-4 flex justify-end">
            {{ $travel_lists->links('pagination::tailwind') }}
        </div>

       

                {{-- Add Modal --}}
                <dialog id="add-travel-modal" class="modal">
                <div class="modal-box max-w-2xl relative">
                    <!-- Close Button -->
                    <button type="button" class="btn btn-sm btn-circle absolute right-2 top-2" 
                        onclick="document.getElementById('add-travel-modal').close();">✕</button>

                    <!-- Title -->
                    <h3 class="font-bold text-xl mb-4 text-center"> Request Travel Order</h3>

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="alert alert-error mb-4">
                            <ul class="list-disc pl-5 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form -->
                    <form id="add-form" action="{{ route('travellist.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Dates Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label font-semibold">Date From</label>
                                <input type="date" name="travel_from" value="{{ old('travel_from') }}" class="input input-bordered w-full" required />
                            </div>

                            <div class="form-control">
                                <label class="label font-semibold">Date To</label>
                                <input type="date" name="travel_to" value="{{ old('travel_to') }}" class="input input-bordered w-full" required />
                            </div>
                        </div>

                        <!-- Requesting Parties -->
                        <div class="form-control w-full">
                            <label class="label font-semibold">Requesting Parties</label>
                            <div id="request-parties-container" class="flex flex-wrap gap-2 border rounded p-2 min-h-[48px]">
                                <!-- Chips will appear here -->
                            </div>
                            <input type="text" id="party-input" placeholder="Type a name and press Enter" 
                                class="input input-bordered w-full mt-2" autocomplete="off">
                            <!-- Hidden input to submit the values as JSON -->
                            <input type="hidden" name="request_parties" id="request-parties-hidden">
                        </div>

                        <script>
                        const partyInput = document.getElementById('party-input');
                        const partiesContainer = document.getElementById('request-parties-container');
                        const hiddenInput = document.getElementById('request-parties-hidden');
                        let parties = [];

                        // Function to update the hidden input for form submission
                        function updateHiddenInput() {
                            hiddenInput.value = JSON.stringify(parties); // store as JSON
                        }

                        // Function to create a chip for a name
                        function createChip(name) {
                            const chip = document.createElement('span');
                            chip.className = 'badge badge-primary flex items-center gap-2';
                            chip.textContent = name;

                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'text-white font-bold';
                            removeBtn.textContent = '×';
                            removeBtn.onclick = () => {
                                parties = parties.filter(p => p !== name);
                                chip.remove();
                                updateHiddenInput();
                            };

                            chip.appendChild(removeBtn);
                            partiesContainer.appendChild(chip);
                        }

                        // Add name when Enter is pressed
                        partyInput.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' && partyInput.value.trim() !== '') {
                                e.preventDefault();
                                const name = partyInput.value.trim();

                                // Avoid duplicates
                                if (!parties.includes(name)) {
                                    parties.push(name);
                                    createChip(name);
                                    updateHiddenInput();
                                }

                                partyInput.value = '';
                            }
                        });

                        // Optional: Load existing parties if editing
                        document.addEventListener('DOMContentLoaded', () => {
                            const existingParties = hiddenInput.value ? JSON.parse(hiddenInput.value) : [];
                            existingParties.forEach(name => {
                                parties.push(name);
                                createChip(name);
                            });
                        });
                        </script>



                        <!-- Purpose & Destination -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label font-semibold">Purpose</label>
                                <input type="text" name="purpose" value="{{ old('purpose') }}" class="input input-bordered w-full" placeholder="e.g., Official meeting" required />
                            </div>

                            <div class="form-control">
                                <label class="label font-semibold">Destination</label>
                                <input type="text" name="destination" value="{{ old('destination') }}" class="input input-bordered w-full" placeholder="e.g., Manila" required />
                            </div>
                        </div>

                        <!-- Transportation -->
                        <div class="form-control">
                            <label class="label font-semibold">Transportation</label>
                            <select name="transportation_id" class="select select-bordered w-full" required>
                                <option value="" selected disabled>Choose transportation</option>
                                @foreach($transportations as $transport)
                                    <option value="{{ $transport->id }}" {{ old('transportation_id') == $transport->id ? 'selected' : '' }}>
                                        {{ $transport->transportvehicle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Approvers -->
                        {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label font-semibold">Recommending Approval</label>
                                <select name="faculty_id" class="select select-bordered w-full" required>
                                    <option value="" selected disabled>Choose Approver</option>
                                    @foreach($faculties as $faculty)
                                        <option value="{{ $faculty->id }}" {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                            {{ $faculty->facultyname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label font-semibold">CEO</label>
                                <select name="ceo_id" class="select select-bordered w-full">
                                    <option value="" selected disabled>Choose CEO</option>
                                    @foreach($ceos as $ceo)
                                        <option value="{{ $ceo->id }}" {{ old('ceo_id') == $ceo->id ? 'selected' : '' }}>
                                            {{ $ceo->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}

                        <!-- Action Buttons -->
                        <div class="modal-action">
                            <button type="button" class="btn" onclick="document.getElementById('add-travel-modal').close();">Cancel</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </dialog>



    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modals = document.querySelectorAll('dialog.modal');
            modals.forEach(modal => {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) modal.close();
                });
            });
        });
    </script>
</x-app-layout>
Sent 1m ago
