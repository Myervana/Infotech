@extends('layouts.app')

@section('title', 'Share Token')

@push('styles')
<style>
    .main-content { 
        flex: 1;
        padding: 40px 60px; 
        background: #f8f9fa; 
        min-height: 100vh; 
        overflow-y: auto;
    }
    .page-header { margin-bottom: 24px; text-align: center; }
    .page-title { font-size: 28px; font-weight: 600; color: #2c3e50; margin: 0; display: inline-flex; align-items: center; gap: 10px; justify-content: center; }
    .card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 16px; }
    .row { display: flex; gap: 12px; align-items: center; }
    .row > * { flex: 1; }
    .btn { padding: 10px 16px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
    .btn-primary { background: #667eea; color: #fff; }
    .btn-danger { background: #dc3545; color: #fff; }
    .btn-secondary { background: #6c757d; color: #fff; }
    .muted { color: #6c757d; font-size: 13px; }
    .pill { background:#eef2ff; color:#4f46e5; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; }
    input[type=text] { width: 100%; padding: 10px 12px; border:1px solid #e5e7eb; border-radius:10px; }
    ul { list-style: none; padding: 0; margin: 0; }
    li { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f3f4; }
    .success-alert {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 15px 20px;
        margin-bottom: 25px;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Share Token</h1>
            <div class="muted">Share read-only access to your data with up to 5 users for 3 hours.</div>
        </div>

        @if(session('success'))
            <div class="success-alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px 20px; margin-bottom: 25px; border-radius: 8px; font-weight: 500;">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px 20px; margin-bottom: 25px; border-radius: 8px; font-weight: 500;">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="row">
                <button id="btn-generate" class="btn btn-primary">Share Token</button>
                <input id="token-output" type="text" placeholder="Generated token appears here" readonly>
                <button id="btn-toggle" class="btn btn-secondary">Show</button>
            </div>
            <div class="muted" style="margin-top:8px;">Token valid for 3 hours • Max 5 users</div>
        </div>

        <div class="card">
            <div class="row">
                <button id="btn-generate-device-token" class="btn btn-primary">Generate Device Share Token</button>
                <input id="device-token-output" type="text" placeholder="Device share token appears here" readonly>
                <button id="btn-toggle-device" class="btn btn-secondary">Show</button>
            </div>
            <div class="muted" style="margin-top:8px;">Device share token valid for 24 hours • Allows device binding</div>
        </div>

        <div class="card">
            <div class="row">
                <input id="token-input" type="text" placeholder="Paste token here">
                <button id="btn-paste" class="btn btn-secondary">Paste Token</button>
            </div>
            <div class="muted" style="margin-top:8px;">If you joined via a token, you cannot generate your own.</div>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <div style="font-weight:700;">Shared Users</div>
                <span id="uses-pill" class="pill">0 / 5 used</span>
            </div>
            <ul id="shared-list"></ul>
        </div>

        @if(isset($deviceBindings) && $deviceBindings->count() > 0)
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <div style="font-weight:700;">Devices with Access</div>
                <span class="pill">{{ $deviceBindings->count() }} device(s)</span>
            </div>
            <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach($deviceBindings as $binding)
                <li style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f3f4; @if(!$binding->is_active) opacity: 0.6; @endif">
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">
                            {{ $binding->device_name ?? 'Unknown Device' }}
                            @if(!$binding->is_active)
                                <span style="color: #dc3545; font-size: 11px; margin-left: 8px;">(Removed)</span>
                            @endif
                        </div>
                        <div style="font-size: 12px; color: #6c757d; margin-top: 4px;">
                            @if($binding->is_primary)
                                <span style="color: #28a745; font-weight: 600;">Primary Device</span> • 
                            @endif
                            {{ $binding->ip_address ?? 'N/A' }} • 
                            Last accessed: {{ $binding->last_accessed_at ? $binding->last_accessed_at->format('M d, Y H:i') : 'Never' }}
                        </div>
                        @if($binding->device_share_token)
                        <div style="font-size: 11px; color: #667eea; margin-top: 6px; font-family: monospace; background: #f0f4ff; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                            <strong>Share Token:</strong> <span id="token-{{ $binding->id }}">{{ substr($binding->device_share_token, 0, 20) }}...</span>
                            <button onclick="toggleToken({{ $binding->id }}, {{ json_encode($binding->device_share_token) }})" style="margin-left: 8px; background: #667eea; color: white; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-size: 10px;">Show</button>
                        </div>
                        @endif
                    </div>
                    @if(!$binding->is_primary && $binding->is_active)
                    <div>
                        <button onclick="removeDevice({{ $binding->id }})" class="btn btn-danger" style="font-size: 12px; padding: 6px 12px;">Remove</button>
                    </div>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <script>
        let TOKEN_HIDDEN = true;
        let TOKEN_PLAIN = '';

        async function fetchState() {
            const res = await fetch('/share/state', { credentials: 'same-origin', headers: { 'Accept': 'application/json' }});
            const data = await res.json();
            const output = document.getElementById('token-output');
            const usesPill = document.getElementById('uses-pill');
            const list = document.getElementById('shared-list');
            const genBtn = document.getElementById('btn-generate');
            const toggleBtn = document.getElementById('btn-toggle');

            list.innerHTML = '';

            if (data.activeToken) {
                TOKEN_PLAIN = data.activeToken.token;
                output.value = TOKEN_HIDDEN && TOKEN_PLAIN ? '*' : TOKEN_PLAIN;
                usesPill.textContent = (data.activeToken.uses || 0) + ' / ' + (data.activeToken.max_uses || 5) + ' used';
                toggleBtn.disabled = !TOKEN_PLAIN;
                toggleBtn.textContent = TOKEN_HIDDEN ? 'Show' : 'Hide';
            } else {
                TOKEN_PLAIN = '';
                output.value = '';
                usesPill.textContent = '0 / 5 used';
                toggleBtn.disabled = true;
                toggleBtn.textContent = 'Show';
            }

            if (data.sharedFrom) {
                genBtn.disabled = true;
                genBtn.title = 'Blocked: you joined via a shared token';
            } else {
                genBtn.disabled = false;
                genBtn.title = '';
            }

            (data.sharedUsers || []).forEach(function(entry){
                const li = document.createElement('li');
                const left = document.createElement('div');
                const right = document.createElement('div');
                left.textContent = (entry.shared_user?.name || 'User') + ' • ' + (entry.shared_user?.email || '');
                const btn = document.createElement('button');
                btn.className = 'btn btn-danger';
                btn.textContent = 'Revoke';
                btn.onclick = async function(){ await revoke(entry.shared_user_id); };
                right.appendChild(btn);
                li.appendChild(left); li.appendChild(right);
                list.appendChild(li);
            });
        }

        async function generate() {
            const res = await fetch('/share/generate', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }});
            if (res.ok) {
                const data = await res.json();
                TOKEN_PLAIN = data.token || '';
                TOKEN_HIDDEN = true;
                document.getElementById('token-output').value = TOKEN_PLAIN ? '*' : '';
                document.getElementById('btn-toggle').disabled = !TOKEN_PLAIN;
                document.getElementById('btn-toggle').textContent = 'Show';
                await Swal.fire({ icon: 'success', title: 'Token Generated', text: TOKEN_PLAIN, confirmButtonText: 'Copy', showCancelButton: true });
                navigator.clipboard?.writeText(data.token).catch(()=>{});
                await fetchState();
            } else {
                const err = await res.json().catch(()=>({message:'Failed'}));
                Swal.fire({ icon: 'error', title: 'Cannot Generate', text: err.message || 'Unknown error' });
            }
        }

        async function paste() {
            const token = document.getElementById('token-input').value.trim();
            if (!token) { return Swal.fire({ icon:'warning', title:'No token', text:'Please paste a token' }); }
            const res = await fetch('/share/paste', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ token }) });
            if (res.ok) {
                Swal.fire({ icon: 'success', title: 'Access Granted' });
                document.getElementById('token-input').value = '';
                await fetchState();
            } else {
                const err = await res.json().catch(()=>({message:'Failed'}));
                Swal.fire({ icon: 'error', title: 'Unable to Join', text: err.message || 'Unknown error' });
            }
        }

        async function revoke(sharedUserId) {
            const ok = await Swal.fire({ title:'Revoke access?', icon:'warning', showCancelButton:true, confirmButtonText:'Revoke' }).then(r=>r.isConfirmed);
            if (!ok) return;
            const res = await fetch('/share/revoke/' + sharedUserId, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }});
            if (res.ok) {
                Swal.fire({ icon:'success', title:'Access Revoked' });
                await fetchState();
            } else {
                const err = await res.json().catch(()=>({message:'Failed'}));
                Swal.fire({ icon:'error', title:'Failed', text: err.message || 'Unknown error' });
            }
        }

        function toggleVisibility() {
            if (!TOKEN_PLAIN) return;
            TOKEN_HIDDEN = !TOKEN_HIDDEN;
            document.getElementById('token-output').value = TOKEN_HIDDEN ? '*' : TOKEN_PLAIN;
            document.getElementById('btn-toggle').textContent = TOKEN_HIDDEN ? 'Show' : 'Hide';
        }

        let DEVICE_TOKEN_HIDDEN = true;
        let DEVICE_TOKEN_PLAIN = '';

        async function generateDeviceToken() {
            const res = await fetch('/device-share-token/generate', { 
                method: 'POST', 
                credentials: 'same-origin', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json' 
                } 
            });
            if (res.ok) {
                const data = await res.json();
                DEVICE_TOKEN_PLAIN = data.token || '';
                DEVICE_TOKEN_HIDDEN = true;
                document.getElementById('device-token-output').value = DEVICE_TOKEN_PLAIN ? '*'.repeat(50) : '';
                document.getElementById('btn-toggle-device').disabled = !DEVICE_TOKEN_PLAIN;
                document.getElementById('btn-toggle-device').textContent = 'Show';
                await Swal.fire({ 
                    icon: 'success', 
                    title: 'Device Share Token Generated', 
                    text: DEVICE_TOKEN_PLAIN, 
                    confirmButtonText: 'Copy', 
                    showCancelButton: true 
                });
                navigator.clipboard?.writeText(DEVICE_TOKEN_PLAIN).catch(()=>{});
            } else {
                const err = await res.json().catch(()=>({message:'Failed'}));
                Swal.fire({ icon: 'error', title: 'Cannot Generate', text: err.message || 'Unknown error' });
            }
        }

        function toggleDeviceVisibility() {
            if (!DEVICE_TOKEN_PLAIN) return;
            DEVICE_TOKEN_HIDDEN = !DEVICE_TOKEN_HIDDEN;
            document.getElementById('device-token-output').value = DEVICE_TOKEN_HIDDEN ? '*'.repeat(50) : DEVICE_TOKEN_PLAIN;
            document.getElementById('btn-toggle-device').textContent = DEVICE_TOKEN_HIDDEN ? 'Show' : 'Hide';
        }

        function toggleToken(bindingId, fullToken) {
            const tokenEl = document.getElementById('token-' + bindingId);
            const btn = event.target;
            if (tokenEl.textContent.includes('...')) {
                tokenEl.textContent = fullToken;
                btn.textContent = 'Hide';
            } else {
                tokenEl.textContent = fullToken.substring(0, 20) + '...';
                btn.textContent = 'Show';
            }
        }

        async function removeDevice(bindingId) {
            const ok = await Swal.fire({ 
                title: 'Remove Device Access?', 
                text: 'This will revoke access for this device. The share token will be kept for reference.',
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonText: 'Remove',
                cancelButtonText: 'Cancel'
            }).then(r => r.isConfirmed);
            
            if (!ok) return;
            
            const res = await fetch('/device-binding/remove/' + bindingId, { 
                method: 'POST', 
                credentials: 'same-origin', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json' 
                } 
            });
            
            if (res.ok) {
                Swal.fire({ icon: 'success', title: 'Device Access Removed' });
                // Reload the page to refresh the device list
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                const err = await res.json().catch(() => ({message: 'Failed'}));
                Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Unknown error' });
            }
        }

        document.getElementById('btn-generate').addEventListener('click', generate);
        document.getElementById('btn-toggle').addEventListener('click', toggleVisibility);
        document.getElementById('btn-paste').addEventListener('click', paste);
        document.getElementById('btn-generate-device-token').addEventListener('click', generateDeviceToken);
        document.getElementById('btn-toggle-device').addEventListener('click', toggleDeviceVisibility);
        document.addEventListener('DOMContentLoaded', fetchState);
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush