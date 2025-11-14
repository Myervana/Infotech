<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IT Department Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header img {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
            background: white;
            border-radius: 50%;
            padding: 10px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #667eea;
            font-size: 1.8em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section h2 i {
            font-size: 1.2em;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .feature-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.3em;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .feature-card i {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .info-item i {
            font-size: 2.5em;
            color: #667eea;
            margin-bottom: 10px;
        }

        .info-item h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .info-item p {
            color: #666;
            font-size: 0.9em;
        }

        .actions {
            text-align: center;
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 10px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .header p {
                font-size: 1em;
            }

            .content {
                padding: 20px;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="/images/logo.png" alt="IT Department Logo">
            <h1>IT Department Management System</h1>
            <p>Comprehensive Asset and Resource Management Solution</p>
        </div>

        <div class="content">
            <div class="section">
                <h2><i class="fas fa-info-circle"></i> About the System</h2>
                <p style="font-size: 1.1em; line-height: 1.8; color: #555; margin-bottom: 20px;">
                    The IT Department Management System is a comprehensive web-based solution designed to streamline 
                    and manage all IT assets, equipment, and resources within an organization. This system provides 
                    a centralized platform for tracking, maintaining, and managing IT infrastructure efficiently.
                </p>
            </div>

            <div class="section">
                <h2><i class="fas fa-star"></i> Key Features</h2>
                <div class="features">
                    <div class="feature-card">
                        <i class="fas fa-door-open"></i>
                        <h3>Room Management</h3>
                        <p>Organize and manage IT equipment by rooms. Track full computer sets, individual components, 
                        and maintain detailed inventory records with barcode support.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-layer-group"></i>
                        <h3>Category Management</h3>
                        <p>Categorize and organize devices by type. Create custom categories for different equipment 
                        types and manage device specifications efficiently.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-tools"></i>
                        <h3>Maintenance Tracking</h3>
                        <p>Track maintenance history and status of all IT equipment. Record maintenance notes, 
                        update equipment status, and schedule maintenance activities.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-handshake"></i>
                        <h3>Borrowing System</h3>
                        <p>Manage equipment borrowing with location tracking. Record borrow and return dates, 
                        track equipment location, and maintain borrowing history.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-barcode"></i>
                        <h3>Barcode Scanning</h3>
                        <p>Quick and efficient equipment lookup using barcode scanning. Search and retrieve 
                        equipment information instantly using barcode technology.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-user-shield"></i>
                        <h3>User Management</h3>
                        <p>Secure user authentication with approval workflow. Manage user accounts, roles, 
                        and access permissions with email verification and device binding.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-print"></i>
                        <h3>Reporting</h3>
                        <p>Generate comprehensive reports for inventory, maintenance, and borrowing activities. 
                        Export data for analysis and record-keeping purposes.</p>
                    </div>

                    <div class="feature-card">
                        <i class="fas fa-share-alt"></i>
                        <h3>Sharing & Access Control</h3>
                        <p>Share access with other users securely. Generate share tokens and manage shared 
                        access permissions for collaborative work.</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-cog"></i> System Capabilities</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-database"></i>
                        <h3>Centralized Database</h3>
                        <p>All data stored securely in a centralized database</p>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-mobile-alt"></i>
                        <h3>Mobile Responsive</h3>
                        <p>Access the system from any device, anywhere</p>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Secure & Protected</h3>
                        <p>Advanced security features and data protection</p>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-sync-alt"></i>
                        <h3>Real-time Updates</h3>
                        <p>Instant synchronization across all users</p>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="/login" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login to System
                </a>
                <a href="/register" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Register New Account
                </a>
            </div>
        </div>
    </div>
</body>
</html>

