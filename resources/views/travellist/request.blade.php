<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Order Request</title>
    @vite('resources/css/app.css') {{-- Tailwind CSS --}}
    <style>
        @media print {
            @page { margin: 20mm; }
            body { font-family: "Times New Roman", serif; font-size: 12pt; color: #000; }
            .no-print { display: none; }
        }
        .checkbox-box {
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 4px;
            vertical-align: middle;
        }
        .checked {
            background-color: #000;
        }
    </style>
</head>
<body class="bg-white text-black flex flex-col items-center">
    <div class="bg-white p-4 mx-auto mt-6 border border-black rounded shadow-lg"
         style="width:380px; min-height:600px;">
        
        {{-- HEADER --}}
        <header class="flex justify-between items-center mb-4 px-4">
            <div class="w-1/4 flex justify-start">
                <img src="{{ public_path('img/csulogo.png') }}" alt="Left Logo" class="h-10 w-auto">
            </div>

            <div class="w-2/4 text-center">
                <p class="font-bold text-[9px]">REPUBLIC OF THE PHILIPPINES</p>
                <p class="font-semibold text-[9px]">CAGAYAN STATE UNIVERSITY</p>
                <p class="text-[9px]">Lasam Campus, Lasam, Cagayan</p>
                <p class="italic text-[9px]">Institutional Email Address: lasam@csu.edu.ph</p>
            </div>

            <div class="w-1/4 flex justify-end">
                 <img src="{{ public_path('img/lasamlogo.png') }}" alt="Right Logo" class="h-10 w-auto">
            </div>
        </header>

        {{-- TITLE --}}
        <h1 class="text-lg font-bold text-center mb-4">Request for Travel Order</h1>

        {{-- TRAVEL ORDER CONTENT --}}
        <div class="text-[14px] space-y-2">
            <p><span class="font-semibold">Date of Travel:</span> 
                {{ \Carbon\Carbon::parse($travel->travel_from)->format('m-d-Y') }}</p>

            <p><span class="font-semibold">Requesting Parties:</span>
                @if($travel->requestParties && $travel->requestParties->isNotEmpty())
                    <ul class="list-disc pl-5">
                        @foreach($travel->requestParties as $party)
                            <li>{{ $party->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <span class="italic">None</span>
                @endif
            </p>

            <p><span class="font-semibold">Purpose:</span> {{ $travel->purpose }}</p>
            <p><span class="font-semibold">Destination:</span> {{ $travel->destination }}</p>
            <p><span class="font-semibold">Means of Transportation:</span> {{ $travel->transportation->transportvehicle ?? 'N/A' }}</p>

            {{-- CONDITIONALITIES WITH CHECKBOX --}}
            <p class="mt-2 font-semibold">Conditionalities:</p>
            <p class="ml-4">
                <span class="checkbox-box {{ $travel->conditionalities == 'On Official Business' || $travel->conditionalities == 'On Official Business and Time' ? 'checked' : '' }}"></span> On Official Business
            </p>
            <p class="ml-4">
                <span class="checkbox-box {{ $travel->conditionalities == 'On Official Time' || $travel->conditionalities == 'On Official Business and Time' ? 'checked' : '' }}"></span> On Official Time
            </p>
        </div>

        {{-- Recommending Approval --}}
        <div class="mt-4 text-[14px]">
            <p class="font-semibold">Recommending Approval:</p>
            <div class=" flex justify-end">
                @if($travel->faculty && $travel->faculty->signature && file_exists(storage_path('app/public/'.$travel->faculty->signature)))
                    <img src="{{ storage_path('app/public/'.$travel->faculty->signature) }}" alt="Faculty Signature" class="h-16 mb-1 mr-8">
                @endif
            </div>
            <p class="underline text-right mr-8">{{ $travel->faculty->facultyname ?? 'Immediate Supervisor' }}</p>

        </div>

        {{-- CEO Approval --}}
        <div class="mt-2 text-[14px]">
            <p class="font-semibold">Approved by:</p> {{-- stays at left --}}
            <div class="h-1 mb-1"></div> {{-- Blank signature space --}}
            <p class="font-bold underline text-center mt-1">{{ $travel->ceo->name ?? 'Campus Executive Officer' }}</p>
            <p class="text-sm text-center">Campus Executive Officer</p>
        </div>


    </div>
</body>
</html>
