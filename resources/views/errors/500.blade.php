<!DOCTYPE html>
<html>
<head>
    <title>500 - Server Error</title>
</head>
<body style="text-align:center;margin-top:60px;">
    <h1>500 - Internal Server Error</h1>

    <p>
        {{ $exception->getMessage() ?: 'Something went wrong on our side. Please try again later.' }}
    </p>

    <a href="{{ url('/') }}">Go Home</a>
</body>
</html>
