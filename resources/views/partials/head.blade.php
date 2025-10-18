<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('images/favicon.png') }}" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{--og content--}}
<meta property="og:title" content="{{ $title ?? config('app.name') }}" />
<meta property="og:description" content="{{ 'BalanceBot - Your Personal Finance Assistant' }}" />
<meta property="og:image" content="{{ asset('images/thumbnail.png') }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
