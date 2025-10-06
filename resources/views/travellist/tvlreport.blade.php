@php
    use Illuminate\Support\Facades\Storage;

    // Inline the CSU logo as base64 so Browsershot doesn't try to fetch it over HTTP
    $logoPath = public_path('img/csulogo.png');
    $logoBase64 = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    // Inline CEO signature too if it exists
    $signaturePath = $travel->ceo_signature ? storage_path('app/public/' . $travel->ceo_signature) : null;
    $signatureBase64 = ($signaturePath && file_exists($signaturePath))
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
        : null;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Order</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: "Times New Roman", serif; font-size: 12pt; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .border-b { border-bottom: 1px solid #000; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-start { align-items: flex-start; }
        .w-1-3 { width: 33.3333%; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <header class="flex justify-between items-start" style="border-bottom:1px solid #000; padding-bottom:5px; margin-bottom:10px;">
        <div class="w-1-3" style="font-size:11px; line-height:1.2;">
            <p class="font-bold" style="border:1px solid #000; display:inline-block; padding:2px 6px; border-radius:4px;">F-OCEO-50004</p>
            <p style="font-weight:600; margin-top:4px;">REPUBLIC OF THE PHILIPPINES</p>
            <p class="font-bold">CAGAYAN STATE UNIVERSITY LASAM CAMPUS</p>
            <p style="font-style:italic;">Centro 02. Lasam, Cagayan</p>
        </div>

        <div class="w-1-3 text-center">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="CSU Logo" style="height:80px; object-fit:contain;">
            @endif
            <h1 class="font-bold" style="font-size:14px; margin-top:4px;">OFFICE OF THE CAMPUS EXECUTIVE OFFICER</h1>
        </div>

        <div class="w-1-3 text-right" style="font-size:10.5px; line-height:1.2;">
            <p><i>Email: lasam@csu.edu.ph</i></p>
            <p><i>Facebook: Office of the CEO-CSU Lasam</i></p>
            <p><i>Website: www.csu.edu.ph</i></p>
        </div>
    </header>

    {{-- TITLE --}}
    <div style="text-align:center; position:relative; margin-bottom:10px;">
        <h1 class="font-bold" style="font-size:20px; margin-bottom:10px;">TRAVEL ORDER</h1>
        <div style="position:absolute; right:0; top:0; font-size:14px; text-align:right;">
            <p style="font-weight:600;">OCEO-TO-{{ \Carbon\Carbon::parse($travel->created_at)->format('Y') }}-{{ str_pad($travel->id, 5, '0', STR_PAD_LEFT) }}</p>
            <p>Date: {{ \Carbon\Carbon::parse($travel->travel_from)->format('m-d-Y') }}</p>
        </div>
    </div>

    {{-- DETAILS --}}
    <div style="font-size:14px;">
        <div style="margin-top:8px;">
            <span style="display:inline-block; width:120px;">Applicant</span>
            <span>
                @foreach($travel->requestParties as $party)
                    : {{ $party->name }}<br>
                @endforeach
            </span>
        </div>

        <p style="margin-top:10px;"><span style="display:inline-block; width:120px;">Campus</span> : CSU-Lasam</p>
        <p style="margin-top:10px;"><span style="display:inline-block; width:120px;">Purpose</span> : {{ $travel->purpose }}</p>
        <p style="margin-top:10px;"><span style="display:inline-block; width:120px;">Destination</span> : {{ $travel->destination }}</p>
        <p style="margin-top:10px;"><span style="display:inline-block; width:120px;">Date of Travel</span> : {{ \Carbon\Carbon::parse($travel->travel_date)->format('F d, Y') }}</p>
    </div>

    {{-- TRANSPORTATION --}}
    <div style="margin-top:20px; font-size:14px;">
        <p class="font-bold">Transportation Vehicle:</p>
        <div>
            <span style="margin-right:15px;">
                <span style="width:12px; height:12px; border:1px solid #000; display:inline-block; margin-right:5px; {{ $travel->transportation->transportvehicle == 'Campus Vehicle' ? 'background-color:#000;' : '' }}"></span>
                Campus Vehicle
            </span>
            <span style="margin-right:15px;">
                <span style="width:12px; height:12px; border:1px solid #000; display:inline-block; margin-right:5px; {{ $travel->transportation->transportvehicle == 'PUV' ? 'background-color:#000;' : '' }}"></span>
                PUV
            </span>
            <span>
                <span style="width:12px; height:12px; border:1px solid #000; display:inline-block; margin-right:5px; {{ $travel->transportation->transportvehicle == 'Personal Vehicle' ? 'background-color:#000;' : '' }}"></span>
                Personal Vehicle
            </span>
        </div>
    </div>

    {{-- CONDITIONALITIES --}}
    <div style="margin-top:20px; font-size:14px; text-align:justify;">
        <strong>Conditionalities</strong> :
        @if($travel->conditionalities == 'On Official Business')
            On <span class="underline font-bold">Official Business</span>, all expenses incurred thereto are chargeable against the Campus Fund, subject to availability under the usual accounting and auditing regulations.
        @elseif($travel->conditionalities == 'On Official Time')
            On <span class="underline font-bold">Official Time</span>.
        @elseif($travel->conditionalities == 'On Official Business and Time')
            On <span class="underline font-bold">Official Business and Time</span>.
        @else
            N/A
        @endif
    </div>

    <p style="margin-top:10px; font-size:14px;">A report should be submitted upon termination of the mission for which this travel order was issued.</p>

    {{-- APPROVAL --}}
    <div style="margin-top:20px; text-align:center;">
        <p style="font-weight:600; text-align:left; margin-left:34%;">Approved:</p>
        @if($signatureBase64)
            <img src="{{ $signatureBase64 }}" alt="CEO Signature" style="height:60px;">
            <p class="font-bold underline">{{ $travel->ceo->name ?? 'Campus Executive Officer' }}</p>
        @else
            <p class="font-bold underline">{{ $travel->ceo->name ?? 'Campus Executive Officer' }}</p>
        @endif
        <p style="font-size:12px;">Campus Executive Officer</p>
    </div>

    {{-- CERTIFICATION --}}
    <div style="margin-top:20px;">
        <div style="border-top:2px dashed #3b82f6; width:100%; margin-bottom:10px;"></div>
        <p style="font-weight:600;">Appearance certified:</p>
        <div style="margin-top:20px; text-align:center;">
            <div class="border-b" style="width:250px; margin:0 auto 5px;"></div>
            <p style="font-size:12px;">Name / Signature</p>
        </div>
        <div style="margin-top:15px; text-align:center;">
            <div class="border-b" style="width:250px; margin:0 auto 5px;"></div>
            <p style="font-size:12px;">Office / Agency</p>
        </div>
    </div>

</body>
</html>
