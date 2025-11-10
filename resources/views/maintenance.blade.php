@extends('layouts.app')

@section('title', 'Maintenance')

@section('content')
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

        /* Sidebar styles - completely fixed position */
        .sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            z-index: 1000 !important;
            scroll-behavior: auto !important;
        }

        .sidebar * {
            position: relative;
        }

        .main-content {
            padding: 30px 40px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title::before {
            content: "🔧";
            font-size: 24px;
        }

        .stats-overview {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-badge {
            background: white;
            padding: 12px 20px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-usable {
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        .stat-unusable {
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .success-alert {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: none;
            border-left: 4px solid #28a745;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 25px;
            color: #155724;
            font-weight: 500;
            box-shadow: 0 2px 15px rgba(40, 167, 69, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-alert::before {
            content: "✅";
            font-size: 18px;
        }

        /* Computer Lab cards */
        .comlab-container {
            display: grid;
            gap: 25px;
        }

        .comlab-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .comlab-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .comlab-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .comlab-header:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
        }

        .comlab-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .comlab-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .comlab-title::before {
            content: "🖥️";
            font-size: 22px;
        }

        .comlab-subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 500;
        }

        .comlab-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .comlab-stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toggle-icon {
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .comlab-card.expanded .toggle-icon {
            transform: rotate(90deg);
        }

        .comlab-content {
            display: none;
            padding: 0;
        }

        .comlab-card.expanded .comlab-content {
            display: block;
        }

        /* Fullset cards inside computer labs */
        .fullsets-container {
            display: grid;
            gap: 20px;
            padding: 25px;
            background: #f8f9fa;
        }

        .fullset-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .fullset-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .fullset-header {
            padding: 18px 22px;
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .fullset-header:hover {
            background: linear-gradient(135deg, #2c3e50, #1a252f);
        }

        .fullset-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .fullset-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fullset-title::before {
            content: "💻";
            font-size: 16px;
        }

        .fullset-room {
            font-size: 13px;
            opacity: 0.9;
            font-weight: 500;
        }

        .fullset-stats {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .fullset-stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .fullset-toggle-icon {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .fullset-card.expanded .fullset-toggle-icon {
            transform: rotate(90deg);
        }

        .fullset-content {
            display: none;
            padding: 0;
        }

        .fullset-card.expanded .fullset-content {
            display: block;
        }

        .device-brand-model {
            font-size: 14px;
            line-height: 1.3;
        }

        .device-brand-model strong {
            color: #2c3e50;
        }

        .device-brand-model small {
            color: #6c757d;
            font-size: 12px;
        }

        .table-container {
            max-height: none;
            overflow: visible;
        }

        .maintenance-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .maintenance-table thead {
            background: linear-gradient(135deg, #2c3e50, #34495e);
        }

        .maintenance-table th {
            padding: 18px 16px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            text-align: left;
        }

        .maintenance-table td {
            padding: 16px;
            border: none;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .maintenance-table tbody tr:last-child td {
            border-bottom: none;
        }

        .maintenance-table tbody tr:hover {
            background: #f8f9fa;
            transition: background 0.3s ease;
        }

        .device-photo {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e9ecef;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .device-photo:hover {
            transform: scale(1.1);
        }

        .no-photo {
            width: 60px;
            height: 60px;
            background: #e9ecef;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 20px;
        }

        .barcode-text {
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #495057;
            border: 1px solid #e9ecef;
            font-weight: 600;
        }

        .device-category {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .serial-number {
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #495057;
            border: 1px solid #e9ecef;
        }

        .device-description {
            color: #6c757d;
            font-size: 14px;
            max-width: 200px;
            line-height: 1.4;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status.usable {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.unusable {
            background: linear-gradient(135deg, #f8d7da, #f1c2c7);
            color: #721c24;
            border: 1px solid #f1c2c7;
        }

        .status.not-set {
            background: #e9ecef;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .status-form select {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            color: #495057;
            font-size: 13px;
            min-width: 100px;
            transition: border-color 0.3s ease;
        }

        .status-form select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-update {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-update::before {
            content: "🔄";
            font-size: 12px;
        }

        /* Item Notes Section */
        .item-notes-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            margin: 10px 0;
            animation: slideDown 0.3s ease-out;
        }

        .item-notes-header {
            padding: 12px 16px;
            background: #fff9e6;
            border-bottom: 1px solid #ffeaa7;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #856404;
        }

        .item-notes-header::before {
            content: "⚠️";
            font-size: 16px;
        }

        .item-notes-form {
            padding: 16px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .item-notes-textarea {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #ffeaa7;
            border-radius: 8px;
            resize: vertical;
            min-height: 80px;
            font-size: 14px;
            background: white;
            transition: border-color 0.3s ease;
        }

        .item-notes-textarea:focus {
            outline: none;
            border-color: #f39c12;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .btn-save-item-note {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-save-item-note:hover {
            background: linear-gradient(135deg, #e67e22, #d35400);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-save-item-note::before {
            content: "💾";
            font-size: 12px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                max-height: 200px;
                transform: translateY(0);
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.6;
        }

        .empty-state-text {
            font-size: 18px;
            color: #6c757d;
            margin: 0;
        }

        /* Photo modal styles */
        .photo-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .photo-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .photo-modal img {
            width: 100%;
            height: auto;
            display: block;
        }

        .photo-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .photo-modal-close:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        /* Responsive design */
        @media (max-width: 1200px) {
            .maintenance-table {
                font-size: 14px;
            }
            
            .maintenance-table th,
            .maintenance-table td {
                padding: 12px 10px;
            }
            
            .device-photo,
            .no-photo {
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 180px !important;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-overview {
                width: 100%;
                justify-content: space-between;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .maintenance-table {
                min-width: 800px;
            }
            
            .status-form {
                flex-direction: column;
                gap: 5px;
            }
            
            .status-form select {
                min-width: 80px;
                font-size: 12px;
            }
            
            .btn-update {
                padding: 6px 12px;
                font-size: 12px;
            }

            .comlab-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .comlab-stats {
                align-self: stretch;
                justify-content: space-between;
            }

            .fullset-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .fullset-stats {
                align-self: stretch;
                justify-content: space-between;
            }

            .item-notes-form {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                height: 100vh !important;
                z-index: 1001 !important;
            }
            
            .sidebar.active {
                transform: translateX(0) !important;
            }
            
            .main-content {
                margin: 0 !important;
                width: 100% !important;
            }
        }

        /* Additional fixes for mobile browsers */
        @supports (-webkit-overflow-scrolling: touch) {
            .sidebar {
                -webkit-overflow-scrolling: touch !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                height: 100vh !important;
                height: 100dvh !important;
            }
        }

        @supports not (height: 100dvh) {
            .sidebar {
                height: 100vh !important;
            }
        }
    </style>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Computer Lab Maintenance</h1>
            <div class="stats-overview">
                @php
                    $totalUsable = 0;
                    $totalUnusable = 0;
                    
                    // Group items by Room → PC# → PC data
                    $roomGroups = collect();
                    
                    if(isset($fullsets) && $fullsets->isNotEmpty()) {
                        foreach($fullsets as $fullsetId => $fullset) {
                            $room = $fullset['room'] ?? 'Other';
                            
                            // Initialize room group if it doesn't exist
                            if (!$roomGroups->has($room)) {
                                $roomGroups->put($room, [
                                    'pcGroups' => collect(),
                                    'total_usable' => 0,
                                    'total_unusable' => 0,
                                    'total_items' => 0
                                ]);
                            }
                            
                            $roomData = $roomGroups->get($room);
                            
                            // Extract PC number from items in this fullset
                            $pcNumber = null;
                            $items = $fullset['items'] ?? collect();
                            
                            // Try to extract PC number from barcode/serial numbers
                            foreach($items as $item) {
                                // Look for 3-digit number suffix (001, 002, etc.)
                                if (preg_match('/(\d{3})$/', $item->barcode, $matches)) {
                                    $pcNumber = intval($matches[1]);
                                    break;
                                }
                                elseif (preg_match('/(\d{3})$/', $item->serial_number, $matches)) {
                                    $pcNumber = intval($matches[1]);
                                    break;
                                }
                            }
                            
                            // If no PC number found, try to extract from fullset ID
                            if ($pcNumber === null && preg_match('/(\d{3})$/', $fullsetId, $matches)) {
                                $pcNumber = intval($matches[1]);
                            }
                            
                            // Fallback: use a default PC number
                            if ($pcNumber === null) {
                                $pcNumber = 1;
                            }
                            
                            $pcKey = 'PC' . str_pad($pcNumber, 3, '0', STR_PAD_LEFT);
                            
                            // Initialize PC group if it doesn't exist
                            if (!$roomData['pcGroups']->has($pcKey)) {
                                $roomData['pcGroups']->put($pcKey, [
                                    'items' => collect(),
                                    'usable_count' => 0,
                                    'unusable_count' => 0,
                                    'total_count' => 0,
                                    'pc_number' => $pcNumber
                                ]);
                            }
                            
                            $pcData = $roomData['pcGroups']->get($pcKey);
                            
                            // Add items to PC group
                            foreach($items as $item) {
                                $pcData['items']->push($item);
                                $pcData['total_count']++;
                                
                                if ($item->status === 'Usable') {
                                    $pcData['usable_count']++;
                                } elseif ($item->status === 'Unusable') {
                                    $pcData['unusable_count']++;
                                }
                            }
                            
                            $roomData['pcGroups']->put($pcKey, $pcData);
                            
                            // Update room totals
                            $roomData['total_usable'] += $pcData['usable_count'];
                            $roomData['total_unusable'] += $pcData['unusable_count'];
                            $roomData['total_items'] += $pcData['total_count'];
                            
                            $roomGroups->put($room, $roomData);
                            
                            $totalUsable += $pcData['usable_count'];
                            $totalUnusable += $pcData['unusable_count'];
                        }
                    }
                @endphp
                <div class="stat-badge stat-usable">
                    <span>{{ $totalUsable }} Usable</span>
                </div>
                <div class="stat-badge stat-unusable">
                    <span>{{ $totalUnusable }} Unusable</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="success-alert">
                {{ session('success') }}
            </div>
        @endif

        @if($roomGroups->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🔧</div>
                <p class="empty-state-text">No maintenance items found</p>
            </div>
        @else
            <div class="comlab-container">
                @foreach($roomGroups as $roomName => $roomData)
                    <div class="comlab-card" id="room-{{ str_replace(' ', '-', strtolower($roomName)) }}">
                        <div class="comlab-header" onclick="toggleRoom('{{ str_replace(' ', '-', strtolower($roomName)) }}')">
                            <div class="comlab-info">
                                <h2 class="comlab-title">{{ $roomName }}</h2>
                                <div class="comlab-subtitle">
                                    {{ $roomData['pcGroups']->count() }} PC Groups • {{ $roomData['total_items'] }} Total Items
                                </div>
                            </div>
                            <div class="comlab-stats">
                                <div class="comlab-stat" style="background: rgba(40, 167, 69, 0.3);">
                                    <span>✅ {{ $roomData['total_usable'] }}</span>
                                </div>
                                <div class="comlab-stat" style="background: rgba(220, 53, 69, 0.3);">
                                    <span>❌ {{ $roomData['total_unusable'] }}</span>
                                </div>
                                <span class="toggle-icon">▶</span>
                            </div>
                        </div>

                        <div class="comlab-content">
                            <div class="fullsets-container">
                                @foreach($roomData['pcGroups']->sortBy('pc_number') as $pcKey => $pcData)
                                    <div class="fullset-card" id="pc-{{ str_replace(' ', '-', strtolower($roomName)) }}-{{ $pcData['pc_number'] }}">
                                        <div class="fullset-header" onclick="togglePC('{{ str_replace(' ', '-', strtolower($roomName)) }}-{{ $pcData['pc_number'] }}')">
                                            <div class="fullset-info">
                                                <h3 class="fullset-title">
                                                    <div class="barcode-text">{{ $pcKey }}</div>
                                                </h3>
                                                <div class="fullset-room">{{ $roomName }}</div>
                                            </div>
                                            <div class="fullset-stats">
                                                <div class="fullset-stat" style="background: rgba(40, 167, 69, 0.2);">
                                                    <span>✅ {{ $pcData['usable_count'] }}</span>
                                                </div>
                                                <div class="fullset-stat" style="background: rgba(220, 53, 69, 0.2);">
                                                    <span>❌ {{ $pcData['unusable_count'] }}</span>
                                                </div>
                                                <div class="fullset-stat">
                                                    <span>📊 {{ $pcData['total_count'] }}</span>
                                                </div>
                                                <span class="fullset-toggle-icon">▶</span>
                                            </div>
                                        </div>

                                        <div class="fullset-content">
                                            <!-- Items Table -->
                                            <div class="table-container">
                                                <table class="maintenance-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Photo</th>
                                                            <th>Barcode</th>
                                                            <th>Category</th>
                                                            <th>Brand/Model</th>
                                                            <th>Serial Number</th>
                                                            <th>Description</th>
                                                            <th>Status</th>
                                                            <th>Update Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pcData['items']->groupBy('device_category') as $category => $categoryItems)
                                                            @foreach($categoryItems as $item)
                                                                <tr class="category-{{ str_replace(' ', '-', strtolower($category)) }}" id="item-row-{{ $item->id }}">
                                                                    <td>
                                                                        @if($item->photo)
                                                                            <img src="{{ route('room-item.photo', $item->id) }}" 
                                                                                alt="Device Photo" 
                                                                                class="device-photo"
                                                                                onclick="openPhotoModal(this.src)"
                                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                            <div class="no-photo" style="display: none;">📷</div>
                                                                        @else
                                                                            <div class="no-photo">📷</div>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <div class="barcode-text">{{ $item->barcode }}</div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="device-category">{{ $item->device_category }}</div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="device-brand-model">
                                                                            <strong>{{ $item->brand }}</strong>
                                                                            @if($item->model)
                                                                                <br><small>{{ $item->model }}</small>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <code class="serial-number">{{ $item->serial_number }}</code>
                                                                    </td>
                                                                    <td>
                                                                        <div class="device-description">{{ $item->description }}</div>
                                                                    </td>
                                                                    <td>
                                                                        @if($item->status === 'Usable')
                                                                            <span class="status usable">Usable</span>
                                                                        @elseif($item->status === 'Unusable')
                                                                            <span class="status unusable">Unusable</span>
                                                                        @else
                                                                            <span class="status not-set">Not Set</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <form action="{{ url('/maintenance/update-status/' . $item->id) }}" 
                                                                            method="POST" 
                                                                            class="status-form"
                                                                            onchange="toggleItemNotes({{ $item->id }}, this.querySelector('select').value)">
                                                                            @csrf
                                                                            <select name="status">
                                                                                <option value="Usable" {{ $item->status == 'Usable' ? 'selected' : '' }}>
                                                                                    Usable
                                                                                </option>
                                                                                <option value="Unusable" {{ $item->status == 'Unusable' ? 'selected' : '' }}>
                                                                                    Unusable
                                                                                </option>
                                                                            </select>
                                                                            <button type="submit" class="btn-update">
                                                                                Update
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                
                                                                <!-- Item-specific notes section (only shows when status is Unusable) -->
                                                                <tr id="notes-row-{{ $item->id }}" class="item-notes-row" style="{{ $item->status === 'Unusable' ? '' : 'display: none;' }}">
                                                                    <td colspan="8">
                                                                        <div class="item-notes-section">
                                                                            <div class="item-notes-header">
                                                                                Issue Details for {{ $item->device_category }} ({{ $item->barcode }})
                                                                            </div>
                                                                            <form action="{{ url('/maintenance/item-note/' . $item->id) }}" method="POST" class="item-notes-form">
                                                                                @csrf
                                                                                <textarea 
                                                                                    name="note" 
                                                                                    class="item-notes-textarea" 
                                                                                    placeholder="Describe the issue with this {{ strtolower($item->device_category) }}..."
                                                                                >{{ isset($itemNotes[$item->id]) ? $itemNotes[$item->id]->note : '' }}</textarea>
                                                                                <button type="submit" class="btn-save-item-note">Save Issue</button>
                                                                            </form>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Photo Modal -->
    <div id="photoModal" class="photo-modal" onclick="closePhotoModal(event)">
        <div class="photo-modal-content">
            <button class="photo-modal-close" onclick="closePhotoModal()">&times;</button>
            <img id="modalImage" src="" alt="Full Size Photo">
        </div>
    </div>

    <script>
        function toggleRoom(roomId) {
            const card = document.getElementById('room-' + roomId);
            card.classList.toggle('expanded');
        }

        function togglePC(pcId) {
            const card = document.getElementById('pc-' + pcId);
            card.classList.toggle('expanded');
        }

        function toggleItemNotes(itemId, status) {
            const notesRow = document.getElementById('notes-row-' + itemId);
            if (status === 'Unusable') {
                notesRow.style.display = '';
                // Add smooth animation
                setTimeout(() => {
                    notesRow.querySelector('.item-notes-section').style.animation = 'slideDown 0.3s ease-out';
                }, 10);
            } else {
                notesRow.style.display = 'none';
            }
        }

        function openPhotoModal(imageSrc) {
            const modal = document.getElementById('photoModal');
            const modalImage = document.getElementById('modalImage');
            
            modalImage.src = imageSrc;
            modal.style.display = 'block';
            
            // Prevent body scrolling when modal is open
            document.body.style.overflow = 'hidden';
        }

        function closePhotoModal(event) {
            // Only close if clicking on the modal background or close button
            if (!event || event.target.id === 'photoModal' || event.target.className === 'photo-modal-close') {
                const modal = document.getElementById('photoModal');
                modal.style.display = 'none';
                
                // Restore body scrolling
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePhotoModal();
            }
        });

        // Auto-expand first room for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const firstRoom = document.querySelector('.comlab-card');
            if (firstRoom) {
                firstRoom.classList.add('expanded');
                
                // Also expand the first PC within the first room
                const firstPC = firstRoom.querySelector('.fullset-card');
                if (firstPC) {
                    firstPC.classList.add('expanded');
                }
            }
        });
    </script>
@endsection