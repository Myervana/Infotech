<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Inventory - Admin Security Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 i {
            color: #667eea;
            font-size: 2.2rem;
        }

        .header p {
            color: #718096;
            font-size: 1.1rem;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            font-size: 0.9rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .stat-card.visits { color: #4299e1; }
        .stat-card.attempts { color: #ed8936; }
        .stat-card.success { color: #48bb78; }
        .stat-card.unique { color: #9f7aea; }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Sidebar */
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: 100%;
            align-self: start;
        }
        .tab-btn {
            width: 100%;
            text-align: left;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            margin-bottom: 10px;
            cursor: pointer;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn.active { background: #edf2f7; border-color: #cbd5e0; }
        .content-area { display: none; }
        .content-area.active { display: block; }

        .card h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card h2 i {
            color: #667eea;
        }

        #map {
            width: 100%;
            height: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #4a5568;
        }

        tr:hover {
            background-color: #f7fafc;
        }

        .ip-address {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #2d3748;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .badge-warning {
            background-color: #fef5e7;
            color: #744210;
        }

        .badge-danger {
            background-color: #fed7d7;
            color: #742a2a;
        }

        .badge-info {
            background-color: #bee3f8;
            color: #2a4365;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
            font-size: 1.1rem;
            color: #718096;
        }

        .loading i {
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logout-btn {
            background: #e53e3e;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover { filter: brightness(0.95); }
        .logout-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        /* Pending Accounts */
        .action-btn {
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-approve { background: #38a169; }
        .btn-decline { background: #e53e3e; }
        .btn-approve:hover { filter: brightness(0.95); }
        .btn-decline:hover { filter: brightness(0.95); }

        .popup-content {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .popup-content h4 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .popup-content p {
            margin: 5px 0;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .popup-content .email-list {
            margin-top: 10px;
        }

        .popup-content .email-item {
            background: #f7fafc;
            padding: 5px 8px;
            border-radius: 4px;
            margin: 2px 0;
            font-size: 0.8rem;
            color: #2d3748;
        }

        .accuracy-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .accuracy-very-high {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .accuracy-high {
            background-color: #bee3f8;
            color: #2a4365;
        }

        .accuracy-medium {
            background-color: #fef5e7;
            color: #744210;
        }

        .accuracy-low {
            background-color: #fed7d7;
            color: #742a2a;
        }

        .accuracy-unknown {
            background-color: #e2e8f0;
            color: #4a5568;
        }

        .accuracy-local {
            background-color: #e9d8fd;
            color: #553c9a;
        }

        .map-legend {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .map-legend h4 {
            margin: 0 0 10px 0;
            color: #2d3748;
            font-size: 1rem;
            font-weight: 600;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }

        .legend-marker {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .legend-accuracy {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .legend-accuracy .accuracy-badge {
            margin: 2px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .dashboard-container {
                padding: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <button class="tab-btn active" id="tab-dashboard" onclick="switchTab('dashboard')"><i class="fas fa-tachometer-alt"></i> Dashboard</button>
            <button class="tab-btn" id="tab-pending" onclick="switchTab('pending')"><i class="fas fa-user-clock"></i> Pending Accounts</button>
            <button class="tab-btn" id="tab-accepted" onclick="switchTab('accepted')"><i class="fas fa-user-check"></i> Accepted Accounts</button>
            <button class="tab-btn" id="tab-share" onclick="switchTab('share')"><i class="fas fa-share-alt"></i> Share Activity</button>
        </div>
        <div>
        <div class="header">
            <div class="logout-container">
                <form method="POST" action="/logout" onsubmit="setTimeout(()=>{ window.location.href='/login'; }, 150);">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            <h1><i class="fas fa-shield-alt"></i> IT Inventory Security Dashboard</h1>
            <p>Real-time monitoring of user access patterns, login attempts, and geographic distribution</p>
            <div style="margin-top: 15px;">
                <button onclick="refreshData()" style="background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
                <span id="lastUpdate" style="margin-left: 15px; color: #718096; font-size: 0.9rem;"></span>
            </div>
        </div>
        <div class="content-area active" id="content-dashboard">
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card visits">
                <div class="icon"><i class="fas fa-eye"></i></div>
                <h3>Total Visits</h3>
                <div class="value" id="totalVisits">-</div>
            </div>
            <div class="stat-card attempts">
                <div class="icon"><i class="fas fa-key"></i></div>
                <h3>Login Attempts</h3>
                <div class="value" id="totalAttempts">-</div>
            </div>
            <div class="stat-card success">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <h3>Successful Logins</h3>
                <div class="value" id="totalSuccess">-</div>
            </div>
            <div class="stat-card unique">
                <div class="icon"><i class="fas fa-globe"></i></div>
                <h3>Unique IPs</h3>
                <div class="value" id="uniqueIPs">-</div>
            </div>
        </div>

        <div class="main-grid">
            <div class="card">
                <h2><i class="fas fa-map-marked-alt"></i> Geographic Distribution</h2>
                <div id="map"></div>
            </div>
            <div class="card">
                <h2><i class="fas fa-table"></i> IP Activity Details</h2>
                <div class="table-container">
                    <table id="ipTable">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Visits</th>
                                <th>Attempts</th>
                                <th>Success</th>
                                <th>Last Seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="loading" id="loadingRow">
                                    <i class="fas fa-spinner"></i> Loading data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>

        <div class="content-area" id="content-pending">
            <div class="card">
                <h2><i class="fas fa-user-clock"></i> Pending Account Approvals</h2>
                <div class="table-container">
                    <table id="pendingTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="loading" id="pendingLoading">
                                    <i class="fas fa-spinner"></i> Loading pending accounts...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="content-area" id="content-accepted">
            <div class="card">
                <h2><i class="fas fa-user-check"></i> Accepted Accounts</h2>
                <div class="table-container">
                    <table id="acceptedTable">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="loading" id="acceptedLoading">
                                    <i class="fas fa-spinner"></i> Loading accepted accounts...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="content-area" id="content-share">
            <div class="card">
                <h2><i class="fas fa-share-alt"></i> Share Activity (Token Generators)</h2>
                <div class="table-container">
                    <table id="shareTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Tokens Generated</th>
                                <th>Shared With</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="loading" id="shareLoading">
                                    <i class="fas fa-spinner"></i> Loading share activity...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>
    <script>
        function switchTab(tab){
            ['dashboard','pending','accepted','share'].forEach(t=>{
                const btn=document.getElementById('tab-'+t);
                const content=document.getElementById('content-'+t);
                if(btn) btn.classList.toggle('active', t===tab);
                if(content) content.classList.toggle('active', t===tab);
            });
            if(tab==='pending') fetchPending();
            if(tab==='accepted') fetchAccepted();
            if(tab==='share') fetchShare();
        }
        let map;
        let markers = [];

        async function fetchMetrics(){
            try {
                console.log('Fetching metrics from /admin-metrics...');
                const res = await fetch('/admin-metrics', { 
                    headers: { 'Accept': 'application/json' } 
                });
                
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                
                const data = await res.json();
                console.log('Received data:', data);
                
                if (!data || !data.ips) {
                    console.warn('No ips data in response:', data);
                    return [];
                }
                
                // Deduplicate by IP and use latest stats to avoid inflated totals
                const mapByIp = new Map();
                data.ips.forEach(row => {
                    if(!row || !row.ip) return;
                    const prev = mapByIp.get(row.ip);
                    if(!prev || new Date(row.last_seen||0) > new Date(prev.last_seen||0)){
                        mapByIp.set(row.ip, row);
                    }
                });
                return Array.from(mapByIp.values());
            } catch (error) {
                console.error('Error fetching metrics:', error);
                return [];
            }
        }

        function updateStats(ips) {
            console.log('Updating stats with IPs:', ips);
            
            if (!ips || !Array.isArray(ips)) {
                console.warn('Invalid IPs data for stats update:', ips);
                return;
            }
            
            const totalVisits = ips.reduce((sum, ip) => sum + (parseInt(ip.visits) || 0), 0);
            const totalAttempts = ips.reduce((sum, ip) => sum + (parseInt(ip.login_attempts) || 0), 0);
            const totalSuccess = ips.reduce((sum, ip) => sum + (parseInt(ip.login_success) || 0), 0);
            const uniqueIPs = ips.length;

            document.getElementById('totalVisits').textContent = totalVisits.toLocaleString();
            document.getElementById('totalAttempts').textContent = totalAttempts.toLocaleString();
            document.getElementById('totalSuccess').textContent = totalSuccess.toLocaleString();
            document.getElementById('uniqueIPs').textContent = uniqueIPs.toLocaleString();
        }

        function createPopupContent(row) {
            const location = row.city && row.country ? `${row.city}, ${row.country}` : 'Unknown Location';
            const accuracyLevel = getAccuracyLevel(row.accuracy);
            const emailsHtml = row.emails_used && row.emails_used.length > 0 
                ? `<div class="email-list">
                     <strong>Emails Used:</strong>
                     ${row.emails_used.map(email => `<div class="email-item">${email}</div>`).join('')}
                   </div>`
                : '<p><em>No email data available</em></p>';

            return `
                <div class="popup-content">
                    <h4><i class="fas fa-map-marker-alt"></i> ${row.ip}</h4>
                    <p><strong>Location:</strong> ${location}</p>
                    <p><strong>Accuracy:</strong> <span class="accuracy-badge ${accuracyLevel.class}">${accuracyLevel.text}</span></p>
                    <p><strong>Coordinates:</strong> ${row.latitude ? `${row.latitude.toFixed(6)}, ${row.longitude.toFixed(6)}` : 'N/A'}</p>
                    <p><strong>Total Visits:</strong> ${row.visits}</p>
                    <p><strong>Login Attempts:</strong> ${row.login_attempts}</p>
                    <p><strong>Successful Logins:</strong> ${row.login_success}</p>
                    <p><strong>Last Seen:</strong> ${row.last_seen}</p>
                    ${emailsHtml}
                </div>
            `;
        }

        function getAccuracyLevel(accuracy) {
            if (!accuracy || accuracy === 'unknown' || accuracy === 'error') {
                return { text: 'Unknown', class: 'accuracy-unknown' };
            }
            if (accuracy === 'local') {
                return { text: 'Local Network', class: 'accuracy-local' };
            }
            const num = parseInt(accuracy);
            if (num >= 90) {
                return { text: 'Very High', class: 'accuracy-very-high' };
            } else if (num >= 80) {
                return { text: 'High', class: 'accuracy-high' };
            } else if (num >= 70) {
                return { text: 'Medium', class: 'accuracy-medium' };
            } else {
                return { text: 'Low', class: 'accuracy-low' };
            }
        }

        function getMarkerColor(row) {
            if (row.login_success > 0) return '#48bb78'; // Green for successful logins
            if (row.login_attempts > 0) return '#ed8936'; // Orange for failed attempts
            return '#4299e1'; // Blue for visits only
        }

        function getMarkerSize(accuracy) {
            if (!accuracy || accuracy === 'unknown' || accuracy === 'error') return 15;
            if (accuracy === 'local') return 12;
            const num = parseInt(accuracy);
            if (num >= 90) return 25; // Very high accuracy - larger marker
            if (num >= 80) return 20; // High accuracy
            if (num >= 70) return 18; // Medium accuracy
            return 15; // Low accuracy - smaller marker
        }

        function createCustomIcon(color, size, accuracy) {
            const accuracyLevel = getAccuracyLevel(accuracy);
            const borderWidth = size >= 20 ? 4 : 3;
            
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background-color: ${color};
                    width: ${size}px;
                    height: ${size}px;
                    border-radius: 50%;
                    border: ${borderWidth}px solid white;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                    position: relative;
                ">
                    <div style="
                        position: absolute;
                        top: -8px;
                        right: -8px;
                        background: ${accuracyLevel.class === 'accuracy-very-high' ? '#48bb78' : 
                                   accuracyLevel.class === 'accuracy-high' ? '#4299e1' : 
                                   accuracyLevel.class === 'accuracy-medium' ? '#ed8936' : '#a0aec0'};
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        border: 1px solid white;
                    "></div>
                </div>`,
                iconSize: [size, size],
                iconAnchor: [size/2, size/2]
            });
        }

        function addMarkersToMap(ips) {
            console.log('Adding markers to map with IPs:', ips);
            
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            const bounds = [];
            
            if (!ips || !Array.isArray(ips)) {
                console.warn('Invalid IPs data for map markers:', ips);
                map.setView([14.5995, 120.9842], 6);
                return;
            }
            
            ips.forEach((row, index) => {
                try {
                    if (row.latitude && row.longitude && !isNaN(row.latitude) && !isNaN(row.longitude)) {
                        const color = getMarkerColor(row);
                        const size = getMarkerSize(row.accuracy);
                        const icon = createCustomIcon(color, size, row.accuracy);
                        
                        const lat = parseFloat(row.latitude), lng = parseFloat(row.longitude);
                        const marker = L.marker([lat, lng], { icon })
                            .addTo(map);
                        
                        marker.bindPopup(createPopupContent(row), {
                            maxWidth: 350,
                            className: 'custom-popup'
                        });
                        // Add accuracy circle if accuracy provided as meters or percentage
                        if(row.accuracy_m){
                            const radius = Math.min(Math.max(parseFloat(row.accuracy_m)||50, 20), 2000);
                            const circle = L.circle([lat, lng], { radius, color: '#667eea', fillColor: '#667eea', fillOpacity: 0.08, weight: 1 });
                            circle.addTo(map);
                            markers.push(circle);
                        }
                        
                        markers.push(marker);
                        bounds.push([lat, lng]);
                    } else {
                        console.log(`Skipping marker for IP ${row.ip} - no valid coordinates`);
                    }
                } catch (error) {
                    console.error(`Error creating marker for row ${index}:`, error, row);
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [20, 20] });
                console.log(`Added ${bounds.length} markers to map`);
            } else {
                // Fallback to Philippines view
                map.setView([14.5995, 120.9842], 6);
                console.log('No valid markers, using Philippines fallback view');
            }
        }

        function updateTable(ips) {
            console.log('Updating table with IPs:', ips);
            const tbody = document.querySelector('#ipTable tbody');
            tbody.innerHTML = '';

            if (!ips || ips.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; color: #718096; padding: 40px;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            No IP tracking data available
                        </td>
                    </tr>
                `;
                return;
            }

            ips.forEach((row, index) => {
                try {
                    const tr = document.createElement('tr');
                    const location = row.city && row.country ? `${row.city}, ${row.country}` : 'Unknown';
                    
                    tr.innerHTML = `
                        <td class="ip-address">${row.ip || 'Unknown'}</td>
                        <td>${location}</td>
                        <td><span class="badge badge-info">${row.visits || 0}</span></td>
                        <td><span class="badge badge-warning">${row.login_attempts || 0}</span></td>
                        <td><span class="badge badge-success">${row.login_success || 0}</span></td>
                        <td>${row.last_seen || 'Never'}</td>
                    `;
                    tbody.appendChild(tr);
                } catch (error) {
                    console.error(`Error processing row ${index}:`, error, row);
                }
            });
        }

        async function init() {
            try {
                console.log('Initializing dashboard...');
                
                // Initialize map with better controls
                map = L.map('map', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true,
                    dragging: true,
                    touchZoom: true
                });
                
                // Add multiple tile layers for better accuracy
                const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                });
                
                const cartoLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
                });
                
                // Add default layer
                osmLayer.addTo(map);
                
                // Add layer control
                const baseMaps = {
                    "OpenStreetMap": osmLayer,
                    "CartoDB Light": cartoLayer
                };
                L.control.layers(baseMaps).addTo(map);
                
                // Add scale control
                L.control.scale({
                    position: 'bottomright',
                    metric: true,
                    imperial: false
                }).addTo(map);
                
                // Add custom legend
                const legend = L.control({position: 'bottomleft'});
                legend.onAdd = function (map) {
                    const div = L.DomUtil.create('div', 'map-legend');
                    div.innerHTML = `
                        <h4>Map Legend</h4>
                        <div class="legend-item">
                            <div class="legend-marker" style="background: #48bb78;"></div>
                            <span>Successful Logins</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker" style="background: #ed8936;"></div>
                            <span>Failed Attempts</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker" style="background: #4299e1;"></div>
                            <span>Visits Only</span>
                        </div>
                        <div class="legend-accuracy">
                            <strong>Accuracy:</strong><br>
                            <span class="accuracy-badge accuracy-very-high">Very High</span>
                            <span class="accuracy-badge accuracy-high">High</span>
                            <span class="accuracy-badge accuracy-medium">Medium</span>
                            <span class="accuracy-badge accuracy-low">Low</span>
                        </div>
                    `;
                    return div;
                };
                legend.addTo(map);

                // Fetch and process data
                console.log('Fetching metrics data...');
                const ips = await fetchMetrics();
                console.log('Fetched IPs:', ips);
                
                if (ips.length === 0) {
                    console.warn('No IP data received');
                }
                
                // Update UI components
                updateStats(ips);
                updateTable(ips);
                addMarkersToMap(ips);
                fetchPending();
                // Preload accepted/share quietly
                fetchAccepted();
                fetchShare();
                
                console.log('Dashboard initialization completed successfully');

            } catch (error) {
                console.error('Error initializing dashboard:', error);
                
                // Show error state
                document.querySelector('#ipTable tbody').innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; color: #e53e3e; padding: 40px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Error loading data. Please refresh the page.
                        </td>
                    </tr>
                `;
            }
        }

        // Manual refresh function
        let refreshInFlight = false;
        async function refreshData() {
            console.log('Manual refresh triggered');
            if(refreshInFlight) { console.log('Refresh already in flight'); return; }
            refreshInFlight = true;
            try {
                const ips = await fetchMetrics();
                updateStats(ips);
                updateTable(ips);
                addMarkersToMap(ips);
                
                // Update last refresh time
                document.getElementById('lastUpdate').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
            } catch (error) {
                console.error('Error refreshing data:', error);
                alert('Error refreshing data. Please check console for details.');
            } finally {
                refreshInFlight = false;
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', init);
        
        // Fallback in case of JavaScript errors
        setTimeout(() => {
            const loadingRow = document.getElementById('loadingRow');
            if (loadingRow && loadingRow.innerHTML.includes('Loading data...')) {
                loadingRow.innerHTML = `
                    <td colspan="6" style="text-align: center; color: #e53e3e; padding: 40px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        JavaScript failed to load data. Please refresh the page or check browser console.
                        <br><br>
                        <button onclick="location.reload()" style="background: #667eea; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
                            <i class="fas fa-redo"></i> Reload Page
                        </button>
                    </td>
                `;
            }
        }, 10000); // 10 second timeout

        // Auto-refresh every 30 seconds with guard
        let autoRefreshId = null;
        function startAutoRefresh(){
            if(autoRefreshId) clearInterval(autoRefreshId);
            autoRefreshId = setInterval(refreshData, 30000);
        }
        startAutoRefresh();

        // Pending accounts
        async function fetchPending(){
            try{
                const res = await fetch('/admin/pending-users', { headers:{ 'Accept':'application/json' } });
                const body = await res.json().catch(()=>({}));
                if(!res.ok) throw new Error(body.message||'Failed to fetch pending');
                renderPending(body.users||[]);
            }catch(e){
                const tbody = document.querySelector('#pendingTable tbody');
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#718096; padding: 20px;">No pending data available.</td></tr>`;
            }
        }
        function renderPending(users){
            const tbody = document.querySelector('#pendingTable tbody');
            if(!users.length){
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#718096; padding: 20px;">No pending accounts.</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            users.forEach(u=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${u.name||'-'}</td><td>${u.email||'-'}</td><td>${u.created_at||'-'}</td><td><button class="action-btn btn-approve" onclick="approveUser('${u.id}')"><i class=\"fas fa-check\"></i> Approve</button> <button class="action-btn btn-decline" onclick="declineUser('${u.id}')"><i class=\"fas fa-times\"></i> Decline</button></td>`;
                tbody.appendChild(tr);
            });
        }
        async function approveUser(id){ await actUser(id, true); }
        async function declineUser(id){ await actUser(id, false); }
        async function actUser(id, approve){
            try{
                const res = await fetch(approve?'/admin/pending-users/approve':'/admin/pending-users/decline', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept':'application/json' }, body: JSON.stringify({ id }) });
                const body = await res.json().catch(()=>({}));
                if(!res.ok) throw new Error(body.message||'Action failed');
                fetchPending();
            }catch(e){ alert(e.message||'Action failed'); }
        }

        // Accepted accounts (login-eligible users)
        async function fetchAccepted(){
            try{
                const res = await fetch('/admin/login-eligible-users', { headers:{ 'Accept':'application/json' } });
                const body = await res.json().catch(()=>({}));
                if(!res.ok) throw new Error(body.message||'Failed to fetch login-eligible users');
                renderAccepted(body.users||[]);
            }catch(e){
                const tbody = document.querySelector('#acceptedTable tbody');
                tbody.innerHTML = `<tr><td colspan="4" style=\"text-align:center; color:#718096; padding: 20px;\">No login-eligible account data.</td></tr>`;
            }
        }
        function renderAccepted(users){
            const tbody = document.querySelector('#acceptedTable tbody');
            if(!users.length){ tbody.innerHTML = `<tr><td colspan=\"4\" style=\"text-align:center; color:#718096; padding:20px;\">No login-eligible accounts.</td></tr>`; return; }
            tbody.innerHTML='';
            users.forEach(u=>{
                const photo = u.photo_url ? `<img src=\"${u.photo_url}\" alt=\"${u.name||''}\" style=\"width:36px; height:36px; border-radius:50%; object-fit:cover;\">` : '<div style="width:36px; height:36px; border-radius:50%; background:#e2e8f0;"></div>';
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${photo}</td><td>${u.name||'-'}</td><td>${u.email||'-'}</td><td>${u.password_mask||'••••••••'}</td>`;
                tbody.appendChild(tr);
            });
        }

        // Share activity
        async function fetchShare(){
            try{
                const res = await fetch('/admin/share-activity', { headers:{ 'Accept':'application/json' } });
                const body = await res.json().catch(()=>({}));
                if(!res.ok) throw new Error(body.message||'Failed to fetch share activity');
                renderShare(body.items||[]);
            }catch(e){
                const tbody = document.querySelector('#shareTable tbody');
                tbody.innerHTML = `<tr><td colspan=\"4\" style=\"text-align:center; color:#718096; padding:20px;\">No share activity data.</td></tr>`;
            }
        }
        function renderShare(items){
            const tbody = document.querySelector('#shareTable tbody');
            if(!items.length){ tbody.innerHTML = `<tr><td colspan=\"4\" style=\"text-align:center; color:#718096; padding:20px;\">No share activity.</td></tr>`; return; }
            tbody.innerHTML='';
            items.forEach(it=>{
                const sharedWith = (it.shared_with||[]).map(e=>`<span class=\"badge badge-info\" style=\"margin:2px;\">${e}</span>`).join('');
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${it.user_name||'-'}</td><td>${it.user_email||'-'}</td><td>${it.tokens||0}</td><td>${sharedWith||'-'}</td>`;
                tbody.appendChild(tr);
            });
        }
    </script>
</body>
</html>

