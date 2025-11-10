<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
<h2>Register</h2>
<form method="POST" action="/register" enctype="multipart/form-data">
    @csrf
    <label>Full Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="password_confirmation" required><br><br>

    <label>Upload Photo:</label><br>
    <input type="file" name="photo" accept="image/*" required><br><br>

    <button type="submit">Register</button>
</form>
<a href="/login">Already have an account? Login</a>
</body>
</html>
