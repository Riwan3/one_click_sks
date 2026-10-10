<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title inertia>{{ config('app.name', 'OneClick') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#7B112C">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="OneClick">
    <link rel="icon" type="image/png" href="/images/logo-oneclick.png">
    <link rel="apple-touch-icon" href="/images/title.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js', { scope: '/' }).catch(function (e) {
                    console.debug('SW reg fail:', e);
                });
            });
        }
    </script>
</body>
</html>
