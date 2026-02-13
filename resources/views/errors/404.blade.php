<!DOCTYPE html>
<html>

<head>
    <title>Service Unavailable</title>
</head>

<body style="text-align:center;margin-top:60px;">
    <h1>503 - Service Unavailable</h1>
    <p>
        {{ $exception->getMessage() ?: 'The service is temporarily unavailable. Please try again later.' }}
    </p>
    <a href="{{ url('/') }}">Go Home</a>
</body>

</html>