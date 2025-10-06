<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Nurse Requests</title>
    <link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @include('pharmacy.pharmacy_header')
    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')
        <main class="main-content">
            <div class="page-header">
                <h2>Nurse Medicine Requests</h2>
            </div>

            <div class="pharmacy-card">
                @if($orders->count() > 0)
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Patient</th>
                                <th>Item Code</th>
                                <th>Medicine</th>
                                <th>Qty</th>
                                <th>Requested By</th>
                                <th>Requested At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->patient_name ?? '-' }}</td>
                                <td>{{ $order->item_code ?? '-' }}</td>
                                <td>{{ $order->generic_name ?? $order->brand_name ?? '-' }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td>{{ optional($order->user)->name }}</td>
                                <td>{{ $order->requested_at ? $order->requested_at->format('M d, Y h:i A') : '-' }}</td>
                                <td>{{ $order->formatted_status ?? ucfirst($order->status) }}</td>
                                <td>
                                    @if($order->status === 'pending')
                                        <button class="btn pharmacy-btn-secondary btn-sm" onclick="viewRequest({{ $order->id }})">View</button>
                                    @else
                                        <button class="btn pharmacy-btn-info btn-sm" onclick="viewRequest({{ $order->id }})">View</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="pagination-wrapper">
                        <x-custom-pagination :paginator="$orders" />
                    </div>
                @else
                    <div class="no-orders">
                        <h3>No Nurse Requests</h3>
                        <p>No medicine requests submitted by nurses at this time.</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    @include('pharmacy.modals.notification_system')

    <script>
        function viewRequest(id){
            fetch(`/pharmacy/orders/${id}`)
                .then(r=>r.json())
                .then(j=>{
                    if(j.success){
                        const o = j.order;
                        alert(`Request ${o.id} - ${o.generic_name || o.brand_name}\nQty: ${o.quantity}\nNotes: ${o.notes || '-'}\nStatus: ${o.status}`);
                    } else alert('Failed to load request');
                }).catch(e=>{ console.error(e); alert('Error loading'); });
        }
    </script>
</body>
</html>