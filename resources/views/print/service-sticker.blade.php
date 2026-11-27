<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stiker Label {{ $ticket->ticket_number }} - {{ $store->store_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 1.5rem 1rem;
            background: #F1F5F9;
            color: #0F172A;
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #0F172A;
            color: #F8FAFC;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            max-width: 34rem;
            width: 100%;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.5rem;
            padding: 0 0.9rem;
            border-radius: 0.5rem;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #8FA0B8;
            background: transparent;
            color: #F8FAFC;
        }
        .btn-accent {
            background: #FB923C;
            color: #0F172A;
            border-color: transparent;
        }
        .sticker-sheet {
            width: {{ $widthMm }}mm;
            background: #FFFFFF;
            color: #000000;
            border: 2px solid #000000;
            border-radius: 6px;
            padding: {{ $widthMm === 80 ? '4mm' : '3mm' }};
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
        }
        .head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 2mm;
            margin-bottom: 2.5mm;
        }
        .brand {
            font-size: {{ $widthMm === 80 ? '11pt' : '9.5pt' }};
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .date {
            font-size: 7.5pt;
            font-family: ui-monospace, Consolas, monospace;
        }
        .ticket-code {
            font-family: ui-monospace, Consolas, monospace;
            font-size: {{ $widthMm === 80 ? '13.5pt' : '11pt' }};
            font-weight: 800;
            text-align: center;
            border: 1.5px solid #000000;
            padding: 1.5mm;
            margin-bottom: 2.5mm;
        }
        .body-row {
            display: flex;
            gap: 2.5mm;
            align-items: flex-start;
        }
        .qr-wrap {
            flex-shrink: 0;
            border: 1px solid #000000;
            padding: 1mm;
            background: #FFFFFF;
            line-height: 0;
        }
        .meta {
            flex: 1;
            min-width: 0;
            font-size: {{ $widthMm === 80 ? '8.5pt' : '7.5pt' }};
            line-height: 1.35;
        }
        .meta strong {
            display: block;
            font-size: {{ $widthMm === 80 ? '9.5pt' : '8.2pt' }};
        }
        .complaint {
            margin-top: 2mm;
            padding-top: 2mm;
            border-top: 1px dashed #000000;
            font-size: {{ $widthMm === 80 ? '8pt' : '7.2pt' }};
            line-height: 1.35;
        }
        .foot {
            margin-top: 2mm;
            padding-top: 1.5mm;
            border-top: 1px solid #000000;
            text-align: center;
            font-size: 7pt;
            font-weight: 700;
        }
        @media print {
            @page {
                size: {{ $widthMm }}mm auto;
                margin: 2mm;
            }
            body {
                background: #FFFFFF;
                padding: 0;
                display: block;
            }
            .no-print {
                display: none !important;
            }
            .sticker-sheet {
                width: 100%;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ route('staff.service-sticker', ['ticket' => $ticket->id, 'lebar' => 58]) }}" class="btn {{ $widthMm === 58 ? 'btn-accent' : '' }}">
            Kertas 58mm
        </a>
        <a href="{{ route('staff.service-sticker', ['ticket' => $ticket->id, 'lebar' => 80]) }}" class="btn {{ $widthMm === 80 ? 'btn-accent' : '' }}">
            Kertas 80mm
        </a>
        <button type="button" onclick="window.print()" class="btn btn-accent">
            Cetak Stiker Label
        </button>
        <button type="button" onclick="window.close()" class="btn">
            Tutup
        </button>
    </div>

    <section class="sticker-sheet" aria-label="Stiker Label Unit Servis {{ $ticket->ticket_number }}">
        <div class="head">
            <span class="brand">{{ strtoupper($store->store_name) }}</span>
            <span class="date">{{ $ticket->created_at?->format('d/m/y H:i') }}</span>
        </div>

        <div class="ticket-code">{{ $ticket->ticket_number }}</div>

        <div class="body-row">
            <div class="qr-wrap" aria-label="QR Code Lacak Resi {{ $ticket->ticket_number }}">
                {!! $qrSvg !!}
            </div>
            <div class="meta">
                <strong>{{ \Illuminate\Support\Str::limit($ticket->device_brand_model, 36) }}</strong>
                <div>Pemilik: {{ \Illuminate\Support\Str::limit($ticket->customer_name, 22) }}</div>
                <div>PIN WA: ****{{ $ticket->phoneLastFour() }}</div>
                <div>Jenis: {{ $ticket->visit_type === 'home_pickup' ? 'Jemput' : 'Workshop' }}</div>
            </div>
        </div>

        <div class="complaint">
            <strong>Keluhan:</strong> {{ \Illuminate\Support\Str::limit($ticket->complaint_summary, 90) }}
        </div>

        <div class="foot">
            PINDAI QR UNTUK CEK PROGRES &amp; SUHU SERVIS
        </div>
    </section>
</body>
</html>
