<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Account</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Fallback for production -->
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('Inventory.inventory_header')
    <div class="inventory-layout">
        @include('Inventory.inventory_sidebar')
        <main class="main-content">
            <div class="inventory-card">
                <h2>Account</h2>
                <p>Inventory user account settings will appear here.</p>
            </div>
        </main>
    </div>
</body>
</html>
