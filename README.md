# HyperTeknoloji Laravel Task — Kurulum
Gereksinimler
- PHP 8.1+ (veya proje tarafından gerektirileni)
- Composer
- Node.js + npm (frontend asset'ler için opsiyonel)

Hızlı kurulum

1. Depoyu klonlayın ve dizine girin

```bash
git clone <repo-url>
cd hyperteknoloji_laravel_task
```

2. PHP bağımlılıklarını kurun

```bash
composer install
```

3. Node bağımlılıkları (varsa)

```bash
npm install
# Development için
npm run dev
# Production için
npm run build
```

4. Ortam dosyanızı oluşturun ve uygulama anahtarını üretin

```bash
cp .env.example .env
php artisan key:generate
```

5. Önemli environment değişkenleri (örnek)

```
HYPER_API_BASE_URL=https://api.hyperteknoloji.com.tr
HYPER_API_KEY=your_api_key_here
HYPER_API_TOKEN=your_token_here

CACHE_STORE=redis
CACHE_PREFIX=-hypertech-
PRODUCT_CACHE_TTL=your_cache_ttl_here (default 60sec)
CATEGORY_CACHE_TTL=your_category_cache_ttl_here default (3600sec)

SESSION_DRIVER=redis
```

Not: `config/services.php` içinde `hypertech` anahtarıyla bu değişkenlerin okutulduğundan emin olun.

6. Veritabanı migrasyonları (gerekliyse)

```bash
php artisan migrate
```

7. Uygulamayı çalıştırma

```bash
php artisan serve
# Sonra tarayıcıda: http://127.0.0.1:8000
```

Yardımcı komutlar

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:list
```

## Tercihler ve Kısa Açıklamaları

### Cache

Cache için Redis kurulumu yaptım böylelikle hem productiona yakın bir deployment alabildim hem de hız kazandım.

1. **Cache Süresi (TTL):**

    TTL değeri .env dosyasından yönetilir:
    ```
    PRODUCT_CACHE_TTL=60
    CATEGORY_CACHE_TTL=3600
    ```
    Kod içinde sabit değer yoktur; ortam bazlı esneklik sağlanır.

2. **Sayfa Bazlı Cache:**

    Her filtre kombinasyonu ve sayfa numarası için ayrı cache tutulur.

    ````
    page=1&pageSize=20
    page=2&pageSize=20&productCategoryID=5
    ````

3. **Cache Anahtarları:**

    Filtreler temizlenip sıralandıktan sonra, aşağıdaki formatta bir anahtar oluşturulur:

    ````
    laravel-hypertech-categories:list
    laravel-hypertech-products:<sha1_hash>
    ````

4. **Cache Invalidation:**

    Bu projede ürünler bu backend üzerinedn değişmediği için bir invalidation mekanizmasına gerek duymadım

    Cache *TTL süresi dolduğunda* Redis otomatik olarak key'i siler.

## Sepet Verileri

Bu projede sepet yönetimi için **veritabanı kullanmayı tercih ettim**. Bunun nedeni, veritabanının cookie/localStorage alternatiflerine göre daha güvenli, esnek ve sürdürülebilir bir yapı sunmasıdır.

**Tercih sebeplerim:**

1. **Kullanıcıdan bağımsız kalıcılık:**  
   Browser bazlı yöntemlerde sepet cihaz değişince kaybolur. Veritabanı ise oturumdan bağımsız olarak verileri korur.

2. **Güvenlik:**  
   Cookie/localStorage manipülasyona açıktır. Veritabanı tamamen backend tarafından yönetildiği için daha güvenlidir.

3. **İlişkisel veri yapısı:**  
   Ürün–adet–fiyat gibi verilerin yönetimi veritabanında daha düzenli ve ölçeklenebilir bir şekilde sağlanır.

4. **Genişletilebilirlik:**  
   Sipariş oluşturma, SMTP ile email hatırlatmaları ve kampanya ekleme gibi gelecekteki özellikler için güçlü bir temel sunar.

Bu nedenlerle backende yük bindirmesine rağmen sepet saklama yaklaşımı olarak veritabanı tabanlı mimariyi seçtim.

