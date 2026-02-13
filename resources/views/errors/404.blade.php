<!DOCTYPE html>
<html>
<head>
    <title>404 - Not Found</title>
</head>
<body style="text-align:center;margin-top:60px;">
    <h1>404 - Not Found</h1>

    <p>
        {{-- if you want to show the original message, you can set APP_DEBUG=true in .env file --}}
        {{-- {{ $exception->getMessage() ?: 'The page you are looking for could not be found.' }} --}}
        <span style="color:gray;">The page you are looking for could not be found.</span>
    </p>

    {{-- <a href="{{ url('/') }}">Go Home</a> --}}
    <a href="{{ route('home') }}">Go Home</a>
</body>
</html>
