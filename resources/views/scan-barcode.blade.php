@extends('layouts.app')

@php use Milon\Barcode\Facades\DNS1DFacade as DNS1D; @endphp

@push('styles')
<style>
/* New style for the page title */
    .page-title {
        text-align: center;
        font-size: 36px; /* Larger font size for a main title */
        color: #2c3e50; /* A darker, more prominent color */
        margin-bottom: 40px; /* More space below the title */
        font-weight: 700; /* Bolder */
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05); /* Subtle shadow */
    }

    .scan-container {
        max-width: 500px;
        margin: 0 auto;
        background: #ffffff;
        padding: 35px 40px;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .scan-container h2 { /* This rule can now be removed if not needed for inner headings */
        /* text-align: center; */
        /* margin-bottom: 30px; */
        /* font-size: 28px; */
        /* color: #343a40; */
        display: none; /* Hide the old H2 inside scan-container */
    }

    .form-group {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
    }

    input[type="text"] {
        flex: 1;
        padding: 12px 15px;
        font-size: 16px;
        border: 1.5px solid #ddd;
        border-radius: 8px;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    /* Scanner input indicator */
    input[type="text"].scanner-input {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }

    button {
        padding: 12px 20px;
        background: #0d6efd;
        border: none;
        color: #fff;
        font-size: 16px;
        cursor: pointer;
        border-radius: 8px;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #0b5ed7;
    }

    .result h3 {
        margin-bottom: 20px;
        font-size: 20px;
        color: #495057;
    }
/* Exit button styling */
    .exit-button {
        background: #28a745;
        margin-top: 20px;
        width: 70%;
        padding: 15px;
        font-size: 16px;
        font-weight: 600;
        text-align: center;
        display: block;
        text-decoration: none;
        color: white;
        border-radius: 8px;
        transition: background 0.3s ease;
        margin-left: auto; /* Center the button */
        margin-right: auto; /* Center the button */
    }

    .exit-button:hover {
        background: #218838;
        text-decoration: none;
        color: white;
    }

    .exit-button:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
    }
    /* Full Set Container */
    .full-set-container {
        border: 2px solid #0d6efd;
        border-radius: 12px;
        margin-bottom: 25px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
    }

    .full-set-header {
        background: #0d6efd;
        color: white;
        padding: 15px 20px;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .full-set-items {
        padding: 20px;
    }

    .full-set-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
    }

    /* Individual Item Box */
    .item-box {
        border: 1px solid #e3e3e3;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        background: #fcfcfc;
        display: flex;
        gap: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        transition: box-shadow 0.3s ease;
    }

    .item-box:hover {
        box-shadow: 0 6px 14px rgba(0,0,0,0.07);
    }

    /* Full Set Item Box */
    .full-set-item-box {
        border: 1px solid #d1ecf1;
        padding: 15px;
        border-radius: 8px;
        background: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .full-set-item-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .photo-wrapper {
        width: 100px;
        height: 100px;
        background: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .photo-wrapper-small {
        width: 50px;
        height: 50px;
        background: #e9ecef;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .photo-wrapper img, .photo-wrapper-small img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .photo-wrapper .fa-image, .photo-wrapper-small .fa-image {
        font-size: 36px;
        color: #adb5bd;
    }

    .photo-wrapper-small .fa-image {
        font-size: 20px;
    }

    .item-info {
        flex-grow: 1;
    }

    .room-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #212529;
    }

    .full-set-item-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #0d6efd;
    }

    .label {
        font-weight: 600;
        color: #495057;
    }

    .item-info div, .full-set-item-info div {
        margin-bottom: 6px;
        font-size: 15px;
    }

    .full-set-item-info div {
        font-size: 13px;
    }

    .barcode-image {
        margin-top: 12px;
    }

    .barcode-image-small {
        margin-top: 8px;
    }

    .barcode-text {
        font-family: monospace;
        font-size: 13px;
        color: #6c757d;
        margin-top: 5px;
    }

    .barcode-text-small {
        font-family: monospace;
        font-size: 11px;
        color: #6c757d;
        margin-top: 3px;
    }

    .status {
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
    }

    .status-small {
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 11px;
    }

    .status.Usable, .status-small.Usable {
        background: #d4edda;
        color: #155724;
    }

    .status.Unusable, .status-small.Unusable {
        background: #f8d7da;
        color: #721c24;
    }

    .status.Borrowed, .status-small.Borrowed {
        background: #fff3cd;
        color: #856404;
    }

    .not-found {
        color: #dc3545;
        text-align: center;
        font-size: 18px;
        margin-top: 25px;
        font-weight: 500;
    }

    .back-link {
        display: block;
        margin-top: 30px;
        text-align: center;
        text-decoration: none;
        color: #0d6efd;
        font-weight: 500;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .set-summary {
        background: rgba(255, 255, 255, 0.8);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border-left: 4px solid #0d6efd;
    }

    .set-summary h4 {
        margin: 0 0 10px 0;
        color: #0d6efd;
        font-size: 16px;
    }

    .set-meta {
        display: flex;
        gap: 20px;
        font-size: 14px;
        color: #6c757d;
    }

    .component-count {
        background: #0d6efd;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .full-set-item-flex {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    /* Main content area styling to work with the sidebar */
    .main-content {
        flex: 1;
        padding: 30px;
        background: #f4f6f9;
        overflow-y: auto;
    }

    /* Scanner status indicator */
    .scanner-status {
        text-align: center;
        margin-bottom: 15px;
        padding: 8px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        display: none;
    }

    .scanner-status.active {
        display: block;
        background: #d4edda;
        color: #155724;
    }

    .scanner-status.scanning {
        display: block;
        background: #fff3cd;
        color: #856404;
    }

/* Enhanced Camera Scanner Styles */
.camera-section {
    margin-bottom: 20px;
    padding: 20px;
    border: 2px dashed #ccc;
    border-radius: 10px;
    text-align: center;
    background: #f9f9f9;
    transition: all 0.3s ease;
}

.camera-section.active {
    border-color: #007bff;
    background: #f0f8ff;
}

/* Camera Overlay for Focus Mode */
.camera-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.camera-overlay.active {
    display: flex;
}

.camera-container {
    position: relative;
    width: 90%;
    max-width: 600px;
    height: 450px;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

#camera-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
}

/* Scanning Line Animation */
.scanning-line {
    position: absolute;
    top: 0;
    left: 10%;
    right: 10%;
    height: 3px;
    background: linear-gradient(90deg, transparent, #ff6b6b, #ff6b6b, transparent);
    box-shadow: 0 0 20px #ff6b6b;
    animation: scanningAnimation 2s linear infinite;
    z-index: 2;
    opacity: 0.8;
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

.scanning-line.readable {
    background: linear-gradient(90deg, transparent, #4ecdc4, #4ecdc4, transparent);
    box-shadow: 0 0 20px #4ecdc4;
}

.scanning-line.success {
    background: linear-gradient(90deg, transparent, #28a745, #28a745, transparent);
    box-shadow: 0 0 30px #28a745;
    animation: successPulse 0.5s ease-in-out;
}

@keyframes scanningAnimation {
    0% { 
        top: 10%; 
        opacity: 0;
    }
    10% { 
        opacity: 0.8;
    }
    90% { 
        opacity: 0.8;
    }
    100% { 
        top: 90%; 
        opacity: 0;
    }
}

@keyframes successPulse {
    0% { 
        transform: scale(1);
        opacity: 0.8;
    }
    50% { 
        transform: scale(1.2);
        opacity: 1;
    }
    100% { 
        transform: scale(1);
        opacity: 0.8;
    }
}

/* Scanner Frame Overlay */
.scanner-frame {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    height: 200px;
    border: 2px solid rgba(255, 255, 255, 0.6);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    z-index: 1;
    pointer-events: none;
}

.scanner-frame::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    border: 2px solid transparent;
    border-radius: 15px;
    background: linear-gradient(45deg, #007bff, #00d4ff);
    background-clip: padding-box;
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
}

/* Camera Controls in Overlay */
.camera-overlay-controls {
    position: absolute;
    bottom: 50px;
    display: flex;
    gap: 20px;
    align-items: center;
}

.camera-overlay-btn {
    padding: 15px 25px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.camera-overlay-btn.stop {
    background: #dc3545;
    color: white;
}

.camera-overlay-btn.stop:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
}

.camera-overlay-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Camera Status in Overlay */
.camera-overlay-status {
    position: absolute;
    top: 30px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.camera-overlay-status.scanning {
    background: rgba(255, 193, 7, 0.9);
    color: #000;
}

.camera-overlay-status.readable {
    background: rgba(78, 205, 196, 0.9);
    color: #000;
}

.camera-overlay-status.success {
    background: rgba(40, 167, 69, 0.9);
    color: white;
    animation: statusSuccess 0.5s ease-in-out;
}

@keyframes statusSuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Instructions */
.camera-instructions {
    position: absolute;
    bottom: 120px;
    color: rgba(255, 255, 255, 0.9);
    text-align: center;
    font-size: 14px;
    max-width: 300px;
}

/* Original Camera Controls (Hidden when overlay is active) */
.camera-controls {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 10px 0;
    transition: opacity 0.3s ease;
}

.camera-controls.hidden {
    opacity: 0;
    pointer-events: none;
}

.camera-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.camera-btn.primary {
    background: #007bff;
    color: white;
}

.camera-btn.primary:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.camera-btn.secondary {
    background: #6c757d;
    color: white;
}

.camera-btn.secondary:hover {
    background: #545b62;
}

.camera-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.camera-status {
    margin: 10px 0;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 6px;
    text-align: center;
    transition: all 0.3s ease;
}

.camera-status.scanning {
    color: #007bff;
    background: rgba(0, 123, 255, 0.1);
}

.camera-status.success {
    color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.camera-status.error {
    color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.camera-status.readable {
    color: #17a2b8;
    background: rgba(23, 162, 184, 0.1);
}

.scan-result-camera {
    margin-top: 20px;
    padding: 15px;
    background: #e8f5e8;
    border: 1px solid #28a745;
    border-radius: 5px;
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.or-divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.or-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #ccc;
}

.or-divider span {
    background: white;
    padding: 0 15px;
    color: #666;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .camera-container {
        width: 95%;
        height: 300px;
    }
    
    .camera-overlay-controls {
        bottom: 30px;
    }
    
    .camera-instructions {
        bottom: 80px;
        font-size: 12px;
    }
    
    .scanner-frame {
        width: 90%;
        height: 150px;
    }
}
</style>
@endpush

@push('scripts')
<!-- QuaggaJS for barcode scanning -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.querySelector('input[name="barcode"]');
    const form = document.querySelector('form');
    const scannerStatus = document.querySelector('.scanner-status');
    
    // Enhanced Camera scanning variables
    const cameraVideo = document.getElementById('camera-video');
    const startCameraBtn = document.getElementById('start-camera');
    const stopCameraBtn = document.getElementById('stop-camera');
    const cameraStatus = document.querySelector('.camera-status');
    const cameraResultDiv = document.querySelector('.scan-result-camera');
    const cameraOverlay = document.querySelector('.camera-overlay');
    const cameraOverlayBtn = document.getElementById('overlay-stop-camera');
    const cameraOverlayStatus = document.querySelector('.camera-overlay-status');
    const scanningLine = document.querySelector('.scanning-line');
    const cameraSection = document.querySelector('.camera-section');
    const cameraControls = document.querySelector('.camera-controls');
    
    let inputStartTime = null;
    let inputBuffer = '';
    let inputTimeout = null;
    let cameraScanning = false;
    let scanningInterval = null;
    let detectionTimeout = null;
    let lastDetectionTime = 0;
    let detectionCount = 0;
    let currentStream = null;
    
    // Configuration for scanner detection
    const SCANNER_CONFIG = {
        minLength: 3,           
        maxLength: 50,          
        maxInputTime: 100,      
        submitDelay: 50,        
        enterKeyDetection: true 
    };
    
    // Configuration for barcode detection
    const BARCODE_CONFIG = {
        detectionThreshold: 3,      // Number of consistent detections needed
        detectionTimeout: 1000,     // Time window for detections
        processingDelay: 500        // Delay before processing barcode
    };
    
    // Function to detect if input is from scanner
    function isScannerInput(inputTime, inputLength) {
        return inputTime <= SCANNER_CONFIG.maxInputTime && 
               inputLength >= SCANNER_CONFIG.minLength && 
               inputLength <= SCANNER_CONFIG.maxLength;
    }
    
    // Function to show scanner status
    function showScannerStatus(message, className) {
        if (scannerStatus) {
            scannerStatus.textContent = message;
            scannerStatus.className = 'scanner-status ' + className;
            setTimeout(() => {
                scannerStatus.className = 'scanner-status';
            }, 2000);
        }
    }

    // Function to show camera status
    function showCameraStatus(message, className) {
        if (cameraStatus) {
            cameraStatus.textContent = message;
            cameraStatus.className = 'camera-status ' + className;
        }
    }

    // Function to show camera overlay status
    function showCameraOverlayStatus(message, className) {
        if (cameraOverlayStatus) {
            cameraOverlayStatus.textContent = message;
            cameraOverlayStatus.className = 'camera-overlay-status ' + className;
        }
    }

    // Function to update scanning line
    function updateScanningLine(status) {
        if (scanningLine) {
            scanningLine.className = 'scanning-line';
            if (status) {
                scanningLine.classList.add(status);
            }
        }
    }
    
    // Function to submit form with scanner styling
    function submitWithScannerEffect() {
        barcodeInput.classList.add('scanner-input');
        showScannerStatus('Barcode scanned successfully!', 'active');
        
        setTimeout(() => {
            form.submit();
        }, SCANNER_CONFIG.submitDelay);
    }

    // Function to process barcode from camera
    function processCameraBarcode(barcode) {
        showCameraOverlayStatus('✓ Barcode Captured! Processing...', 'success');
        updateScanningLine('success');
        
        // Stop camera after successful scan
        setTimeout(() => {
            stopCamera();
        }, 1000);
        
        // Send AJAX request to API endpoint
        fetch('{{ route("roomitem.scan.api-search") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCameraStatus('Barcode found! Displaying results...', 'success');
                displayCameraResults(data.items, data.barcode);
            } else {
                showCameraStatus('No item found for this barcode', 'error');
                cameraResultDiv.innerHTML = `<div class="not-found">❌ No item found for barcode: <strong>${data.barcode}</strong></div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCameraStatus('Error processing barcode', 'error');
        });
    }

    // Function to display camera scan results
    function displayCameraResults(items, barcode) {
        let html = `<h3>🔍 Camera Scan Result for: <code>${barcode}</code></h3>`;
        
        items.forEach(item => {
            // Extract PC number from barcode or serial number
            let pcNumber = null;
            const barcodeMatch = item.barcode.match(/(\d{3})$/);
            const serialMatch = item.serial_number.match(/(\d{3})$/);
            
            if (barcodeMatch) {
                pcNumber = parseInt(barcodeMatch[1]);
            } else if (serialMatch) {
                pcNumber = parseInt(serialMatch[1]);
            }
            
            const pcDisplay = pcNumber ? `<span style="color: #0d6efd; font-weight: 600; margin-left: 10px;">PC${pcNumber.toString().padStart(3, '0')}</span>` : '';
            
            html += `
                <div class="item-box" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <div style="display: flex; gap: 15px;">
                        <div style="min-width: 80px;">
                            ${item.has_photo ? 
                                `<img src="/room-items/${item.id}/photo" alt="Item Photo" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">` :
                                `<div style="width: 80px; height: 80px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;"><i class="fas fa-image"></i></div>`
                            }
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: bold; font-size: 16px; color: #007bff; margin-bottom: 8px;">
                                ${item.room_title}${pcDisplay}
                            </div>
                            <div style="display: grid; grid-template-columns: auto 1fr; gap: 5px 15px; font-size: 14px;">
                                <span style="font-weight: 600;">Category:</span> <span>${item.device_category}</span>
                                <span style="font-weight: 600;">Type:</span> <span>${item.device_type}</span>
                                <span style="font-weight: 600;">Brand:</span> <span>${item.brand}</span>
                                <span style="font-weight: 600;">Model:</span> <span>${item.model}</span>
                                <span style="font-weight: 600;">Serial:</span> <span>${item.serial_number}</span>
                                <span style="font-weight: 600;">Description:</span> <span>${item.description || 'N/A'}</span>
                                <span style="font-weight: 600;">Status:</span> <span class="status ${item.status}" style="padding: 2px 8px; border-radius: 3px; font-size: 12px;">${item.status}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        cameraResultDiv.innerHTML = html;
    }
    
    // Enhanced camera scanning functions
    function startCamera() {
        showCameraStatus('Starting camera...', 'scanning');
        startCameraBtn.disabled = true;
        cameraSection.classList.add('active');
        cameraControls.classList.add('hidden');
        
        // Show overlay
        cameraOverlay.classList.add('active');
        showCameraOverlayStatus('📷 Starting Camera...', 'scanning');
        updateScanningLine();
        
        // Request camera access
        navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: "environment",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        }).then(function(stream) {
            currentStream = stream;
            cameraVideo.srcObject = stream;
            
            // Initialize Quagga
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: cameraVideo,
                    constraints: {
                        width: { min: 640, ideal: 1280 },
                        height: { min: 480, ideal: 720 },
                        facingMode: "environment"
                    }
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        "ean_reader",
                        "ean_8_reader",
                        "code_39_reader",
                        "code_93_reader",
                        "codabar_reader"
                    ]
                },
                locate: true,
                locator: {
                    patchSize: "medium",
                    halfSample: true
                }
            }, function(err) {
                if (err) {
                    console.log('Quagga initialization error:', err);
                    showCameraOverlayStatus('❌ Error: ' + err.message, 'error');
                    showCameraStatus('Error starting camera: ' + err.message, 'error');
                    stopCamera();
                    return;
                }
                
                Quagga.start();
                cameraScanning = true;
                showCameraOverlayStatus('📱 Point camera at barcode', 'scanning');
                showCameraStatus('Camera ready - point at barcode', 'scanning');
                cameraOverlayBtn.disabled = false;
                
                // Start scanning line animation and detection monitoring
                startScanningMonitor();
            });
        }).catch(function(err) {
            console.log('Camera access error:', err);
            showCameraOverlayStatus('❌ Camera access denied', 'error');
            showCameraStatus('Camera access denied: ' + err.message, 'error');
            stopCamera();
        });
    }
    
    function stopCamera() {
        if (cameraScanning) {
            Quagga.stop();
            cameraScanning = false;
            
            // Stop current stream
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            
            // Hide overlay
            cameraOverlay.classList.remove('active');
            cameraSection.classList.remove('active');
            cameraControls.classList.remove('hidden');
            
            // Reset UI
            showCameraOverlayStatus('Camera stopped', '');
            showCameraStatus('Camera stopped', '');
            startCameraBtn.disabled = false;
            cameraOverlayBtn.disabled = true;
            updateScanningLine();
            
            // Clear monitoring
            stopScanningMonitor();
            
            // Reset detection variables
            lastDetectionTime = 0;
            detectionCount = 0;
        }
    }
    
    // Function to start scanning monitor
    function startScanningMonitor() {
        scanningInterval = setInterval(() => {
            if (cameraScanning) {
                const currentTime = Date.now();
                
                // Check if we have recent detections
                if (currentTime - lastDetectionTime < 500) {
                    updateScanningLine('readable');
                    showCameraOverlayStatus('🎯 Barcode detected - hold steady', 'readable');
                } else {
                    updateScanningLine();
                    showCameraOverlayStatus('📱 Point camera at barcode', 'scanning');
                }
            }
        }, 100);
    }
    
    // Function to stop scanning monitor
    function stopScanningMonitor() {
        if (scanningInterval) {
            clearInterval(scanningInterval);
            scanningInterval = null;
        }
        if (detectionTimeout) {
            clearTimeout(detectionTimeout);
            detectionTimeout = null;
        }
    }
    
    // Enhanced Quagga barcode detection with consistency checking
    Quagga.onDetected(function(data) {
        if (!cameraScanning) return;
        
        const barcode = data.codeResult.code;
        const currentTime = Date.now();
        
        // Update detection tracking
        lastDetectionTime = currentTime;
        
        // Simple barcode validation
        if (!barcode || barcode.length < 3) {
            return;
        }
        
        console.log('Barcode detected:', barcode);
        
        // Clear existing timeout
        if (detectionTimeout) {
            clearTimeout(detectionTimeout);
        }
        
        // Set timeout for processing
        detectionTimeout = setTimeout(() => {
            if (cameraScanning) {
                detectionCount++;
                
                // Process the barcode immediately for better user experience
                if (detectionCount >= 1) {
                    updateScanningLine('success');
                    showCameraOverlayStatus('✅ Barcode captured successfully!', 'success');
                    processCameraBarcode(barcode);
                }
            }
        }, 200); // Short delay to ensure stability
    });
    
    // Quagga processing event for real-time feedback
    Quagga.onProcessed(function(result) {
        if (!cameraScanning) return;
        
        const drawingCtx = Quagga.canvas.ctx.overlay;
        const drawingCanvas = Quagga.canvas.dom.overlay;
        
        if (result) {
            // Clear previous drawings
            drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
            
            if (result.boxes) {
                drawingCtx.strokeStyle = "rgba(0, 255, 0, 0.5)";
                drawingCtx.lineWidth = 2;
                
                result.boxes.filter(function(box) {
                    return box !== result.box;
                }).forEach(function(box) {
                    Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "green", lineWidth: 2});
                });
            }
            
            if (result.box) {
                drawingCtx.strokeStyle = "rgba(0, 255, 0, 0.8)";
                drawingCtx.lineWidth = 3;
                Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "blue", lineWidth: 2});
            }
            
            if (result.codeResult && result.codeResult.code) {
                lastDetectionTime = Date.now();
            }
        }
    });
    
    // Camera button events
    startCameraBtn.addEventListener('click', startCamera);
    cameraOverlayBtn.addEventListener('click', stopCamera);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && cameraScanning) {
            stopCamera();
        }
    });
    
    // Original input handling code (unchanged)
    barcodeInput.addEventListener('input', function(e) {
        const currentTime = Date.now();
        
        if (this.value.length === 1) {
            inputStartTime = currentTime;
            inputBuffer = this.value;
        } else {
            inputBuffer = this.value;
        }
        
        if (inputTimeout) {
            clearTimeout(inputTimeout);
        }
        
        inputTimeout = setTimeout(() => {
            if (inputStartTime && inputBuffer.length >= SCANNER_CONFIG.minLength) {
                const inputTime = currentTime - inputStartTime;
                
                if (isScannerInput(inputTime, inputBuffer.length)) {
                    submitWithScannerEffect();
                }
            }
        }, SCANNER_CONFIG.maxInputTime);
    });
    
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && SCANNER_CONFIG.enterKeyDetection) {
            e.preventDefault();
            
            const currentTime = Date.now();
            const inputValue = this.value.trim();
            
            if (inputValue.length >= SCANNER_CONFIG.minLength) {
                if (inputStartTime) {
                    const inputTime = currentTime - inputStartTime;
                    if (isScannerInput(inputTime, inputValue.length)) {
                        submitWithScannerEffect();
                        return;
                    }
                }
                
                showScannerStatus('Processing barcode...', 'scanning');
                setTimeout(() => {
                    form.submit();
                }, SCANNER_CONFIG.submitDelay);
            }
        }
    });
    
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key.length === 1) {
            const currentTime = Date.now();
            if (inputStartTime && currentTime - inputStartTime > 1000) {
                inputStartTime = currentTime;
            }
        }
    });
    
    barcodeInput.addEventListener('paste', function(e) {
        setTimeout(() => {
            const pastedValue = this.value.trim();
            if (pastedValue.length >= SCANNER_CONFIG.minLength && 
                pastedValue.length <= SCANNER_CONFIG.maxLength) {
                showScannerStatus('Barcode pasted - processing...', 'scanning');
                setTimeout(() => {
                    form.submit();
                }, SCANNER_CONFIG.submitDelay);
            }
        }, 10);
    });
    
    barcodeInput.addEventListener('blur', function() {
        setTimeout(() => {
            this.classList.remove('scanner-input');
        }, 1000);
    });
    
    // Auto-focus on input when page loads
    barcodeInput.focus();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (cameraScanning) {
            stopCamera();
        }
    });
});
</script>
@endpush

