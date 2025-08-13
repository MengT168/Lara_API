<!DOCTYPE html>
<html>
<head><title>Redirecting...</title></head>
<body>
    <script>
        const data = {
            token: "{{ $token ?? '' }}",
            user: {!! $user ?? '{}' !!}
        };
        if (data.token && data.user) {
            // This redirects to your Flutter web app's callback page
            // and puts the data in the URL fragment.
           window.location.href = `http://localhost:52942/callback.html#token=${data.token}&user=${encodeURIComponent(JSON.stringify(data.user))}`;
        } else {
            document.body.innerHTML = "<h1>Error: Authentication failed.</h1>";
        }
    </script>
</body>
</html>