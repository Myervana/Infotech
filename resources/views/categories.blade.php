@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
    .container {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 20px;
        max-width: 1200px;
        margin: auto;
    }
    .box {
        background: white;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .category-total {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 8px 12px;
        margin: 5px 0;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .category-total .category-name {
        font-weight: 600;
        color: #1976d2;
    }
    .category-total .category-count {
        background: #2196f3;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    .totals-section {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
    }
    .totals-title {
        font-size: 16px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .no-totals {
        color: #6c757d;
        font-style: italic;
        text-align: center;
        padding: 20px;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-6 box">
            <h4>ALL CATEGORIES (from Room Items)</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Items Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roomItemCategories as $index => $category)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $category }}</td>
                            <td>{{ $itemCounts[$category] ?? 0 }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="loadItems('{{ $category }}', 'category')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-6 box">
            <h4>ALL ROOMS</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $index => $room)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $room->room_title }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="loadItems('{{ $room->room_title }}', 'room')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Enhanced Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Category Totals Section -->
                <div id="categoryTotalsSection" style="display: none;">
                    <div class="totals-section">
                        <div class="totals-title">
                            <i class="fas fa-chart-bar"></i>
                            <span id="roomTotalTitle">Category Totals</span>
                        </div>
                        <div id="categoryTotalsList">
                            <!-- Category totals will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Room</th>
                            <th>Category</th>
                            <th>Serial</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="modalItemList">
                        <tr><td colspan="5">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function loadItems(identifier, type) {
        // Update modal title
        const modalTitle = document.getElementById('itemModalLabel');
        modalTitle.textContent = type === 'room' ? `Room: ${identifier}` : `Category: ${identifier}`;
        
        // Show/hide category totals section based on type
        const categoryTotalsSection = document.getElementById('categoryTotalsSection');
        const roomTotalTitle = document.getElementById('roomTotalTitle');
        
        if (type === 'room') {
            categoryTotalsSection.style.display = 'block';
            roomTotalTitle.textContent = `${identifier} - Category Totals`;
        } else {
            categoryTotalsSection.style.display = 'none';
        }

        fetch(`/categories/items/${encodeURIComponent(identifier)}?type=${type}`)
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('modalItemList');
                list.innerHTML = '';
                
                // Load category totals if it's a room
                if (type === 'room' && data.categoryTotals) {
                    loadCategoryTotals(data.categoryTotals);
                }
                
                if (data.items.length === 0) {
                    list.innerHTML = '<tr><td colspan="5">No items found.</td></tr>';
                } else {
                    data.items.forEach(item => {
                        list.innerHTML += `
                            <tr>
                                <td><img src="/storage/${item.photo}" width="50" height="50" onerror="this.src='/default.png'" /></td>
                                <td>${item.room_title}</td>
                                <td>${item.device_category}</td>
                                <td>${item.serial_number}</td>
                                <td><span class="badge ${getStatusBadgeClass(item.status)}">${item.status}</span></td>
                            </tr>`;
                    });
                }
            })
            .catch(error => {
                console.error('Error loading items:', error);
                document.getElementById('modalItemList').innerHTML = '<tr><td colspan="5">Error loading items.</td></tr>';
                
                // Hide totals section on error
                document.getElementById('categoryTotalsSection').style.display = 'none';
            });
    }

    function loadCategoryTotals(categoryTotals) {
        const totalsList = document.getElementById('categoryTotalsList');
        totalsList.innerHTML = '';
        
        if (!categoryTotals || Object.keys(categoryTotals).length === 0) {
            totalsList.innerHTML = '<div class="no-totals">No items found in this room.</div>';
            return;
        }
        
        // Sort categories by count (descending)
        const sortedCategories = Object.entries(categoryTotals)
            .sort(([,a], [,b]) => b - a);
        
        sortedCategories.forEach(([category, count]) => {
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'category-total';
            categoryDiv.innerHTML = `
                <span class="category-name">${category}</span>
                <span class="category-count">${count}</span>
            `;
            totalsList.appendChild(categoryDiv);
        });
    }

    function getStatusBadgeClass(status) {
        switch(status.toLowerCase()) {
            case 'active':
            case 'working':
                return 'bg-success';
            case 'inactive':
            case 'broken':
                return 'bg-danger';
            case 'maintenance':
                return 'bg-warning';
            default:
                return 'bg-secondary';
        }
    }
</script>
@endsection