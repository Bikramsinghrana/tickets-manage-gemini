<!DOCTYPE html>
<html>
<head>
    <title>403 - Forbidden</title>
</head>
<body style="text-align:center;margin-top:60px;">
    <h1>403 - Forbidden</h1>

    <p>
        {{ $exception->getMessage() ?: 'You do not have permission to access this page.' }}
    </p>

    <a href="{{ url('/') }}">Go Home</a>
</body>
</html>
