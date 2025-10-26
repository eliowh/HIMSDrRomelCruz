<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Receipt - {{ $billing->billing_number ?? '' }}</title>
    <style>
        /* Ensure body margins are small for receipt printing */
        body { font-family: Arial, sans-serif; margin: 10px; color: #000; }
        /* Force a consistent font size for PDF generation */
        .receipt-wrapper { font-size: 12px; }
        .no-print { display: none; }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        {{-- Include the compact receipt fragment which contains its own minimal styles --}}
        @include('billing.receipt_fragment')
    </div>

    @if(isset($autoPrint) && $autoPrint)
    <script>
        // Auto-print on load (used for the billing view "Print")
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 250);
        });
    </script>
    @endif
</body>
</html>
