{{-- dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            background: radial-gradient(1200px 600px at 70% 30%, #1b0f1f 0%, #140a18 40%, #0f0813 100%);
            color: #ffe9cc;
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

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 12px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .topbar .datetime {
            font-weight: bold;
            font-size: 16px;
        }

        .topbar .account {
            position: relative;
            font-weight: bold;
        }

        .topbar .account button {
            background: none;
            border: none;
            color: #2c3e50;
            cursor: pointer;
            font-size: 16px;
        }

        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 10px;
            border-radius: 5px;
            z-index: 999;
        }

        .account:hover .dropdown {
            display: block;
        }

        .activity-panel {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .activity-box {
            background: linear-gradient(180deg, rgba(40,18,26,0.9) 0%, rgba(30,12,20,0.9) 100%);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 0 0 1px rgba(255, 140, 0, 0.25), 0 0 18px rgba(255, 140, 0, 0.08) inset;
            color: #ffd7a1;
        }

        .chart-container {
            height: 200px;
            background: linear-gradient(180deg, rgba(40,18,26,0.9) 0%, rgba(30,12,20,0.9) 100%);
            border-radius: 12px;
            box-shadow: 0 0 0 1px rgba(255, 140, 0, 0.25), 0 0 22px rgba(255, 140, 0, 0.08) inset;
            padding: 8px;
            margin-bottom: 8px;
        }

        canvas {
            max-width: 100%;
            width: 100% !important;
            height: 100% !important;
        }

        .hud-row {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 8px;
        }
        .col-4 { grid-column: span 4; }
        .col-6 { grid-column: span 6; }
        .col-12 { grid-column: span 12; }
        .hud-title {
            color: #ffae52;
            letter-spacing: 1px;
            margin: 0 0 12px 0;
            font-weight: 700;
        }

        #contributionModal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        #contributionModal .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            position: relative;
        }

        #contributionModal .close {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .contribution-btn {
            padding: 10px 20px;
            font-size: 14px;
            background: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .contribution-btn:hover {
            background: #3498db;
        }

        /* Profile header above Usable Peripherals */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 8px 0 14px 0;
        }
        .profile-header img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 0 2px rgba(255, 140, 0, 0.35);
        }
        .profile-header .name {
            font-weight: 800;
            letter-spacing: .3px;
            color: #ffd7a1;
            text-shadow: 0 0 8px rgba(255, 140, 0, 0.2);
        }

        /* Topbar right group (profile + dropdown) */
        .topbar-right { display:flex; align-items:center; gap: 12px; }
        .topbar-profile { display:flex; align-items:center; gap:8px; }
        .topbar-profile img { width:36px; height:36px; border-radius:50%; object-fit:cover; box-shadow: 0 0 0 2px rgba(255, 140, 0, 0.25); }
        .topbar-profile .name { color:#ffe9cc; font-weight:700; font-size:14px; }

        /* Profile Modal */
        #profileModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:10000; }
        #profileModal .modal-content { background:#fff; width: 92%; max-width:520px; margin: 8% auto; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,.35); position:relative; padding:20px; }
        #profileModal .close { position:absolute; right:14px; top:10px; font-size:26px; color:#999; cursor:pointer; }
        .profile-form .row { display:flex; gap:12px; margin-bottom:12px; align-items:center; }
        .profile-form label { width: 120px; font-weight:700; color:#111827; }
        .profile-form input { flex:1; padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px; }
        .profile-avatar { display:flex; align-items:center; gap:12px; }
        .profile-avatar img { width:72px; height:72px; border-radius:50%; object-fit:cover; }
    </style>
</head>
<body>

<nav>
   <img src="{{ asset('images/logo.png') }}" alt="Logo" width="150">
    <h2>IT DEPARTMENT</h2>
    <ul>
    <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li><a href="/add-new-user"><i class="fas fa-user-plus"></i> Add New User</a></li>
    <li><a href="/manage-room"><i class="fas fa-door-open"></i> Room Management</a></li>
    <li><a href="{{ url('/categories') }}"><i class="fas fa-layer-group"></i> Categories</a></li>
    <li><a href="/maintenance"><i class="fas fa-tools"></i> Maintenance</a></li>
    <li><a href="/borrow"><i class="fas fa-handshake"></i> Borrow</a></li>
    <li><a href="/print-report"><i class="fas fa-print"></i> Print Report</a></li>
 <li><a href="/scan-barcode"><i class="fas fa-barcode"></i> Scan Barcode</a></li>

</ul>

</nav>

<div class="container">
    <div class="topbar">
        <div class="datetime" id="datetime"></div>
        <div class="topbar-right">
            <div class="topbar-profile">
                <img src="{{ asset('photos/' . ($user->photo ?? '')) }}" onerror="this.src='{{ asset('images/logo.png') }}'" alt="Profile">
                <div class="name">{{ $user->name ?? $user->full_name }}</div>
            </div>
            <div class="account">
                <button onclick="toggleAccountMenu(event)" aria-label="Open menu">&#9660;</button>
                <div class="dropdown" id="accountDropdown">
                    
                    <a href="#" onclick="openProfileModal(); return false;">Settings</a><br>
                    <a href="/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="activity-panel">
        <div class="activity-box">Pending Users: {{ $pendingUsers->count() }}</div>
        <div class="activity-box">Total Rooms: {{ $roomItemCounts->count() }}</div>
        <div class="activity-box">Total Peripherals: {{ $peripheralCount }}</div>
        <div class="activity-box">Total Computer Units: {{ $computerUnitCount }}</div>
        <div class="activity-box">Usable Peripherals: {{ $usablePeripheralCount }}</div>
        <div class="activity-box">Usable Computer Units: {{ $usableComputerUnitCount }}</div>
        <div class="activity-box">Unusable Peripherals: {{ $unusablePeripheralCount }}</div>
        <div class="activity-box">Unusable Computer Units: {{ $unusableComputerUnitCount }}</div>
        <div class="activity-box">Borrowed Items: {{ $borrowedCount }}</div>
    </div>

    <div class="hud-row">
        <div class="chart-container col-4">
            <h3 class="hud-title">Items by Category</h3>
            <canvas id="deviceChart"></canvas>
        </div>
        <div class="chart-container col-4">
            <h3 class="hud-title">Item Status Overview</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-container col-4">
            <h3 class="hud-title">Borrowed Items by Type</h3>
            <canvas id="borrowedChart"></canvas>
        </div>
        <div class="chart-container col-4">
            <h3 class="hud-title">Activity Timeline</h3>
            <canvas id="hudLineChart"></canvas>
        </div>
        <div class="chart-container col-4">
            <h3 class="hud-title">Usable Peripherals</h3>
            <canvas id="gaugeChart"></canvas>
        </div>
    </div>

    <!-- Button to Trigger Modal -->
    <div style="margin-top: 10px;">
        <button class="contribution-btn" onclick="openModal()">📊 View Inventory Distribution</button>
    </div>
</div>

<!-- Modal with Contribution Graph -->
<div id="contributionModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 style="text-align:center;">Total Inventory Distribution</h3>
        <canvas id="contributionChart" height="250"></canvas>
    </div>
</div>

<!-- Profile Modal -->
<div id="profileModal">
    <div class="modal-content">
        <span class="close" onclick="closeProfileModal()">&times;</span>
        <h3 style="margin-top:0; text-align:center; color:#111827;">Profile Settings</h3>
        <div class="profile-avatar" style="justify-content:center; margin-bottom:12px;">
            <img src="{{ asset('photos/' . ($user->photo ?? '')) }}" onerror="this.src='{{ asset('images/logo.png') }}'" alt="Avatar">
            <div>
                <div style="font-weight:800; color:#111827;">{{ $user->name ?? $user->full_name }}</div>
                <div style="color:#6b7280; font-size:14px;">{{ $user->email }}</div>
            </div>
        </div>
        <form class="profile-form" id="profileForm" enctype="multipart/form-data">
            @csrf
            <div class="row profile-avatar">
                <img id="profilePreview" src="{{ asset('photos/' . ($user->photo ?? '')) }}" onerror="this.src='{{ asset('images/logo.png') }}'" alt="Avatar">
                <div>
                    <label style="width:auto;">Change Photo</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
            </div>
            <div class="row">
                <label>Full Name</label>
                <input type="text" name="name" value="{{ $user->name ?? $user->full_name }}" placeholder="Leave empty to keep current">
            </div>
            <div class="row">
                <label>Email</label>
                <input type="email" name="email" value="{{ $user->email }}" placeholder="Leave empty to keep current">
            </div>
            <div class="row">
                <label>Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep">
            </div>
            <div class="row">
                <label>Confirm</label>
                <input type="password" name="password_confirmation" placeholder="Confirm new password">
            </div>
            <div style="text-align:right; margin-top:6px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="contribution-btn" onclick="closeProfileModal()" style="background:#6c757d;">Cancel</button>
                <button type="submit" class="contribution-btn">Save Changes</button>
            </div>
        </form>
    </div>
    
</div>

<script>
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('datetime').innerText = now.toLocaleDateString('en-US', options);
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Items by Category Chart
    const ctx = document.getElementById('deviceChart').getContext('2d');
    // Orange/Cyan palette
    const neonCyan = 'rgba(0, 255, 209, 1)';
    const cyanDim = 'rgba(0, 255, 209, 0.35)';
    const neonOrange = 'rgba(255, 153, 0, 1)';
    const orangeDim = 'rgba(255, 153, 0, 0.35)';
    const gridColor = 'rgba(255, 190, 120, 0.18)';

    const deviceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($itemCounts->pluck('device_category')) !!},
            datasets: [{
                label: 'Items per Category',
                data: {!! json_encode($itemCounts->pluck('total')) !!},
                backgroundColor: cyanDim,
                borderColor: neonCyan,
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: '#ffd7a1' }
                },
                x: {
                    grid: { color: 'transparent' },
                    ticks: { color: '#ffd7a1' }
                }
            }
        }
    });

    // Item Status Overview Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Usable', 'Unusable'],
            datasets: [{
                data: [{{ $usableCount }}, {{ $unusableCount }}],
                backgroundColor: [neonCyan, orangeDim],
                borderColor: ['rgba(0,0,0,0)', 'rgba(0,0,0,0)'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                            const value = context.raw;
                            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${value} (${percent}%)`;
                        }
                    }
                }
            }
        }
    });

    // Borrowed Items by Type Chart
    const borrowedCtx = document.getElementById('borrowedChart').getContext('2d');
    const borrowedChart = new Chart(borrowedCtx, {
        type: 'bar',
        data: {
            labels: ['Peripheral Devices', 'Computer Units'],
            datasets: [{
                label: 'Borrowed Items',
                data: [{{ $borrowedPeripheralCount }}, {{ $borrowedComputerCount }}],
                backgroundColor: [neonOrange, neonCyan],
                borderColor: ['rgba(0,0,0,0)', 'rgba(0,0,0,0)'],
                borderWidth: 0,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: '#ffd7a1' }
                },
                x: {
                    grid: { color: 'transparent' },
                    ticks: { color: '#ffd7a1' }
                }
            }
        }
    });

    // HUD Line Chart (sine-like with points)
    const hudLineCtx = document.getElementById('hudLineChart').getContext('2d');
    const hudLineChart = new Chart(hudLineCtx, {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + 1),
            datasets: [{
                label: 'Activity',
                data: Array.from({length: 24}, (_, i) => Math.round(40 + 20*Math.sin(i/2) + (Math.random()*10-5))),
                borderColor: neonCyan,
                backgroundColor: 'rgba(0, 255, 209, 0.12)',
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: neonCyan,
                pointBorderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: gridColor }, ticks: { color: '#ffd7a1' }, beginAtZero: true },
                x: { grid: { color: 'rgba(255, 190, 120, 0.08)' }, ticks: { color: '#ffd7a1' } }
            }
        }
    });

    // Semi-circle Gauge (utilization)
    const gaugeCtx = document.getElementById('gaugeChart').getContext('2d');
    const gaugeValue = {{ max(0, min(100, round(($usableCount/ max(1, $usableCount+$unusableCount))*100))) }}; // percent
    const gaugeChart = new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Used', 'Remaining'],
            datasets: [{
                data: [gaugeValue, 100 - gaugeValue],
                backgroundColor: [neonOrange, 'rgba(80, 40, 20, 0.6)'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            rotation: -90,
            circumference: 180,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });

    function toggleAccountMenu(e){
        e.stopPropagation();
        const dd = document.getElementById('accountDropdown');
        if(!dd) return; dd.style.display = dd.style.display==='block' ? 'none' : 'block';
        document.addEventListener('click', function hide(e2){ if(!dd.contains(e2.target)){ dd.style.display='none'; document.removeEventListener('click', hide); } });
    }

    function openProfileModal(){
        const m = document.getElementById('profileModal'); if(!m) return; m.style.display = 'block';
    }
    function closeProfileModal(){ const m = document.getElementById('profileModal'); if(!m) return; m.style.display='none'; }

    function openModal() {
        document.getElementById('contributionModal').style.display = 'block';
        setTimeout(() => {
            if (!window.contributionChart) {
                renderContributionChart();
            }
        }, 200);
    }

    function closeModal() {
        document.getElementById('contributionModal').style.display = 'none';
    }

    function renderContributionChart() {
        const ctx = document.getElementById('contributionChart').getContext('2d');
        window.contributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Peripheral Devices', 'Computer Units'],
                datasets: [{
                    data: [{{ $peripheralCount }}, {{ $computerUnitCount }}],
                    backgroundColor: ['rgba(52, 152, 219, 0.7)', 'rgba(231, 76, 60, 0.7)'],
                    borderColor: ['rgba(52, 152, 219, 1)', 'rgba(231, 76, 60, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                                const value = context.raw;
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    window.onclick = function(event) {
        const modal = document.getElementById('contributionModal');
        if (event.target === modal) {
            closeModal();
        }
    };

    // Profile save handler (AJAX + SweetAlert)
    document.addEventListener('DOMContentLoaded', function(){
        const form = document.getElementById('profileForm');
        if(form){
            form.addEventListener('submit', async function(e){
                e.preventDefault();
                const fd = new FormData(form);
                // If password is blank, don't send password fields at all
                const pw = (form.querySelector('input[name="password"]')?.value || '').trim();
                const pwc = (form.querySelector('input[name="password_confirmation"]')?.value || '').trim();
                if (!pw && !pwc) {
                    fd.delete('password');
                    fd.delete('password_confirmation');
                }
                try{
                    const res = await fetch('{{ route('profile.update') }}', { method:'POST', credentials:'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                    let data = {};
                    try { data = await res.json(); } catch(e) { try { const t = await res.text(); data = { message: t }; } catch(_) {} }
                    if(res.ok){
                        closeProfileModal();
                        const name = (data.user && data.user.name) || '';
                        const photo = (data.user && data.user.photo) ? ('{{ asset('photos') }}/' + data.user.photo) : null;
                        const nameEls = document.querySelectorAll('.topbar-profile .name');
                        nameEls.forEach(el=> el.textContent = name);
                        if(photo){ document.querySelectorAll('.topbar-profile img').forEach(el=> el.src = photo); }
                        Swal.fire({ icon:'success', title:'Saved', text: data.message || 'Profile updated successfully', confirmButtonColor:'#28a745' });
                    } else if (res.status === 419) {
                        closeProfileModal();
                        Swal.fire({ icon:'error', title:'Session Expired', text:'Please refresh the page and try again.', confirmButtonColor:'#dc3545' });
                    } else if (res.status === 422) {
                        closeProfileModal();
                        let msg = 'Validation failed';
                        if (data && data.errors) {
                            msg = Object.values(data.errors).flat().join('\n');
                        } else if (data && data.message) { msg = data.message; }
                        Swal.fire({ icon:'error', title:'Validation Error', text: msg, confirmButtonColor:'#dc3545' });
                    } else {
                        closeProfileModal();
                        Swal.fire({ icon:'error', title:'Update Failed', text: data.message || 'Please try again', confirmButtonColor:'#dc3545' });
                    }
                }catch(err){
                    Swal.fire({ icon:'error', title:'Network Error', text:'Please try again', confirmButtonColor:'#dc3545' });
                }
            });

            const file = form.querySelector('input[name="photo"]');
            if(file){ file.addEventListener('change', function(){ const f=this.files && this.files[0]; if(!f) return; const r=new FileReader(); r.onload = e=>{ const p=document.getElementById('profilePreview'); if(p) p.src=e.target.result; }; r.readAsDataURL(f); }); }
        }
    });
</script>

</body>
</html>
