<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login Lockout Alert</title>
</head>
<body>
    <p><strong>Alert:</strong> A login lockout occurred.</p>
    <ul>
        <li><strong>IP:</strong> {{ $ip ?? 'unknown' }}</li>
        <li><strong>Latitude:</strong> {{ $latitude ?? 'unknown' }}</li>
        <li><strong>Longitude:</strong> {{ $longitude ?? 'unknown' }}</li>
    </ul>
    <p>If a selfie was provided, it is attached to this email.</p>
</body>
</html>