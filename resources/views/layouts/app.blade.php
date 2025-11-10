<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            background: #f4f6f8;
        }

        nav {
            width: 250px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        nav img {
            width: 100px;
            margin-bottom: 10px;
        }

        nav h2 {
            font-size: 16px;
            margin: 0 0 20px;
        }

        nav ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        nav ul li {
            width: 100%;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.3s ease;
        }

        nav ul li a:hover {
            background: #34495e;
        }
        
    </style>
    @stack('styles')
</head>
<body>
<nav>
    <img src="/images/logo.png" alt="Logo">
    <h2>IT DEPARTMENT</h2>
    <ul>
        <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/add-new-user"><i class="fas fa-user-plus"></i> Add New User</a></li>
        <li><a href="/manage-room"><i class="fas fa-door-open"></i> Room Management</a></li>
        <li><a href="/categories"><i class="fas fa-layer-group"></i> Categories</a></li>
        <li><a href="/maintenance"><i class="fas fa-tools"></i> Maintenance</a></li>
        <li><a href="/borrow"><i class="fas fa-handshake"></i> Borrow</a></li>
        <li><a href="/print-report"><i class="fas fa-print"></i> Print Report</a></li>
        <li><a href="/scan-barcode"><i class="fas fa-barcode"></i> Scan Barcode</a></li>
    </ul>
</nav>



    @yield('content')
</div>

<script>
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('datetime').innerText = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

@stack('scripts')
</body>
</html>
