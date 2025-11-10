@php
    use Illuminate\Support\Str;
@endphp

<h3>🔍 Result for Barcode: <code>{{ $barcode }}</code></h3>

@php
    $fullSets = [];
    $individualItems = [];

    foreach($items as $item) {
        if (preg_match('/^(PC|Monitor|Keyboard|Mouse|PSU)(\d+)$/i', $item->serial_number, $matches)) {
            $setId = $matches[2];
            if (!isset($fullSets[$setId])) {
                $fullSets[$setId] = [];
            }
            $fullSets[$setId][] = $item;
        } else {
            $individualItems[] = $item;
        }
    }
@endphp

{{-- Display Full Sets --}}
@foreach($fullSets as $setId => $setItems)
    <div class="full-set-container">
        <div class="full-set-header">
            <i class="fas fa-desktop"></i>
            Full Set {{ $setId }}
            <span class="component-count">{{ count($setItems) }} Components</span>
        </div>
        <div class="full-set-items">
            <div class="set-summary">
                <h4>Set Information</h4>
                <div class="set-meta">
                    <div><strong>Room:</strong> {{ $setItems[0]->room_title }}</div>
                    <div><strong>Brand:</strong> {{ $setItems[0]->brand ?? 'N/A' }}</div>
                    <div><strong>Model:</strong> {{ $setItems[0]->model ?? 'N/A' }}</div>
                    <div><strong>Set ID:</strong> {{ $setId }}</div>
                </div>
            </div>
            <div class="full-set-grid">
                @foreach($setItems as $item)
                    <div class="full-set-item-box">
                        <div class="full-set-item-flex">
                            <div class="photo-wrapper-small">
                                @if($item->photo)
                                    <img src="{{ route('room-item.photo', $item->id) }}" alt="Item Photo">
                                @else
                                    <i class="fas fa-image"></i>
                                @endif
                            </div>
                            <div class="full-set-item-info">
                                <div class="full-set-item-title">
                                    @if(str_contains($item->serial_number, 'PC'))
                                        <i class="fas fa-desktop"></i> System Unit
                                    @elseif(str_contains($item->serial_number, 'Monitor'))
                                        <i class="fas fa-tv"></i> Monitor
                                    @elseif(str_contains($item->serial_number, 'Keyboard'))
                                        <i class="fas fa-keyboard"></i> Keyboard
                                    @elseif(str_contains($item->serial_number, 'Mouse'))
                                        <i class="fas fa-mouse"></i> Mouse
                                    @elseif(str_contains($item->serial_number, 'PSU'))
                                        <i class="fas fa-plug"></i> Power Supply
                                    @else
                                        <i class="fas fa-cog"></i> {{ $item->device_category }}
                                    @endif
                                </div>
                                <div><span class="label">Serial:</span> {{ $item->serial_number }}</div>
                                <div><span class="label">Category:</span> {{ $item->device_category }}</div>
                                @if($item->description)
                                    <div><span class="label">Description:</span> {{ Str::limit($item->description, 50) }}</div>
                                @endif
                                <div>
                                    <span class="label">Status:</span>
                                    <span class="status-small {{ $item->status }}">{{ $item->status }}</span>
                                </div>
                                <div class="barcode-image-small">
                                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->barcode, 'C128', 1.5, 40) }}" alt="Barcode">
                                    <div class="barcode-text-small">{{ $item->barcode }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach

{{-- Display Individual Items if any --}}
@if(count($individualItems))
    <div class="item-box">
        @foreach($individualItems as $item)
            <div class="full-set-item-flex" style="gap:12px;">
                <div class="photo-wrapper-small">
                    @if($item->photo)
                        <img src="{{ route('room-item.photo', $item->id) }}" alt="Item Photo">
                    @else
                        <i class="fas fa-image"></i>
                    @endif
                </div>
                <div class="full-set-item-info">
                    <div class="full-set-item-title">{{ $item->brand ?? 'N/A' }} {{ $item->model ?? 'N/A' }}</div>
                    <div><span class="label">Serial:</span> {{ $item->serial_number }}</div>
                    <div><span class="label">Category:</span> {{ $item->device_category }}</div>
                    @if($item->description)
                        <div><span class="label">Description:</span> {{ Str::limit($item->description, 50) }}</div>
                    @endif
                    <div>
                        <span class="label">Status:</span>
                        <span class="status-small {{ $item->status }}">{{ $item->status }}</span>
                    </div>
                    <div class="barcode-image-small">
                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->barcode, 'C128', 1.5, 40) }}" alt="Barcode">
                        <div class="barcode-text-small">{{ $item->barcode }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif