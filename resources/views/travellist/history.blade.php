<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h1 class="text-3xl font-bold text-base-content">
                Travel History
            </h1>
            <a href="{{ route('travellist.index') }}" 
               class="link link-primary text-sm sm:text-base hover:opacity-80">
               ← Back to Travel List
            </a>
        </div>

        <!-- Empty State -->
        @if($travelHistory->isEmpty())
            <div class="text-center py-16 flex flex-col items-center justify-center">
                <div class="text-6xl mb-4 opacity-70">🗂️</div>
                <h2 class="text-lg font-semibold text-base-content/80">
                    No travel history found
                </h2>
                <p class="text-sm text-base-content/60 mt-1">
                    Your previous travel requests will appear here once you start submitting them.
                </p>
            </div>
        @else
            <!-- Timeline Container -->
            <div class="relative border-l border-base-300 dark:border-base-200 ml-5 space-y-8">
                @foreach($travelHistory as $travel)
                    <div class="relative pl-6">
                        <!-- Timeline Dot -->
                        <span class="absolute -left-[14px] flex h-6 w-6 items-center justify-center rounded-full bg-base-100 border-2 border-primary">
                            <span class="h-3 w-3 rounded-full bg-primary"></span>
                        </span>

                        <!-- Travel Card -->
                        <div class="card bg-base-100 shadow-md border border-base-300 hover:shadow-lg transition-all duration-200">
                            <div class="card-body p-5 space-y-3">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                                    <h3 class="card-title text-lg sm:text-xl text-base-content">
                                        {{ $travel->destination ?? 'Unknown Destination' }}
                                    </h3>

                                    <!-- Status Badge -->
                                    <div>
                                        <span class="badge badge-outline 
                                            @if($travel->status === 'CEO Approved') badge-success
                                            @elseif($travel->status === 'Cancelled') badge-error
                                            @elseif($travel->status === 'Recommended for Approval') badge-info
                                            @else badge-warning
                                            @endif">
                                            {{ $travel->status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Purpose -->
                                <p class="text-sm text-base-content/70 leading-relaxed">
                                    <b>Purpose:</b> {{ $travel->purpose ?? 'No purpose specified' }}
                                </p>

                                <!-- Cancellation Reason -->
                                @if($travel->cancellation_reason ?? false)
                                    <div class="alert alert-error text-sm p-3 rounded-lg">
                                        <span><b>Reason for Cancellation:</b> {{ $travel->cancellation_reason }}</span>
                                    </div>
                                @endif

                                <div class="divider my-1"></div>

                                <!-- Dates -->
                                <div class="text-xs text-base-content/60 flex flex-col sm:flex-row justify-between gap-1">
                                    <span>Requested: {{ $travel->created_at->format('M d, Y - h:i A') }}</span>
                                    @if($travel->updated_at)
                                        <span>Updated: {{ $travel->updated_at->format('M d, Y - h:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
