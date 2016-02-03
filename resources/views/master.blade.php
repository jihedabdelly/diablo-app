<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Diablo Rankings</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="/css/index.css">
    <link rel="icon" type="image/png"
          href="/img/diablorankings.png" />
</head>
<body>
    @yield('content')

    <div id="app"></div>

    <script>
        var base_url = '{!! url('/') !!}'
    </script>
    @yield('js')

    @if (env('APP_DEBUG'))
        <script>document.write('<script src="http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1"></' + 'script>')</script>
    @endif
</body>
</html>