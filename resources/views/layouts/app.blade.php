<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Hyper Store' }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{asset('favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}"/>
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('apple-touch-icon.png')}}"/>
</head>

<body>

    <header>
        <div class="logo">
            <a href="/" style="text-decoration:none; color:black;">HyperStore</a>
        </div>

        <div class="cart">
            <a href="{{ route('cart.show') }}">🛒</a>

            @php
                $cartCount = session('cart_session_id')
                    ? \App\Models\Cart::where('session_id', session('cart_session_id'))->first()?->items()->count()
                    : 0;
            @endphp

            @if ($cartCount > 0)
                <span class="badge">{{ $cartCount }}</span>
            @endif
        </div>
    </header>

    <div class="container">
        @yield('content')
    </div>

    <footer>
        <p>HyperStore © {{ date('Y') }}</p>
        <p>Developed by <a href="https://www.linkedin.com/in/emirhan-ilica/">Emirhan ILICA </a>with ❤️</p>
    </footer>

</body>

</html>
