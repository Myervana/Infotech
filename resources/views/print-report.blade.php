@php use Carbon\Carbon; use Milon\Barcode\Facades\DNS1DFacade as DNS1D; @endphp

@extends('layouts.app')

@section('title', 'Print Report')

@section('content')
<style>
    /* Responsive Print Report Styles */
/* Add this to your existing CSS file or include it in your blade template */

.main-content {
    max-width: 80%;
    margin: 0 auto;
    padding: 10px;
    background-color: #f8f9fa;
    min-height: 100vh;
}

.page-header {
    text-align: center;
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 2px solid #e9ecef;
}

.page-title {
    font-size: 28px;
    color: #343a40;
    margin: 0;
    font-weight: 700;
}

.report-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.section-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-form select,
.filter-form button,
.section-header button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-form select {
    background: white;
    color: #333;
    border: 1px solid #ddd;
}

.filter-form button,
.section-header button {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.filter-form button:hover,
.section-header button:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

.stats-overview {
    padding: 20px 25px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.stats-overview span {
    font-size: 16px;
    color: #495057;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

thead {
    background: #f8f9fa;
}

th {
    padding: 10px 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

td {
    padding: 8px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

tbody tr:nth-child(even) {
    background-color: #fdfdfd;
}

/* Status Styles */
.status {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    text-align: center;
}

.status.active,
.status.borrowed {
    background-color: #d4edda;
    color: #155724;
}

.status.returned {
    background-color: #cce5ff;
    color: #004085;
}

.status.overdue {
    background-color: #f8d7da;
    color: #721c24;
}

/* Code styling for serial numbers */
code {
    background-color: #f1f3f4;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #5f6368;
}

/* Image styling */
img {
    border-radius: 4px;
    object-fit: cover;
}

/* No photo placeholder */
.no-photo-placeholder {
    width: 60px;
    height: 60px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 12px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    text-align: center;
}

/* Barcode container */
td div {
    text-align: center;
    font-size: 11px;
    color: #666;
    margin-top: 4px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .main-content {
        padding: 15px;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    table {
        font-size: 13px;
    }
    
    th, td {
        padding: 10px 8px;
    }
}

@media (max-width: 992px) {
    .section-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .filter-form {
        justify-content: center;
    }
    
    .stats-overview {
        justify-content: center;
        text-align: center;
    }
    
    /* Make tables horizontally scrollable on medium screens */
    .report-section {
        overflow-x: auto;
    }
    
    table {
        min-width: 800px;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 10px;
    }
    
    .page-title {
        font-size: 1.75rem;
    }
    
    .section-header {
        padding: 15px 20px;
    }
    
    .section-header h2 {
        font-size: 1.25rem;
    }
    
    .stats-overview {
        padding: 15px 20px;
        gap: 20px;
    }
    
    table {
        font-size: 12px;
        min-width: 700px;
    }
    
    th, td {
        padding: 8px 6px;
    }
    
    img {
        width: 50px !important;
        height: 50px !important;
    }
    
    .no-photo-placeholder {
        width: 50px !important;
        height: 50px !important;
        font-size: 10px;
    }
    
    .filter-form {
        gap: 8px;
    }
    
    .filter-form select,
    .filter-form button,
    .section-header button {
        padding: 6px 12px;
        font-size: 13px;
    }
}

@media (max-width: 576px) {
    .main-content {
        padding: 8px;
    }
    
    .page-header {
        padding: 15px 0;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .section-header {
        padding: 12px 15px;
    }
    
    .section-header h2 {
        font-size: 1.1rem;
    }
    
    .stats-overview {
        padding: 12px 15px;
        flex-direction: column;
        gap: 10px;
    }
    
    table {
        font-size: 11px;
        min-width: 600px;
    }
    
    th, td {
        padding: 6px 4px;
    }
    
    img {
        width: 40px !important;
        height: 40px !important;
    }
    
    .no-photo-placeholder {
        width: 40px !important;
        height: 40px !important;
        font-size: 8px;
    }
    
    code {
        font-size: 10px;
        padding: 2px 4px;
    }
    
    .status {
        font-size: 10px;
        padding: 2px 6px;
    }
}

/* Print Styles */
@media print {
    .main-content {
        background-color: white;
        padding: 0;
        max-width: 100%;
    }
    
    .report-section {
        box-shadow: none;
        border: 1px solid #ddd;
        break-inside: avoid;
        margin-bottom: 10px;
    }
    
    .section-header {
        background: #f8f9fa !important;
        color: #333 !important;
        -webkit-print-color-adjust: exact;
    }
    
    .filter-form button,
    .section-header button {
        display: none;
    }
    
    table {
        font-size: 12px;
    }
    
    th, td {
        padding: 8px 6px;
    }
    
    tbody tr:hover {
        background-color: transparent;
    }
    
    img {
        max-width: 50px;
        max-height: 50px;
    }
    
    .no-photo-placeholder {
        max-width: 50px;
        max-height: 50px;
    }
}
</style>
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Print Reports</h1>
    </div>

    {{-- Borrow Reports Section --}}
    <div class="report-section" id="borrow-report-section">
        <div class="section-header">
            <h2>Borrow Reports ({{ ucfirst($filter ?? 'daily') }})</h2>
            <form method="GET" action="{{ url('/print-report') }}" class="filter-form">
                <select name="filter">
                    <option value="daily" {{ ($filter ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ ($filter ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ ($filter ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
                <button type="submit">Apply</button>
                <button type="button" onclick="printSection('borrow-report-section')">🖨️ Print</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Borrower</th>
                    <th>Device</th>
                    <th>Serial #</th>
                    <th>Room</th>
                    <th>Borrowed At</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrowedItems as $index => $borrow)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $borrow->borrower_name }}</td>
                        <td>{{ $borrow->item->device_category ?? 'N/A' }} - {{ $borrow->item->device_type ?? 'N/A' }}</td>
                        <td><code>{{ $borrow->item->serial_number ?? 'N/A' }}</code></td>
                        <td>{{ $borrow->item->room_title ?? 'N/A' }}</td>
                        <td>{{ Carbon::parse($borrow->borrow_date)->format('M d, Y') }}</td>
                        <td>{{ $borrow->return_date ? Carbon::parse($borrow->return_date)->format('M d, Y') : 'N/A' }}</td>
                        <td class="status {{ $borrow->status }}">{{ $borrow->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">No borrowed items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Room Management Section --}}
    <div class="report-section" id="room-management-section">
        <div class="section-header">
            <h2>Room Management</h2>
            <button onclick="printSection('room-management-section')">🖨️ Print</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Room Title</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Serial #</th>
                    <th>Description</th>
                    <th>Barcode</th>
                    <th>Status</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomItems as $item)
                    <tr>
                        <td>
                            @if($item->photo)
                                <img src="{{ route('room-item.photo', $item->id) }}" 
                                     width="60" 
                                     height="60"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="no-photo-placeholder" style="display: none;">📷</div>
                            @else
                                <div class="no-photo-placeholder">📷</div>
                            @endif
                        </td>
                        <td>{{ $item->room_title }}</td>
                        <td>{{ $item->device_category }}</td>
                        <td>{{ $item->device_type ?? 'Uncategorized' }}</td>
                        <td><code>{{ $item->serial_number }}</code></td>
                        <td>{{ $item->description }}</td>
                        <td>
                            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->barcode ?? '000000000', 'C128', 1.5, 35) }}" alt="{{ $item->barcode ?? 'N/A' }}">
                            <div>{{ $item->barcode }}</div>
                        </td>
                        <td>{{ $item->status }}</td>
                        <td>{{ $item->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Room Titles Summary Section --}}
    <div class="report-section" id="room-titles-section">
        <div class="section-header">
            <h2>Room Titles Summary</h2>
            <button onclick="printSection('room-titles-section')">🖨️ Print</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Room Title</th>
                    <th>Total Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomItems->groupBy('room_title') as $index => $group)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $index }}</td>
                        <td>{{ $group->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Maintenance Section --}}
    <div class="report-section" id="maintenance-section">
        <div class="section-header">
            <h2>Maintenance</h2>
            <button onclick="printSection('maintenance-section')">🖨️ Print</button>
        </div>
        <div class="stats-overview">
            <span><strong>{{ $roomItems->where('status', 'Usable')->count() }}</strong> Usable</span>
            <span><strong>{{ $roomItems->where('status', 'Unusable')->count() }}</strong> Unusable</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Room</th>
                    <th>Category</th>
                    <th>Serial Number</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomItems as $item)
                    <tr>
                        <td>
                            @if($item->photo)
                                <img src="{{ route('room-item.photo', $item->id) }}" 
                                     width="60" 
                                     height="60"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="no-photo-placeholder" style="display: none;">📷</div>
                            @else
                                <div class="no-photo-placeholder">📷</div>
                            @endif
                        </td>
                        <td>{{ $item->room_title }}</td>
                        <td>{{ $item->device_category }}</td>
                        <td><code>{{ $item->serial_number }}</code></td>
                        <td>{{ $item->description }}</td>
                        <td>
                            @if($item->status === 'Usable')
                                <span style="color: green; font-weight: 600;">Usable</span>
                            @elseif($item->status === 'Unusable')
                                <span style="color: red; font-weight: 600;">Unusable</span>
                            @else
                                <span style="color: gray; font-weight: 600;">Not Set</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function printSection(sectionId) {
        const section = document.getElementById(sectionId);
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Report</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    .section-header { background: #f8f9fa; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
                    .section-header h2 { margin: 0; color: #333; }
                    .stats-overview { background: #f8f9fa; padding: 15px; margin-bottom: 10px; }
                    .no-photo-placeholder { 
                        width: 60px; 
                        height: 60px; 
                        background: #f8f9fa; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        border: 1px solid #ddd; 
                        font-size: 12px; 
                        color: #666;
                    }
                    img { max-width: 60px; max-height: 60px; object-fit: cover; }
                    code { 
                        background: #f1f1f1; 
                        padding: 2px 4px; 
                        border-radius: 3px; 
                        font-family: monospace; 
                    }
                    .filter-form, button { display: none; }
                </style>
            </head>
            <body>
                ${section.innerHTML}
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
        
        // Wait for images to load before printing
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 1000);
    }
</script>
@endsection