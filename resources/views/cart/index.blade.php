@extends('layouts.app')

@section('content')
    <h2 style="margin-bottom: 15px;">Sepetim</h2>
    
    <table class="cart-table">
        <tr>
            <th>Ürün</th>
            <th>Adet</th>
            <th>Fiyat</th>
            <th>Toplam</th>
        </tr>

        @forelse ($items as $item)
            <tr>
                <td>
                    <div class="cart-item">
                        <img src="{{ $item->image_url }}">
                        <div>{{ $item->name }}</div>
                    </div>
                </td>

                <td style="white-space:nowrap;">

                    <form action="{{ route('cart.decrement', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="qty-btn">−</button>
                    </form>

                    <span class="qty">{{ $item->qty }}</span>

                    <form action="{{ route('cart.increment', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="qty-btn">+</button>
                    </form>

                </td>

                <td>
                    <span class="price">{{ number_format($item->price, 2) }} ₺</span>
                </td>

                <td>
                    <span class="subtotal">
                        {{ number_format($item->qty * $item->price, 2) }} ₺
                    </span>

                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="remove-btn">✖</button>
                    </form>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="4" style="text-align:center; padding: 20px;">
                    Sepetiniz boş.
                </td>
            </tr>
        @endforelse
    </table>


    <div class="total-box">
        Genel Toplam: <strong>{{ number_format($total, 2) }} ₺</strong>
    </div>
@endsection
