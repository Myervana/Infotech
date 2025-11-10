@extends('layouts.app')
@section('title', 'Borrow')
@section('content')
@push('styles')
    <!-- Leaflet CSS 2025 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * {
    
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
 .main-content {
    flex: 1;
    padding: 5px;
    background: #f5f5f5;
    overflow-y: auto;
}

        body {
        margin: 0;
        font-family: Arial, sans-serif;
        display: flex;
        height: 100vh;
        background: #f4f6f8;
    }
        /* Main content wrapper for internal spacing */
        .main-content {
            padding: clamp(15px, 4vw, 30px);
            padding-left: 0; /* Remove left padding for edge-to-edge */
            padding-right: 0; /* Remove right padding for edge-to-edge */
        }

        /* Content wrapper for internal elements */
        .content-wrapper {
            padding: 0 clamp(15px, 4vw, 30px); /* Add horizontal padding only to content */
        }

        /* Header Section */
        .page-header {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: clamp(24px, 5vw, 32px);
            color: #2c3e50;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title::before {
            content: "📋";
            font-size: clamp(20px, 4vw, 28px);
        }

        /* Top Buttons - Enhanced Responsive Design */
        .top-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .top-buttons button, 
        .top-buttons a {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: clamp(10px, 2vw, 12px) clamp(15px, 3vw, 20px);
            text-decoration: none;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: clamp(13px, 2.5vw, 14px);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 10px rgba(44, 62, 80, 0.15);
            white-space: nowrap;
            min-width: fit-content;
        }

        .top-buttons button:hover, 
        .top-buttons a:hover {
            background: linear-gradient(135deg, #1b2733, #2c3e50);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(44, 62, 80, 0.25);
        }

        .top-buttons button:active,
        .top-buttons a:active {
            transform: translateY(0);
        }

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-top: 15px;
            margin-left: clamp(15px, 4vw, 30px);
            margin-right: clamp(15px, 4vw, 30px);
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            box-shadow: 0 2px 15px rgba(40, 167, 69, 0.1);
        }
/* Page Header: center title */
.page-header {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centers horizontally */
    text-align: center;  /* Centers text inside */
    gap: 15px;
    margin-bottom: 30px;
}


/* Top-buttons used at bottom: right-aligned */
.bottom-buttons {
    justify-content: flex-end;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

/* Make sure .top-buttons itself is flex */
.top-buttons {
    display: flex;
}

        /* Table Container - Enhanced Responsive */
     .table-container {
    background: #fff;
    border-radius: 0;
    overflow: auto;
    max-height: 70vh;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    width: 90%;
    margin: 0 auto;
    padding: 0;
    transform: translateX(3%);
}


        .table-responsive {
            max-height: 70vh;
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            min-width: 800px; /* Ensures table doesn't get too cramped */
        }

        th, td {
            padding: clamp(10px, 2vw, 16px);
            text-align: left;
            border: none;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        th {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            font-weight: 600;
            font-size: clamp(12px, 2vw, 14px);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            font-size: clamp(13px, 2.2vw, 14px);
            color: #495057;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f8f9fa;
            transition: background 0.3s ease;
        }

        /* Status Styling */
        .unusable {
            color: #dc3545;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-borrowed {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-usable {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Button Styling */
        .btn-return {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: fit-content;
        }

        .btn-return:hover:not(.btn-disabled) {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-disabled {
            background: #e9ecef !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.6;
        }

        /* Modal Styles - Enhanced Responsive */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: clamp(2%, 5vh, 5%) auto;
            padding: clamp(20px, 5vw, 30px);
            border-radius: 15px;
            width: clamp(300px, 90vw, 700px);
            max-width: 95vw;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal h3 {
            color: #2c3e50;
            margin: 0 0 25px 0;
            font-size: clamp(18px, 4vw, 22px);
            font-weight: 700;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #adb5bd;
            transition: color 0.3s ease;
            line-height: 1;
        }

        .close:hover {
            color: #495057;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        /* Date and Time Formatting */
        .date-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .date-main {
            font-weight: 500;
        }

        .date-relative {
            font-size: 11px;
            color: #6c757d;
            font-style: italic;
        }

        /* Mobile Responsive Breakpoints */
        @media (max-width: 768px) {
            .page-header {
                text-align: center;
            }

            .top-buttons {
                justify-content: center;
            }

            .top-buttons button,
            .top-buttons a {
                flex: 1;
                min-width: 0;
                justify-content: center;
            }

            /* Card-based layout for very small screens */
            .table-responsive {
                display: none;
            }

            .card-layout {
                display: block;
                padding: 0 clamp(15px, 4vw, 30px);
            }

            .item-card {
                background: white;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
                border-left: 4px solid #667eea;
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
                flex-wrap: wrap;
                gap: 10px;
            }

            .card-title {
                font-weight: 600;
                color: #2c3e50;
                font-size: 16px;
            }

            .card-status {
                font-size: 12px;
                padding: 4px 8px;
                border-radius: 12px;
                font-weight: 600;
            }

            .card-details {
                display: grid;
                gap: 10px;
                margin-bottom: 15px;
            }

            .card-detail {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid #f1f3f4;
            }

            .card-detail:last-child {
                border-bottom: none;
            }

            .detail-label {
                font-weight: 600;
                color: #6c757d;
                font-size: 13px;
            }

            .detail-value {
                font-size: 14px;
                color: #495057;
                text-align: right;
            }

            .card-actions {
                display: flex;
                justify-content: flex-end;
                margin-top: 15px;
            }
        }

        @media (min-width: 769px) {
            .card-layout {
                display: none;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 768px) and (max-width: 1024px) {
            .main-content {
                padding: 20px 0; /* Remove horizontal padding */
            }

            .content-wrapper {
                padding: 0 20px; /* Add horizontal padding only to content */
            }

            .modal-content {
                width: 85vw;
                max-width: 600px;
            }
        }

        /* Large screen optimizations */
        @media (min-width: 1200px) {
            .table-container {
                border-radius: 0; /* Keep edge-to-edge */
            }

            .modal-content {
                max-width: 800px;
            }
        }

        /* High DPI screen adjustments */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .top-buttons button,
            .top-buttons a {
                box-shadow: 0 1px 5px rgba(44, 62, 80, 0.2);
            }
        }

        /* Print styles */
        @media print {
            .top-buttons,
            .btn-return,
            .modal {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
            }

            .table-container {
                box-shadow: none;
            }
        }

        /* Custom scrollbar for webkit browsers */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Map Styles */
        #map, #trackerMap {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            margin: 15px 0;
            z-index: 1;
        }

        .map-container {
            margin: 15px 0;
        }

        .setup-map-btn {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        .setup-map-btn:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            margin-top: 10px;
            display: none;
        }

        .photo-preview.show {
            display: block;
        }

        .pc-group {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .pc-group-header {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Dropdown styling for grouped items */
        select option.full-set-option {
            font-weight: 600;
            background-color: #e3f2fd;
            color: #1976d2;
        }

        select option[style*="padding-left"] {
            color: #666;
            font-size: 0.95em;
        }

        select optgroup {
            font-weight: 600;
            color: #2c3e50;
        }

        /* Custom Collapsible Dropdown */
        .custom-dropdown {
            position: relative;
            width: 100%;
        }

        .dropdown-toggle {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: border-color 0.3s ease;
        }

        .dropdown-toggle:hover {
            border-color: #667eea;
        }

        .dropdown-toggle:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin-top: 5px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f4;
            transition: background 0.2s ease;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item.room-header {
            background: #e3f2fd;
            font-weight: 600;
            color: #1976d2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-item.room-header:hover {
            background: #bbdefb;
        }

        .dropdown-item.pc-header {
            background: #f5f5f5;
            font-weight: 600;
            color: #666;
            padding-left: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-item.pc-header:hover {
            background: #eeeeee;
        }

        .dropdown-item.item-option {
            padding-left: 50px;
            color: #495057;
        }

        .dropdown-item.item-option.full-set-option {
            background: #e8f5e9;
            font-weight: 600;
            color: #2e7d32;
        }

        .dropdown-item.item-option.full-set-option:hover {
            background: #c8e6c9;
        }

        .dropdown-item.disabled {
            cursor: default;
            color: #adb5bd;
        }

        .dropdown-item.disabled:hover {
            background: transparent;
        }

        .expand-icon {
            transition: transform 0.3s ease;
            font-size: 12px;
        }

        .expand-icon.expanded {
            transform: rotate(90deg);
        }

        .pc-content {
            display: none;
        }

        .pc-content.show {
            display: block;
        }

        .room-content {
            display: none;
        }

        .room-content.show {
            display: block;
        }
    </style>
    
</head>
<body>

<div class="main-content">
    <div class="content-wrapper">
        <div class="page-header">
            <h1 class="page-title">Borrowed Items List</h1>
            <div class="top-buttons">
                <button onclick="openBorrowModal()"><i class="fas fa-plus"></i> Borrow Item</button>
                <button onclick="openTrackerModal()"><i class="fas fa-calendar-alt"></i> Monthly Tracker</button>
            </div>
        </div>

        <!-- Success/Error messages will be shown via SweetAlert -->
    </div>

    <!-- Desktop Table View - Edge to Edge -->
    <div class="table-container">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-door-open"></i> Room</th>
                        <th><i class="fas fa-tags"></i> Category</th>
                        <th><i class="fas fa-barcode"></i> Serial #</th>
                        <th><i class="fas fa-info-circle"></i> Description</th>
                        <th><i class="fas fa-check-circle"></i> Status</th>
                        <th><i class="fas fa-user"></i> Borrower</th>
                        <th><i class="fas fa-calendar"></i> Borrow Date</th>
                        <th><i class="fas fa-undo"></i> Return</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Group items by PC#/full_set_id similar to room-manage.blade.php
                        $groupedItems = [];
                        $individualItems = [];
                        
                        foreach($items as $item) {
                            $pcNumber = null;
                            
                            // Try to extract PC number from various sources
                            if (preg_match('/(\d{3})$/', $item->barcode ?? '', $matches)) {
                                $pcNumber = intval($matches[1]);
                            } elseif (preg_match('/(\d{3})$/', $item->serial_number ?? '', $matches)) {
                                $pcNumber = intval($matches[1]);
                            } elseif ($item->full_set_id && preg_match('/(\d{3})$/', $item->full_set_id, $matches)) {
                                $pcNumber = intval($matches[1]);
                            } elseif (preg_match('/(\d{3})/', ($item->barcode ?? '') . ' ' . ($item->serial_number ?? ''), $matches)) {
                                $pcNumber = intval($matches[1]);
                            }
                            
                            if ($pcNumber !== null) {
                                if (!isset($groupedItems[$pcNumber])) {
                                    $groupedItems[$pcNumber] = [];
                                }
                                $groupedItems[$pcNumber][] = $item;
                            } else {
                                $individualItems[] = $item;
                            }
                        }
                        
                        ksort($groupedItems, SORT_NUMERIC);
                    @endphp
                    
                    @forelse($groupedItems as $pcNumber => $pcItems)
                        @foreach($pcItems as $item)
                            <tr>
                                <td><strong>{{ $item->room_title }}</strong></td>
                                <td>{{ $item->device_category }}</td>
                                <td><code>{{ $item->serial_number }}</code> @if($item->is_full_item) <span style="color: #667eea; font-size: 11px;">(PC#{{ $pcNumber }})</span> @endif</td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    @if($item->status === 'Unusable')
                                        <span class="unusable">❌ Unusable</span>
                                    @elseif($item->latestBorrow && $item->latestBorrow->status === 'Borrowed')
                                        <span class="status-badge status-borrowed">Borrowed</span>
                                    @else
                                        <span class="status-badge status-usable">Usable</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->latestBorrow)
                                        <div>
                                            @if($item->latestBorrow->borrower_photo)
                                                <img src="{{ asset('storage/' . $item->latestBorrow->borrower_photo) }}" 
                                                     alt="Borrower" 
                                                     style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px; vertical-align: middle; object-fit: cover;"
                                                     onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';">
                                                <span style="display: none; width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; align-items: center; justify-content: center; font-size: 14px; margin-right: 5px;">👤</span>
                                            @endif
                                            {{ $item->latestBorrow->borrower_name }}
                                            @if($item->latestBorrow->position)
                                                <br><small style="color: #6c757d;">{{ $item->latestBorrow->position }}</small>
                                            @endif
                                            @if($item->latestBorrow->department)
                                                <br><small style="color: #6c757d;">{{ $item->latestBorrow->department }}</small>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                   @if($item->latestBorrow && $item->latestBorrow->borrow_date)
            <div class="date-info">
                <span class="date-main">{{ \Carbon\Carbon::parse($item->latestBorrow->borrow_date)->format('M d, Y (g:i A)') }}</span>
                <span class="date-relative">{{ \Carbon\Carbon::parse($item->latestBorrow->borrow_date)->diffForHumans() }}</span>
            </div>
        @else
            -
        @endif

                                </td>
                                <td>
                                    @if($item->latestBorrow && $item->latestBorrow->status === 'Borrowed')
            <form method="POST" action="/borrow/return/{{ $item->latestBorrow->id }}">
                @csrf
                <button class="btn-return"><i class="fas fa-check"></i> Return</button>
            </form>
        @elseif($item->status === 'Unusable')
            <button class="btn-return btn-disabled" disabled><i class="fas fa-times"></i> Not Usable</button>
        @else
            <button class="btn-return btn-disabled" disabled><i class="fas fa-check"></i> Returned</button>
        @endif

                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    
                    @forelse($individualItems as $item)
                        <tr>
                            <td><strong>{{ $item->room_title }}</strong></td>
                            <td>{{ $item->device_category }}</td>
                            <td><code>{{ $item->serial_number }}</code></td>
                            <td>{{ $item->description }}</td>
                            <td>
                                @if($item->status === 'Unusable')
                                    <span class="unusable">❌ Unusable</span>
                                @elseif($item->latestBorrow && $item->latestBorrow->status === 'Borrowed')
                                    <span class="status-badge status-borrowed">Borrowed</span>
                                @else
                                    <span class="status-badge status-usable">Usable</span>
                                @endif
                            </td>
                            <td>
                                @if($item->latestBorrow)
                                    <div>
                                        @if($item->latestBorrow->borrower_photo)
                                            <img src="{{ asset('storage/' . $item->latestBorrow->borrower_photo) }}" 
                                                 alt="Borrower" 
                                                 style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px; vertical-align: middle; object-fit: cover;"
                                                 onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';">
                                            <span style="display: none; width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; align-items: center; justify-content: center; font-size: 14px; margin-right: 5px;">👤</span>
                                        @endif
                                        {{ $item->latestBorrow->borrower_name }}
                                        @if($item->latestBorrow->position)
                                            <br><small style="color: #6c757d;">{{ $item->latestBorrow->position }}</small>
                                        @endif
                                        @if($item->latestBorrow->department)
                                            <br><small style="color: #6c757d;">{{ $item->latestBorrow->department }}</small>
                                        @endif
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                               @if($item->latestBorrow && $item->latestBorrow->borrow_date)
        <div class="date-info">
            <span class="date-main">{{ \Carbon\Carbon::parse($item->latestBorrow->borrow_date)->format('M d, Y (g:i A)') }}</span>
            <span class="date-relative">{{ \Carbon\Carbon::parse($item->latestBorrow->borrow_date)->diffForHumans() }}</span>
        </div>
    @else
        -
    @endif

                            </td>
                            <td>
                                @if($item->latestBorrow && $item->latestBorrow->status === 'Borrowed')
        <form method="POST" action="/borrow/return/{{ $item->latestBorrow->id }}">
            @csrf
            <button class="btn-return"><i class="fas fa-check"></i> Return</button>
        </form>
    @elseif($item->status === 'Unusable')
        <button class="btn-return btn-disabled" disabled><i class="fas fa-times"></i> Not Usable</button>
    @else
        <button class="btn-return btn-disabled" disabled><i class="fas fa-check"></i> Returned</button>
    @endif

                            </td>
                        </tr>
                    @empty
                        @if(empty($groupedItems))
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">📭</div>
                                        <p>No items found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card Layout -->
    <div class="card-layout">
        @forelse($items as $item)
            <div class="item-card">
                <div class="card-header">
                    <div class="card-title">{{ $item->room_title }} - {{ $item->device_category }}</div>
                    <div class="card-status 
                        @if($item->status === 'Unusable') unusable
                        @elseif($item->borrow && $item->borrow->status === 'Borrowed') status-borrowed
                        @else status-usable @endif">
                        @if($item->status === 'Unusable')
                            ❌ Unusable
                        @elseif($item->borrow && $item->borrow->status === 'Borrowed')
                            Borrowed
                        @else
                            Usable
                        @endif
                    </div>
                </div>
                
                <div class="card-details">
                    <div class="card-detail">
                        <span class="detail-label">Serial #:</span>
                        <span class="detail-value"><code>{{ $item->serial_number }}</code></span>
                    </div>
                    <div class="card-detail">
                        <span class="detail-label">Description:</span>
                        <span class="detail-value">{{ $item->description }}</span>
                    </div>
                    @if($item->borrow && $item->borrow->borrower_name)
                    <div class="card-detail">
                        <span class="detail-label">Borrower:</span>
                        <span class="detail-value">{{ $item->borrow->borrower_name }}</span>
                    </div>
                    @endif
                    @if($item->borrow && $item->borrow->borrow_date)
                    <div class="card-detail">
                        <span class="detail-label">Borrow Date:</span>
                        <div class="detail-value">
                            <div class="date-info">
                                <span class="date-main">{{ \Carbon\Carbon::parse($item->borrow->borrow_date)->format('M d, Y (g:i A)') }}</span>
                                <span class="date-relative">{{ \Carbon\Carbon::parse($item->borrow->borrow_date)->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="card-actions">
                    @if($item->borrow && $item->borrow->status === 'Borrowed')
                        <form method="POST" action="/borrow/return/{{ $item->borrow->id }}">
                            @csrf
                            <button class="btn-return"><i class="fas fa-check"></i> Return</button>
                        </form>
                    @elseif($item->status === 'Unusable')
                        <button class="btn-return btn-disabled" disabled><i class="fas fa-times"></i> Not Usable</button>
                    @else
                        <button class="btn-return btn-disabled" disabled><i class="fas fa-check"></i> Returned</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No items found.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Borrow Item -->
<div id="borrowModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeBorrowModal()">&times;</span>
        <h3>➕ Borrow Item</h3>

        <form method="POST" action="/borrow" enctype="multipart/form-data" id="borrowForm">
            @csrf
            <div class="form-group">
                <label for="room_item_id">Select Item</label>
                <div class="custom-dropdown">
                    <div class="dropdown-toggle" id="dropdownToggle" onclick="toggleDropdown()">
                        <span id="selectedText">-- Choose an item --</span>
                        <span>▼</span>
                    </div>
                    <div class="dropdown-menu" id="dropdownMenu">
                        {{-- Grouped by Room, then by PC# - Collapsible --}}
                        @foreach($groupedAvailableItems as $roomTitle => $pcGroups)
                            @php
                                $roomId = 'room-' . Str::slug($roomTitle);
                            @endphp
                            <div class="dropdown-item room-header" onclick="toggleRoom('{{ $roomId }}', event)">
                                <span>🏢 {{ $roomTitle }}</span>
                                <span class="expand-icon" id="icon-{{ $roomId }}">▶</span>
                            </div>
                            <div class="room-content" id="{{ $roomId }}">
                                @foreach($pcGroups as $pcNumber => $pcItems)
                                    @php
                                        $firstItem = $pcItems[0];
                                        $itemCount = count($pcItems);
                                        $pcId = 'pc-' . Str::slug($roomTitle) . '-' . $pcNumber;
                                        
                                        // Check if items share the same full_set_id (indicating a full set)
                                        $sharedFullSetId = null;
                                        $allHaveFullSetId = true;
                                        foreach($pcItems as $pcItem) {
                                            if ($pcItem->full_set_id) {
                                                if ($sharedFullSetId === null) {
                                                    $sharedFullSetId = $pcItem->full_set_id;
                                                } elseif ($sharedFullSetId !== $pcItem->full_set_id) {
                                                    $allHaveFullSetId = false;
                                                    break;
                                                }
                                            } else {
                                                $allHaveFullSetId = false;
                                            }
                                        }
                                        $isFullSet = ($allHaveFullSetId && $sharedFullSetId && count($pcItems) > 1);
                                        $fullSetId = $sharedFullSetId ?? '';
                                        
                                        // Get the most common full_set_id if items have different ones
                                        if (!$isFullSet && $itemCount > 1) {
                                            $fullSetIds = [];
                                            foreach($pcItems as $pcItem) {
                                                if ($pcItem->full_set_id) {
                                                    $fullSetIds[] = $pcItem->full_set_id;
                                                }
                                            }
                                            if (count($fullSetIds) > 0) {
                                                $fullSetId = $fullSetIds[0];
                                            }
                                        }
                                    @endphp
                                    
                                    {{-- PC# Header --}}
                                    <div class="dropdown-item pc-header" onclick="togglePC('{{ $pcId }}', event)">
                                        <span>└─ PC#{{ str_pad($pcNumber, 3, '0', STR_PAD_LEFT) }} ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                                        <span class="expand-icon" id="icon-{{ $pcId }}">▶</span>
                                    </div>
                                    
                                    {{-- PC# Content --}}
                                    <div class="pc-content" id="{{ $pcId }}">
                                        @if($isFullSet && $itemCount > 1)
                                            {{-- Full Set Option --}}
                                            <div class="dropdown-item item-option full-set-option" 
                                                onclick="selectItem('{{ $firstItem->id }}', '📦 Full Set ({{ $itemCount }} items) - Borrow All', '{{ $roomTitle }}', '{{ $pcNumber }}', '{{ $fullSetId }}', '1', event)">
                                                📦 Full Set ({{ $itemCount }} items) - Borrow All
                                            </div>
                                        @endif
                                        
                                        {{-- Individual items in PC# --}}
                                        @foreach($pcItems as $item)
                                            <div class="dropdown-item item-option" 
                                                onclick="selectItem('{{ $item->id }}', '{{ $item->device_category }} - {{ $item->serial_number }}', '{{ $roomTitle }}', '{{ $pcNumber }}', '{{ $item->full_set_id ?? $fullSetId }}', '{{ ($isFullSet && $itemCount > 1) ? '1' : ($item->is_full_item ? '1' : '0') }}', event)">
                                                {{ $item->device_category }} - {{ $item->serial_number }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                                
                                {{-- Individual items in this room (not part of PC#) --}}
                                @if(isset($individualAvailableItems[$roomTitle]) && count($individualAvailableItems[$roomTitle]) > 0)
                                    <div class="dropdown-item pc-header" style="background: #fff3cd;">
                                        <span>└─ Individual Items</span>
                                    </div>
                                    @foreach($individualAvailableItems[$roomTitle] as $item)
                                        <div class="dropdown-item item-option" 
                                            onclick="selectItem('{{ $item->id }}', '{{ $item->device_category }} - {{ $item->serial_number }}', '{{ $roomTitle }}', '', '{{ $item->full_set_id ?? '' }}', '{{ $item->is_full_item ? '1' : '0' }}', event)">
                                            {{ $item->device_category }} - {{ $item->serial_number }}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                        
                        {{-- Rooms with only individual items (no PC# groups) --}}
                        @foreach($individualAvailableItems as $roomTitle => $items)
                            @if(!isset($groupedAvailableItems[$roomTitle]))
                                @php
                                    $roomId = 'room-' . Str::slug($roomTitle);
                                @endphp
                                <div class="dropdown-item room-header" onclick="toggleRoom('{{ $roomId }}', event)">
                                    <span>🏢 {{ $roomTitle }}</span>
                                    <span class="expand-icon" id="icon-{{ $roomId }}">▶</span>
                                </div>
                                <div class="room-content" id="{{ $roomId }}">
                                    @foreach($items as $item)
                                        <div class="dropdown-item item-option" 
                                            onclick="selectItem('{{ $item->id }}', '{{ $item->device_category }} - {{ $item->serial_number }}', '{{ $roomTitle }}', '', '{{ $item->full_set_id ?? '' }}', '{{ $item->is_full_item ? '1' : '0' }}', event)">
                                            {{ $item->device_category }} - {{ $item->serial_number }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="room_item_id" id="room_item_id" required>
                </div>
            </div>

            <div class="form-group" id="fullSetGroup" style="display: none;">
                <label>
                    <input type="checkbox" name="borrow_full_set" id="borrow_full_set" value="1" checked>
                    <strong>Borrow Full Set</strong> - All items in PC#<span id="selectedPcNumber"></span> will be borrowed ({{ count($groupedAvailableItems) > 0 ? 'recommended' : '' }})
                </label>
                <small style="color: #6c757d; display: block; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> When checked, all items in this PC# will be borrowed together.
                </small>
            </div>

            <div class="form-group">
                <label for="borrower_name">Borrower Name</label>
                <input type="text" name="borrower_name" id="borrower_name" required>
            </div>

            <div class="form-group">
                <label for="borrower_photo">Photo of the Borrower</label>
                <input type="file" name="borrower_photo" id="borrower_photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/jfif,image/webp,image/bmp,image/svg+xml" onchange="previewPhoto(this)">
                <small style="color: #6c757d; display: block; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Accepted formats: JPG, JPEG, PNG, GIF, JFIF, WEBP, BMP, SVG (Max: 5MB)
                </small>
                <img id="photoPreview" class="photo-preview" alt="Photo preview">
            </div>

            <div class="form-group">
                <label for="position">Position</label>
                <input type="text" name="position" id="position" placeholder="e.g., Student, Faculty, Staff">
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <select name="department" id="department">
                    <option value="">-- Select Department --</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSHM">BSHM</option>
                    <option value="BSBA">BSBA</option>
                    <option value="BSED">BSED</option>
                    <option value="BEED">BEED</option>
                </select>
            </div>

            <div class="form-group">
                <button type="button" class="setup-map-btn" onclick="setupMap()">
                    <i class="fas fa-map-marker-alt"></i> Setup Map Location
                </button>
                <div id="map" style="display: none;"></div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>

            <div class="form-group">
                <label for="borrow_date">Borrow Date</label>
                <input type="datetime-local" name="borrow_date" id="borrow_date" required>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-check"></i> Submit Borrow Request
            </button>
        </form>
    </div>
</div>

<!-- Modal: Monthly Tracker -->
<div id="trackerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeTrackerModal()">&times;</span>
        <h3>📅 Monthly Activity Tracker</h3>
        
        <div id="trackerMap" style="margin-bottom: 20px;"></div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Borrower</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Borrow Date</th>
                        <th>Returned Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>
                                @if($activity->borrower_photo)
                                    <img src="{{ asset('storage/' . $activity->borrower_photo) }}" 
                                         alt="Borrower" 
                                         style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px; vertical-align: middle; object-fit: cover;"
                                         onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';">
                                    <span style="display: none; width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; align-items: center; justify-content: center; font-size: 14px; margin-right: 5px;">👤</span>
                                @endif
                                {{ $activity->borrower_name }}
                                @if($activity->position)
                                    <br><small style="color: #6c757d;">{{ $activity->position }}</small>
                                @endif
                                @if($activity->department)
                                    <br><small style="color: #6c757d;">{{ $activity->department }}</small>
                                @endif
                            </td>
                            <td><code>{{ $activity->roomItem->serial_number ?? 'N/A' }}</code></td>
                            <td>{{ $activity->roomItem->device_category ?? 'N/A' }}</td>
                            <td>
                                <div class="date-info">
                                    <span class="date-main">{{ \Carbon\Carbon::parse($activity->borrow_date)->format('M d, Y (g:i A)') }}</span>
                                    <span class="date-relative">{{ \Carbon\Carbon::parse($activity->borrow_date)->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td>
                                @if($activity->return_date)
                                    <div class="date-info">
                                        <span class="date-main">{{ \Carbon\Carbon::parse($activity->return_date)->format('M d, Y (g:i A)') }}</span>
                                        <span class="date-relative">{{ \Carbon\Carbon::parse($activity->return_date)->diffForHumans() }}</span>
                                    </div>
                                @else
                                    <span class="status-badge status-borrowed">Not Returned</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge @if($activity->status === 'Borrowed') status-borrowed @else status-usable @endif">
                                    {{ $activity->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📊</div>
                                    <p>No activity found this month.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Leaflet JS 2025 -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Initialize SweetAlert for session messages
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc3545'
        });
    @endif

    @if (session('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: '{{ session('warning') }}',
            confirmButtonColor: '#ffc107'
        });
    @endif

    @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validation Error!',
            html: '<ul style="text-align: left; margin: 10px 0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            confirmButtonColor: '#dc3545'
        });
    @endif

    let map = null;
    let trackerMap = null;
    let marker = null;
    let trackerMarkers = [];

    // Original JavaScript functionality preserved
    function openBorrowModal() {
        document.getElementById("borrowModal").style.display = "block";
        document.body.style.overflow = "hidden"; // Prevent background scrolling
    }

    function closeBorrowModal() {
        document.getElementById("borrowModal").style.display = "none";
        document.body.style.overflow = "auto";
        // Reset form
        if (map) {
            map.remove();
            map = null;
            marker = null;
        }
        document.getElementById("map").style.display = "none";
        document.getElementById("photoPreview").classList.remove("show");
        document.getElementById("photoPreview").src = "";
    }

    function openTrackerModal() {
        document.getElementById("trackerModal").style.display = "block";
        document.body.style.overflow = "hidden";
        // Initialize tracker map
        setTimeout(initTrackerMap, 100);
    }

    function closeTrackerModal() {
        document.getElementById("trackerModal").style.display = "none";
        document.body.style.overflow = "auto";
        if (trackerMap) {
            trackerMap.remove();
            trackerMap = null;
            trackerMarkers = [];
        }
    }

    // Close modal when clicking outside (enhanced)
    window.onclick = function(event) {
        const borrowModal = document.getElementById("borrowModal");
        const trackerModal = document.getElementById("trackerModal");
        
        if (event.target === borrowModal) {
            closeBorrowModal();
        }
        if (event.target === trackerModal) {
            closeTrackerModal();
        }
    };

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeBorrowModal();
            closeTrackerModal();
        }
    });

    // Auto-set current date/time for borrow date input
    document.addEventListener('DOMContentLoaded', function() {
        const borrowDateInput = document.querySelector('input[name="borrow_date"]');
        if (borrowDateInput) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            borrowDateInput.value = now.toISOString().slice(0, 16);
        }
    });

    // Custom Dropdown Functions
    let selectedItemData = {
        id: '',
        pcNumber: '',
        roomTitle: '',
        fullSetId: '',
        isFullItem: '0'
    };

    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        menu.classList.toggle('show');
    }

    function toggleRoom(roomId, event) {
        event.stopPropagation();
        const content = document.getElementById(roomId);
        const icon = document.getElementById('icon-' + roomId);
        content.classList.toggle('show');
        icon.classList.toggle('expanded');
    }

    function togglePC(pcId, event) {
        event.stopPropagation();
        const content = document.getElementById(pcId);
        const icon = document.getElementById('icon-' + pcId);
        content.classList.toggle('show');
        icon.classList.toggle('expanded');
    }

    function selectItem(itemId, itemText, roomTitle, pcNumber, fullSetId, isFullItem, event) {
        event.stopPropagation();
        
        selectedItemData = {
            id: itemId,
            pcNumber: pcNumber,
            roomTitle: roomTitle,
            fullSetId: fullSetId,
            isFullItem: isFullItem
        };
        
        document.getElementById('room_item_id').value = itemId;
        document.getElementById('selectedText').textContent = itemText;
        document.getElementById('dropdownMenu').classList.remove('show');
        
        // Check if full set option should be shown
        checkFullSet();
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.custom-dropdown');
        if (!dropdown.contains(event.target)) {
            document.getElementById('dropdownMenu').classList.remove('show');
        }
    });

    // Check if selected item is part of a full set
    function checkFullSet() {
        const pcNumber = selectedItemData.pcNumber;
        const fullSetId = selectedItemData.fullSetId;
        const isFullItem = selectedItemData.isFullItem === '1';
        const fullSetGroup = document.getElementById('fullSetGroup');
        const selectedPcNumberSpan = document.getElementById('selectedPcNumber');
        
        // Check if this item belongs to a PC# group (has pcNumber)
        if (pcNumber && pcNumber !== '') {
            fullSetGroup.style.display = 'block';
            selectedPcNumberSpan.textContent = pcNumber;
            
            // Auto-check the full set checkbox if it's a full set option
            if (document.getElementById('room_item_id').value && isFullItem && fullSetId) {
                // Check if this is the full set option by checking the selected text
                const selectedText = document.getElementById('selectedText').textContent;
                if (selectedText.includes('Full Set')) {
                    document.getElementById('borrow_full_set').checked = true;
                } else {
                    document.getElementById('borrow_full_set').checked = false;
                }
            }
        } else {
            fullSetGroup.style.display = 'none';
            document.getElementById('borrow_full_set').checked = false;
        }
    }

    // Preview photo
    function previewPhoto(input) {
        const preview = document.getElementById('photoPreview');
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileSize = file.size / 1024 / 1024; // Size in MB
            
            // Check file size (max 5MB)
            if (fileSize > 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'The photo must be less than 5MB. Please choose a smaller file.',
                    confirmButtonColor: '#dc3545'
                });
                input.value = '';
                preview.classList.remove('show');
                preview.src = '';
                return;
            }
            
            // Check if it's an image file
            if (!file.type.match('image.*')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Please select a valid image file (JPG, JPEG, PNG, GIF, JFIF, WEBP, BMP, or SVG).',
                    confirmButtonColor: '#dc3545'
                });
                input.value = '';
                preview.classList.remove('show');
                preview.src = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.add('show');
                preview.onerror = function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Image Load Error',
                        text: 'There was an error loading the image. Please try another file.',
                        confirmButtonColor: '#dc3545'
                    });
                    preview.classList.remove('show');
                };
            };
            reader.onerror = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'File Read Error',
                    text: 'There was an error reading the file. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
                input.value = '';
                preview.classList.remove('show');
            };
            reader.readAsDataURL(file);
        } else {
            preview.classList.remove('show');
            preview.src = '';
        }
    }

    // Setup map with satellite view
    function setupMap() {
        const mapDiv = document.getElementById('map');
        if (map) {
            mapDiv.style.display = mapDiv.style.display === 'none' ? 'block' : 'none';
            return;
        }

        mapDiv.style.display = 'block';
        
        // Initialize map with satellite view (using Esri World Imagery)
        map = L.map('map', {
            center: [14.5995, 120.9842], // Default to Philippines
            zoom: 15
        });

        // Add satellite tile layer (Esri World Imagery)
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri',
            maxZoom: 19
        }).addTo(map);

        // Add marker
        marker = L.marker([14.5995, 120.9842], {draggable: true}).addTo(map);
        
        // Update coordinates when marker is dragged
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat;
            document.getElementById('longitude').value = pos.lng;
        });

        // Get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 18);
                marker.setLatLng([lat, lng]);
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });
        }

        // Update coordinates on click
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        });

        // Initialize with default values if not set
        if (!document.getElementById('latitude').value) {
            document.getElementById('latitude').value = '14.5995';
            document.getElementById('longitude').value = '120.9842';
        }
    }

    // Initialize tracker map with all borrower and item locations
    function initTrackerMap() {
        if (trackerMap) return;

        const activities = @json($activities);
        
        trackerMap = L.map('trackerMap', {
            center: [14.5995, 120.9842],
            zoom: 13
        });

        // Add satellite tile layer
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri',
            maxZoom: 19
        }).addTo(trackerMap);

        const bounds = [];
        
        activities.forEach(function(activity) {
            if (activity.latitude && activity.longitude) {
                const lat = parseFloat(activity.latitude);
                const lng = parseFloat(activity.longitude);
                
                // Create popup content
                const roomItem = activity.room_item || {};
                const popupContent = `
                    <div style="min-width: 200px;">
                        <strong>${activity.borrower_name}</strong><br>
                        ${activity.position ? activity.position + '<br>' : ''}
                        ${activity.department ? activity.department + '<br>' : ''}
                        <hr>
                        <strong>Item:</strong> ${roomItem.serial_number || 'N/A'}<br>
                        <strong>Category:</strong> ${roomItem.device_category || 'N/A'}<br>
                        <strong>Status:</strong> ${activity.status}<br>
                        <strong>Borrow Date:</strong> ${new Date(activity.borrow_date).toLocaleString()}
                    </div>
                `;
                
                // Create marker with different colors for borrower and item
                const markerColor = activity.status === 'Borrowed' ? 'red' : 'green';
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${markerColor}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                
                const m = L.marker([lat, lng], {icon: icon})
                    .addTo(trackerMap);
                
                // Add click event to show borrower modal (no popup, only modal)
                m.on('click', function() {
                    showBorrowerModal(activity);
                });
                
                trackerMarkers.push(m);
                bounds.push([lat, lng]);
            }
        });

        // Fit map to show all markers
        if (bounds.length > 0) {
            trackerMap.fitBounds(bounds, {padding: [50, 50]});
        }
    }

    // Show borrower modal when marker is clicked
    function showBorrowerModal(activity) {
        const roomItem = activity.room_item || {};
        const borrowerPhoto = activity.borrower_photo ? `/storage/${activity.borrower_photo}` : null;
        
        let htmlContent = `
            <div style="text-align: left; padding: 10px;">
                <div style="display: flex; align-items: center; margin-bottom: 20px; gap: 15px;">
                    ${borrowerPhoto ? `<img src="${borrowerPhoto}" alt="Borrower" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid #667eea; object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"><div style="display: none; width: 80px; height: 80px; border-radius: 50%; background: #e9ecef; align-items: center; justify-content: center; font-size: 32px;">👤</div>` : '<div style="width: 80px; height: 80px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 32px;">👤</div>'}
                    <div>
                        <h3 style="margin: 0; color: #2c3e50;">${activity.borrower_name}</h3>
                        ${activity.position ? `<p style="margin: 5px 0; color: #6c757d;"><strong>Position:</strong> ${activity.position}</p>` : ''}
                        ${activity.department ? `<p style="margin: 5px 0; color: #6c757d;"><strong>Department:</strong> ${activity.department}</p>` : ''}
                    </div>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 2px solid #e9ecef;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; color: #667eea;">📦 Item Information</h4>
                        <p style="margin: 5px 0;"><strong>Serial #:</strong> ${roomItem.serial_number || 'N/A'}</p>
                        <p style="margin: 5px 0;"><strong>Category:</strong> ${roomItem.device_category || 'N/A'}</p>
                        <p style="margin: 5px 0;"><strong>Room:</strong> ${roomItem.room_title || 'N/A'}</p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 10px 0; color: #667eea;">📅 Borrow Details</h4>
                        <p style="margin: 5px 0;"><strong>Status:</strong> <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; ${activity.status === 'Borrowed' ? 'background: #fff3cd; color: #856404;' : 'background: #d4edda; color: #155724;'}">${activity.status}</span></p>
                        <p style="margin: 5px 0;"><strong>Borrow Date:</strong><br>${new Date(activity.borrow_date).toLocaleString()}</p>
                        ${activity.return_date ? `<p style="margin: 5px 0;"><strong>Return Date:</strong><br>${new Date(activity.return_date).toLocaleString()}</p>` : '<p style="margin: 5px 0; color: #dc3545;"><strong>Return Date:</strong> Not Returned</p>'}
                    </div>
                </div>
                ${activity.latitude && activity.longitude ? `
                    <hr style="margin: 20px 0; border: none; border-top: 2px solid #e9ecef;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; color: #667eea;">📍 Location</h4>
                        <p style="margin: 5px 0;"><strong>Coordinates:</strong> ${parseFloat(activity.latitude).toFixed(6)}, ${parseFloat(activity.longitude).toFixed(6)}</p>
                        <a href="https://www.google.com/maps?q=${activity.latitude},${activity.longitude}" target="_blank" style="color: #667eea; text-decoration: none;">🗺️ View on Google Maps</a>
                    </div>
                ` : ''}
            </div>
        `;

        Swal.fire({
            title: 'Borrower Information',
            html: htmlContent,
            width: '700px',
            confirmButtonText: 'Close',
            confirmButtonColor: '#667eea',
            customClass: {
                popup: 'borrower-modal-popup'
            }
        });
    }
</script>

<style>
    .borrower-modal-popup {
        border-radius: 15px;
    }
</style>
@endsection