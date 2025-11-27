@extends('layouts.app')

@section('content')
    <h2 style="margin-bottom: 10px;">Ürünler</h2>

    <form class="productForm" method="GET" style="">

        <input type="hidden" name="page" value="0">

        {{-- Category Filter --}}
        <select class="categoryOptionSelect" name="categoryID" onchange="this.form.submit()">

            <option value="">Tüm Kategoriler</option>

            @foreach ($categories as $cat)
                <option value="{{ $cat['id'] }}" {{ $selectedCategory == $cat['id'] ? 'selected' : '' }}>
                    {{ $cat['name'] }}
                </option>
            @endforeach
        </select>

        {{-- PageSize Filter --}}
        <select class="productOptionSelect" name="pageSize" onchange="this.form.submit()">
            <option value="10" {{ $pageSize == 10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ $pageSize == 20 ? 'selected' : '' }}>20</option>
            <option value="30" {{ $pageSize == 30 ? 'selected' : '' }}>30</option>
            <option value="50" {{ $pageSize == 50 ? 'selected' : '' }}>50</option>
        </select>

    </form>

    <!-- PRODUCT GRID -->
    <div class="grid">
        @if (count($products) == 0)
            <h1>Ürün bulunamadı.</h1>
        @endif
        @foreach ($products as $p)
            <div class="card">

                <img src="{{ $p['productData']['productMainImage'] ?? '' }}">

                <h4>{{ $p['productName'] }}</h4>

                <p>{{ number_format($p['salePrice'], 2) }} ₺</p>

                <form method="POST" action="{{ route('cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $p['productID'] }}">
                    <input type="hidden" name="name" value="{{ $p['productName'] }}">
                    <input type="hidden" name="price" value="{{ $p['salePrice'] }}">
                    <input type="hidden" name="image_url" value="{{ $p['productData']['productMainImage'] }}">
                    <button type="submit">Sepete Ekle</button>
                </form>
            </div>
        @endforeach
    </div>


    <!-- PAGINATION -->
    <div class="pagination">
        @if ($currentPage > 0)
            <a href="?page={{ $currentPage - 1 }}&pageSize={{ $pageSize }}&categoryID={{ $selectedCategory }}">←
                Önceki</a>
        @endif

        @if ($hasMore)
            <a href="?page={{ $currentPage + 1 }}&pageSize={{ $pageSize }}&categoryID={{ $selectedCategory }}">Sonraki
                →</a>
        @endif
    </div>
@endsection
