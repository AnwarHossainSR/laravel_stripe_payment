<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
</head>

<body class="antialiased">
    <div style="display: flex; gap: 3rem;">
            <h5>Standard Monthly Plan</h5>
            <p>$99</p>
    </div>
    <p>
    <form action="{{route('checkout.subscription')}}" method="POST">
        @csrf
        <button>Checkout</button>
    </form>
    </p>
</body>

</html>