@section('title', 'Scan Barcode')

@section('content')
<div class="main-content">
    <h1 class="page-title"><i class="fas fa-barcode"></i> Scan Barcode</h1>

    <!-- CAMERA OVERLAY FOR FOCUSED SCANNING -->
    <div class="camera-overlay">
        <div class="camera-overlay-status">
            📷 Starting Camera...
        </div>
        
        <div class="camera-container">
            <video id="camera-video" autoplay playsinline></video>
            <div class="scanner-frame"></div>
            <div class="scanning-line"></div>
        </div>
        
        <div class="camera-instructions">
            <i class="fas fa-info-circle"></i>
            Position the barcode within the frame and hold steady
        </div>
        
        <div class="camera-overlay-controls">
            <button id="overlay-stop-camera" class="camera-overlay-btn stop" disabled>
                <i class="fas fa-times"></i> Stop Camera
            </button>
        </div>
    </div>

    <div class="scan-container">
        <!-- ENHANCED CAMERA SCANNING SECTION -->
        <div class="camera-section">
            <h3><i class="fas fa-camera"></i> Camera Barcode Scanner</h3>
            <div class="camera-status">Click "Start Camera" to begin scanning</div>
            
            <div class="camera-controls">
                <button id="start-camera" class="camera-btn primary">
                    <i class="fas fa-camera"></i> Start Camera
                </button>
            </div>
            
            <div class="scan-result-camera"></div>
        </div>

        <!-- OR DIVIDER -->
        <div class="or-divider">
            <span>OR</span>
        </div>

        <!-- ORIGINAL MANUAL/SCANNER INPUT SECTION -->
        <div class="scanner-status"></div>
        
        <form method="POST" action="{{ route('roomitem.scan.search') }}">
            @csrf
            <div class="form-group">
                <input type="text" name="barcode" placeholder="Enter or scan barcode manually..." value="{{ old('barcode', $barcode ?? '') }}" required autofocus>
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>

        @if(isset($scanned) && $scanned && isset($items) && count($items) > 0)
            <div class="result">
                <h3>🔍 Result for Barcode: <code>{{ $barcode }}</code></h3>

                @php
                    // Group items by set ID if they belong to a full set
                    $fullSets = [];
                    $individualItems = [];
                    
                    foreach($items as $item) {
                        // Check if item is part of a full set by looking for set pattern in serial number
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
                            PC{{ str_pad($setId, 3, '0', STR_PAD_LEFT) }}
                            <span class="component-count">{{ count($setItems) }} Components</span>
                        </div>
                        <div class="full-set-items">
                            <div class="set-summary">
                                <h4>PC Information</h4>
                                <div class="set-meta">
                                    <div><strong>PC#:</strong> PC{{ str_pad($setId, 3, '0', STR_PAD_LEFT) }}</div>
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

                {{-- Display Individual Items --}}
                @foreach($individualItems as $item)
                    @php
                        // Try to extract PC number from barcode or serial number for individual items
                        $pcNumber = null;
                        if (preg_match('/(\d{3})$/', $item->barcode, $matches)) {
                            $pcNumber = intval($matches[1]);
                        } elseif (preg_match('/(\d{3})$/', $item->serial_number, $matches)) {
                            $pcNumber = intval($matches[1]);
                        }
                    @endphp
                    <div class="item-box">
                        <div class="photo-wrapper">
                            @if($item->photo)
                                <img src="{{ route('room-item.photo', $item->id) }}" alt="Item Photo">
                            @else
                                <i class="fas fa-image"></i>
                            @endif
                        </div>
                        <div class="item-info">
                            <div class="room-title">
                                {{ $item->room_title }}
                                @if($pcNumber)
                                    <span style="color: #0d6efd; font-weight: 600; margin-left: 10px;">PC{{ str_pad($pcNumber, 3, '0', STR_PAD_LEFT) }}</span>
                                @endif
                            </div>

                            <div><span class="label">Category:</span> {{ $item->device_category }}</div>
                            <div><span class="label">Type:</span> {{ $item->device_type ?? 'Unspecified' }}</div>
                            <div><span class="label">Brand:</span> {{ $item->brand ?? 'N/A' }}</div>
                            <div><span class="label">Model:</span> {{ $item->model ?? 'N/A' }}</div>
                            <div><span class="label">Serial Number:</span> {{ $item->serial_number }}</div>
                            <div><span class="label">Description:</span> {{ $item->description }}</div>
                            <div>
                                <span class="label">Status:</span>
                                <span class="status {{ $item->status }}">{{ $item->status }}</span>
                            </div>
                            <div class="barcode-image">
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->barcode, 'C128', 2, 60) }}" alt="Barcode">
                                <div class="barcode-text">{{ $item->barcode }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('roomitem.scan.index') }}" class="exit-button">
                <i class="fas fa-check"></i> Okay
            </a>

        @elseif(isset($notFound) && $notFound)
            <div class="not-found">
                ❌ No item found for barcode: <strong>{{ $barcode }}</strong>
            </div>
        @endif
        
    </div>
</div>
@endsection