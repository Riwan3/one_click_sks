# CLAUDE.md — Project Standard (Laravel + Inertia.js + Vue)

Dokumen ini adalah acuan wajib bagi Claude (dan siapa pun) saat menulis kode di project ini — mencakup **frontend** dan **backend** sekaligus.
Stack: **Laravel + Inertia.js + Vue 3 (Composition API) + Tailwind CSS**.

Prinsip utama: **scalable, ringkas, tidak bertele-tele, mudah dipahami orang lain.**

---

## 0. Aturan Umum Lintas Frontend & Backend

Aturan di section ini berlaku di **mana pun**, baik di file Vue (`.vue`) maupun PHP (`.php`).

### 0.1 Hindari If-Else Bertingkat/Berantai

If-else sederhana 1 kondisi tanpa nesting **boleh**. Yang **wajib dihindari** adalah nested if-else dan if-else berantai panjang (`if / elseif / elseif / else`). Gunakan pola berikut sebagai gantinya:

**Guard Clause / Early Return** (paling sering dipakai, di Vue maupun PHP):

```php
// ❌ Nested
if ($product) {
    if ($product->stock > 0) {
        return true;
    } else {
        return false;
    }
}

// ✅ Guard clause
if (! $product) return false;
if ($product->stock <= 0) return false;
return true;
```

**`match` expression** (PHP) sebagai pengganti if-else/switch berantai:

```php
$label = match ($status) {
    'pending'   => 'Menunggu',
    'paid'      => 'Lunas',
    'cancelled' => 'Dibatalkan',
    default     => 'Unknown',
};
```

**Ternary / null coalescing** untuk kondisi simpel (PHP & JS/Vue):

```php
$name = $request->name ?? 'Guest';
```

```js
const label = isActive ? 'Aktif' : 'Nonaktif'
```

**Lookup object/array** sebagai pengganti if-else "kalau X maka Y":

```php
$permission = [
    'admin'  => ['create', 'read', 'update', 'delete'],
    'editor' => ['read', 'update'],
][$role] ?? ['read'];
```

```js
const statusLabel = {
  pending: 'Menunggu',
  paid: 'Lunas',
  cancelled: 'Dibatalkan',
}[status] ?? 'Unknown'
```

**Strategy Pattern / polymorphism** untuk business logic kompleks per tipe (PHP, backend saja) — lihat detail di § 17 Service Layer.

**Aturan konkret:**
- Maksimal 1 level if-else tanpa nesting. Kalau butuh nesting → wajib refactor pakai guard clause.
- If-else berantai 3+ cabang berdasarkan value tunggal → wajib pakai `match` (PHP) atau lookup object (JS).
- Validasi kondisi di controller/component (`if ($request->has(...))`, dst) tetap boleh sebagai guard clause, bukan sebagai nested logic.

---

# BAGIAN 1 — FRONTEND (Inertia.js + Vue)

## 1. Prinsip Umum

- **Tulis kode sesedikit mungkin tanpa mengorbankan kejelasan.** Jangan bikin abstraksi/lapisan baru kalau belum benar-benar dibutuhkan (hindari over-engineering).
- **Konsisten > kreatif.** Ikuti pola yang sudah ada di project sebelum membuat pola baru.
- **Komponen kecil, fokus satu tanggung jawab.** Kalau file Vue > ~150-200 baris, pertimbangkan pecah jadi sub-komponen atau composable.
- **Jangan duplikasi logic.** Kalau logic dipakai di 2+ tempat → tarik ke composable (`resources/js/composables/`) atau helper.
- **Tidak ada dead code / console.log / kode di-comment yang menumpuk.** Bersihkan sebelum commit.
- **Explicit lebih baik daripada implicit.** Nama variabel/fungsi harus jelas maksudnya tanpa perlu baca isi fungsinya.
- **Hindari if-else bertingkat** — lihat aturan lengkap di § 0.1.

---

## 2. Struktur Folder

```
resources/js/
├── Pages/              # Satu file = satu halaman Inertia (mapping ke route/controller)
│   └── Product/
│       ├── Index.vue
│       ├── Create.vue
│       └── Edit.vue
├── Layouts/            # AppLayout, AuthLayout, dll — dipakai lewat <Layout>
├── Components/         # Komponen reusable, TIDAK terikat 1 page
│   ├── ui/             # Komponen generik (Button, Input, Modal, Table, dll)
│   └── shared/         # Komponen spesifik domain tapi dipakai lintas page
├── composables/         # Logic reusable (useForm helpers, usePagination, dll)
├── stores/             # Pinia store (HANYA untuk state lintas-halaman/global)
├── utils/              # Pure function helper (format date, currency, dsb)
└── types/              # TypeScript types/interfaces (jika pakai TS)
```

**Aturan:**
- Page component (`Pages/**`) HANYA mengurus: menerima props dari Inertia, layout, dan compose komponen. **Tidak boleh** berisi logic bisnis besar.
- Logic bisnis / transformasi data → composable atau utils, bukan ditulis langsung di `<script setup>` page.
- Komponen di `Components/ui/` tidak boleh tahu apa-apa soal domain (tidak import model "Product", dll). Harus generik & reusable.

---

## 3. Konvensi Penamaan

| Item | Konvensi | Contoh |
|---|---|---|
| Nama file komponen | PascalCase | `ProductCard.vue`, `ConfirmDialog.vue` |
| Nama komponen di template | PascalCase | `<ProductCard />` |
| Props | camelCase | `defineProps<{ productList: Product[] }>()` |
| Event emit | kebab-case, verb-based | `emit('item-selected')`, bukan `emit('data')` |
| Composable | prefix `use` | `useCart.js`, `usePagination.js` |
| Variabel boolean | prefix `is/has/should` | `isLoading`, `hasError` |
| Handler function | prefix `handle/on` | `handleSubmit`, `onDelete` |

---

## 4. Struktur Komponen Vue (wajib urutan ini di `<script setup>`)

```vue
<script setup>
// 1. imports
// 2. props & emits
// 3. composables / stores
// 4. reactive state (ref, reactive)
// 5. computed
// 6. methods/handlers
// 7. lifecycle hooks (onMounted, dll)
// 8. watchers (taruh paling bawah, hindari watcher kalau computed masih cukup)
</script>

<template>
  <!-- markup -->
</template>
```

- Gunakan **`<script setup>`**, bukan Options API.
- Selalu deklarasikan `defineProps` dan `defineEmits` secara eksplisit (dengan tipe kalau pakai TS).
- Hindari logic kompleks langsung di dalam template (`v-if` dengan expression panjang, dsb) — pindahkan ke `computed`.

---

## 5. Data & State

- **Props dari Inertia = source of truth** untuk data server. Jangan duplikasi ke local state kalau tidak perlu diedit.
- Gunakan `useForm()` bawaan Inertia untuk semua form (create/update), bukan bikin state manual + axios manual.
- **Pinia store** hanya untuk state yang benar-benar global/lintas halaman (auth user, cart, notifikasi). Jangan taruh state lokal 1 halaman ke store.
- Untuk data turunan dari props (filter, sort, format), pakai `computed`, jangan `watch` + `ref` manual kalau tidak diperlukan.

---

## 6. Styling (Tailwind)

- Utility class langsung di template. Kalau kombinasi class sudah dipakai berulang (3+ tempat) → ekstrak jadi komponen, **bukan** `@apply` bertumpuk di CSS.
- Urutan class disarankan: layout → spacing → sizing → typography → warna → state (hover/focus) — atau pakai Prettier plugin `prettier-plugin-tailwindcss` biar otomatis.
- Hindari inline `style=""` kecuali untuk nilai dinamis (mis. `width` dari data).
- Desain harus responsive by default (mobile-first: base class dulu, baru `md:`/`lg:`).

---

## 7. Komunikasi dengan Backend

- Semua request data pakai **Inertia** (`router.visit`, `router.post`, `useForm`), bukan `fetch`/`axios` manual, kecuali untuk endpoint API murni (mis. autocomplete/search) — baru pakai axios instance yang sudah dikonfigurasi di `bootstrap.js`.
- Jangan taruh URL route hardcoded — pakai helper route (`route('product.index')` via Ziggy) kalau project sudah setup Ziggy.
- Tangani loading & error state di form dengan `form.processing` dan `form.errors` bawaan Inertia — jangan bikin state manual duplikat.

---

## 8. Dummy Data (saat backend belum siap)

Kalau sedang fokus frontend dan backend/logic asli belum siap, **tetap render lewat Inertia** dengan data dummy dari Controller — jangan hardcode data di dalam komponen Vue.

Alasan: struktur props akan sama persis dengan kondisi final. Begitu backend selesai, cukup ganti isi controller, **komponen Vue tidak perlu diubah sama sekali**.

```php
// app/Http/Controllers/ProductController.php
public function index()
{
    return Inertia::render('Product/Index', [
        'products' => [
            ['id' => 1, 'name' => 'Kaos Polos', 'price' => 75000, 'stock' => 12],
            ['id' => 2, 'name' => 'Kemeja Flanel', 'price' => 150000, 'stock' => 5],
        ],
    ]);
}
```

```vue
<script setup>
defineProps({
  products: Array,
})
</script>
```

Untuk data yang lebih banyak/realistis (mis. testing pagination/table), gunakan **Laravel Factory** — jangan tulis array manual panjang-panjang:

```php
// database/factories/ProductFactory.php
public function definition(): array
{
    return [
        'name' => fake()->words(2, true),
        'price' => fake()->numberBetween(10000, 500000),
        'stock' => fake()->numberBetween(0, 50),
    ];
}
```

```php
public function index()
{
    return Inertia::render('Product/Index', [
        'products' => Product::factory()->count(20)->make(), // ->make() = tanpa simpan ke DB
    ]);
}
```

**Aturan dummy data:**
- Dummy data WAJIB lewat Controller → props Inertia, bukan hardcode di `<script setup>`.
- Kalau terpaksa hardcode langsung di Vue (mis. route/controller belum dibuat sama sekali), wajib kasih komentar `// TODO: ganti dengan props dari Inertia begitu endpoint siap` dan harus dipindah sebelum PR di-merge.
- Bentuk/shape data dummy harus sama persis dengan yang nanti dikirim API/controller asli (nama field, tipe data) supaya tidak ada refactor besar saat integrasi.

---

## 9. Reusability & Skala

Sebelum bikin komponen/logic baru, cek urutan ini:
1. Sudah ada komponen serupa di `Components/ui/`? → pakai/extend, jangan bikin baru.
2. Logic mirip sudah ada di composable lain? → refactor jadi composable yang lebih generik.
3. Kalau memang beda kebutuhan → baru buat baru, dengan penamaan jelas.

Kalau ada 1 komponen dipakai di banyak page dengan variasi kecil (misal beda warna/label), gunakan **props**, bukan duplikasi file (`ButtonPrimary.vue`, `ButtonSecondary.vue` → cukup `Button.vue` dengan prop `variant`).

---

## 10. Hal yang DILARANG (Frontend)

- ❌ Menulis logic bisnis di dalam file `Pages/**`.
- ❌ `console.log` / `debugger` tertinggal di kode final.
- ❌ Import komponen tapi tidak dipakai (unused import).
- ❌ Nested ternary lebih dari 1 level di template — pindahkan ke computed/method.
- ❌ Nested if-else di composable/handler — pakai guard clause atau lookup object (lihat § 0.1).
- ❌ Membuat abstraksi/wrapper baru untuk hal yang cuma dipakai 1 kali (YAGNI).
- ❌ Copy-paste komponen untuk kebutuhan yang mirip — refactor jadi reusable.
- ❌ Mengubah struktur folder/konvensi project tanpa alasan kuat & tanpa dijelaskan.

---

## 11. Checklist Sebelum Selesai (Frontend)

- [ ] Komponen fokus 1 tanggung jawab, tidak kepanjangan.
- [ ] Tidak ada duplikasi logic (sudah dicek composable/utils yang ada).
- [ ] Penamaan variabel/fungsi/komponen jelas & konsisten dengan konvensi di atas.
- [ ] Tidak ada `console.log`, kode mati, atau komentar basi.
- [ ] Tidak ada if-else bertingkat (sudah refactor pakai guard clause/lookup).
- [ ] Sudah responsive (mobile-first).
- [ ] Props & emits dideklarasikan eksplisit.
- [ ] Kalau ada state form → pakai `useForm()` Inertia, bukan state manual.

---

# BAGIAN 2 — BACKEND (Laravel)

## 12. Prinsip Umum

- **Fat model/service, skinny controller.** Controller HANYA orkestrasi (terima request → panggil service/action → return response). Tidak boleh ada query builder atau business logic panjang di controller.
- **Single Responsibility.** Satu class/method = satu tanggung jawab. Kalau method > ~25-30 baris atau ngerjain lebih dari 1 hal, pecah.
- **Jangan bikin abstraksi sebelum dibutuhkan (YAGNI).** Repository pattern, interface, dsb hanya dipakai kalau memang ada kebutuhan konkret (mis. butuh swap data source, testing lebih mudah) — bukan "karena best practice katanya".
- **Konsisten > kreatif.** Ikuti pola yang sudah ada di project sebelum membuat pola baru.
- **Tidak ada dead code, `dd()`/`dump()`/`Log::info` debug yang tertinggal, atau kode di-comment menumpuk.**
- **Explicit > implicit.** Nama method/variabel harus jelas maksudnya tanpa perlu baca isi implementasinya.
- **Hindari if-else bertingkat** — lihat aturan lengkap di § 0.1.

---

## 13. Struktur Folder

```
app/
├── Http/
│   ├── Controllers/         # Orkestrasi request → service/action → response
│   │   └── Api/             # Kalau ada endpoint API terpisah dari web
│   ├── Requests/            # Form Request — SEMUA validasi taruh di sini
│   ├── Resources/           # API Resource — transform model → response/props
│   └── Middleware/
├── Models/                  # Eloquent model — relasi, scope, accessor/mutator ringan
├── Services/                # Business logic utama (mis. ProductService, OrderService)
├── Actions/                 # Single-purpose action class (opsional, untuk logic sekali-pakai kompleks)
├── Repositories/            # HANYA kalau memang butuh abstraksi query kompleks/reusable
├── Enums/                   # Status, role, tipe — hindari magic string
├── Exceptions/              # Custom exception class
└── Policies/                # Authorization logic
```

**Aturan:**
- Controller tidak boleh langsung query Eloquent kompleks (`where`, `join`, dst lebih dari 1-2 baris). Panggil method dari Model (scope) atau Service.
- Validasi request WAJIB pakai **Form Request**, jangan `$request->validate()` inline di controller kalau rule-nya lebih dari 2-3 field.
- Response ke Inertia/API WAJIB lewat **API Resource**, jangan `return $model` mentah — supaya kontrak data konsisten & tidak bocor kolom sensitif.

---

## 14. Konvensi Penamaan

| Item | Konvensi | Contoh |
|---|---|---|
| Controller | PascalCase + suffix `Controller` | `ProductController` |
| Model | PascalCase singular | `Product`, `OrderItem` |
| Migration table | snake_case plural | `products`, `order_items` |
| Service | PascalCase + suffix `Service` | `ProductService` |
| Form Request | PascalCase + suffix `Request` | `StoreProductRequest`, `UpdateProductRequest` |
| Resource | PascalCase + suffix `Resource` | `ProductResource` |
| Method controller (RESTful) | `index, show, store, update, destroy` | wajib pakai ini, jangan bikin nama sendiri (`getAll`, `list`, dll) |
| Route name | kebab-case + dot notation | `products.index`, `products.store` |
| Job/Event/Listener | PascalCase, verb/noun jelas | `SendOrderConfirmation`, `OrderCreated` |
| Boolean column/variable | prefix `is_/has_` | `is_active`, `has_paid` |

---

## 15. Alur Standar 1 Fitur (mis. Product CRUD)

Urutan wajib saat bikin fitur baru:

1. **Migration** — definisikan tabel & kolom (dengan index yang perlu).
2. **Model** — relasi, `$fillable`/`$guarded`, cast, scope kalau perlu.
3. **Form Request** — `StoreXRequest`, `UpdateXRequest` untuk validasi + otorisasi (`authorize()`).
4. **Service** (kalau logic > sekadar create/update simpel) — taruh business logic di sini, bukan di controller.
5. **Resource** — bentuk data yang dikirim ke frontend.
6. **Controller** — orkestrasi: validasi (otomatis dari Form Request) → panggil service/model → return Inertia/Resource.
7. **Route** — daftar di `routes/web.php` atau `routes/api.php`, pakai `Route::resource()` kalau CRUD standar.

Contoh controller yang benar (ringkas, tidak bertele-tele):

```php
class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index()
    {
        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection(
                Product::query()->latest()->paginate(15)
            ),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }
}
```

---

## 16. Validasi

- SEMUA validasi lewat **Form Request**, bukan inline di controller.
- Gunakan `authorize()` di Form Request untuk cek permission dasar (bukan business rule kompleks — itu tetap di Service/Policy).
- Pesan error custom (kalau perlu Bahasa Indonesia) taruh di method `messages()` pada Form Request yang sama, jangan bikin file lang terpisah kecuali sudah multi-bahasa.

```php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
```

---

## 17. Service Layer

- Dipakai kalau logic melibatkan: lebih dari 1 model, transaksi DB, kalkulasi, dispatch event/job, atau dipanggil dari lebih dari 1 controller/tempat.
- Kalau cuma create/update 1 model tanpa logic tambahan, **boleh langsung di controller/model** — jangan bikin Service kosong cuma buat "ikut pola".
- Service tidak boleh tahu soal HTTP (`Request`, `response()`, dll) — dia terima array/DTO, return data/model, biar reusable (bisa dipanggil dari job, command, atau controller lain).
- Kalau ada business logic yang bercabang berdasarkan tipe (mis. metode pembayaran) dan jadi if-else panjang → refactor jadi **Strategy Pattern** (interface + class per tipe), bukan if-else besar di satu method.

```php
class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            // logic tambahan: log activity, dispatch event, dll
            return $product;
        });
    }
}
```

Contoh Strategy Pattern untuk menghindari if-else besar berdasarkan tipe:

```php
interface PaymentMethod {
    public function process(Order $order): void;
}

class CreditCardPayment implements PaymentMethod {
    public function process(Order $order): void { /* ... */ }
}

class BankTransferPayment implements PaymentMethod {
    public function process(Order $order): void { /* ... */ }
}

// Dipanggil via factory/container, bukan if-else
app(PaymentMethodFactory::class)->make($type)->process($order);
```

---

## 18. Database & Eloquent

- Migration harus punya nama jelas & index untuk kolom yang sering di-query/filter (`foreignId`, kolom status, dll).
- Gunakan `$fillable` (whitelist), **hindari** `$guarded = []` di production code.
- Query kompleks (lebih dari `where` + `orderBy` simpel) → taruh sebagai **local scope** di Model, bukan diulang-ulang di controller/service.

```php
// Model
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

- **N+1 query wajib dihindari** — selalu `with()`/`load()` relasi yang akan dipakai di Resource/view.
- Gunakan `paginate()` untuk list data, jangan `get()` semua data tanpa batas kalau berpotensi besar.

---

## 19. API Resource (kontrak data ke frontend)

- WAJIB dipakai untuk semua data yang dikirim ke Inertia/API — jangan return Eloquent model mentah.
- Bentuk field di Resource harus stabil (nama & tipe) — ini kontrak dengan frontend, jangan asal ubah tanpa koordinasi.

```php
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
        ];
    }
}
```

---

## 20. Error Handling & Response

- Gunakan Custom Exception untuk error domain-spesifik (mis. `InsufficientStockException`), jangan `throw new \Exception('...')` generik untuk kasus yang bisa terjadi berulang.
- Untuk redirect Inertia setelah action, selalu kasih flash message (`->with('success', ...)` / `->with('error', ...)`) — jangan silent redirect.
- Untuk API murni (bukan Inertia), selalu return status code yang sesuai (`422` validasi, `403` forbidden, `404` not found, `500` unexpected).

---

## 21. Testing

- Setiap fitur baru (Service/Controller penting) minimal punya **Feature Test** untuk happy path + 1 edge case (mis. validasi gagal, unauthorized).
- Gunakan **Factory** untuk data test, jangan hardcode array panjang di setiap test.
- Nama test method deskriptif: `test_user_can_create_product()`, bukan `test_1()`.

```php
public function test_user_can_create_product(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('products.store'), [
        'name' => 'Kaos Polos',
        'price' => 75000,
        'stock' => 10,
    ]);

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', ['name' => 'Kaos Polos']);
}
```

---

## 22. Security & Best Practice Wajib

- Selalu gunakan **Policy/Gate** untuk otorisasi, jangan cek role manual berulang-ulang di controller (`if ($user->role === 'admin')` tersebar di mana-mana).
- Jangan pernah taruh credential/API key hardcoded — selalu lewat `.env` + `config/*.php`.
- Selalu **mass-assignment protection** (`$fillable`).
- Sanitasi/validasi semua input dari user, termasuk query param untuk filter/sort (whitelist kolom yang boleh di-sort, jangan raw dari request).

---

## 23. API untuk Mobile App (APK)

Backend ini bisa melayani **dua konsumen sekaligus**: Inertia (web) dan REST API murni (untuk Android/iOS app). Keduanya **HARUS dipisah jalurnya**, jangan dicampur di satu controller/route yang sama.

### 23.1 Pemisahan Route & Controller

```
routes/
├── web.php     # Untuk Inertia — controller return Inertia::render()
└── api.php     # Untuk mobile app — controller return JSON murni

app/Http/Controllers/
├── ProductController.php        # Web/Inertia
└── Api/
    └── ProductController.php    # Mobile — namespace terpisah
```

- Controller API **tidak boleh** `return Inertia::render()` — harus `return response()->json(...)` atau langsung `return new ProductResource($product)`.
- Kalau logic-nya sama (mis. create product), **service layer dipakai bareng** oleh controller web maupun API — jangan duplikasi business logic. Yang beda cuma cara controller merespons.

```php
// app/Http/Controllers/Api/ProductController.php
class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }
}
```

### 23.2 Autentikasi — Laravel Sanctum

Untuk mobile app, gunakan **Sanctum token-based auth** (bukan session cookie seperti web):

```bash
php artisan install:api
```

```php
// routes/api.php
Route::post('/login', [Api\AuthController::class, 'login']); // return token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [Api\AuthController::class, 'me']);
    Route::apiResource('products', Api\ProductController::class);
});
```

```php
// Contoh login yang return token untuk disimpan di APK
public function login(Request $request)
{
    $request->validate(['email' => 'required|email', 'password' => 'required']);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    return response()->json([
        'token' => $user->createToken('mobile-app')->plainTextToken,
        'user'  => new UserResource($user),
    ]);
}
```

### 23.3 Format Response Konsisten (Wajib)

Semua endpoint API harus punya **bentuk response yang seragam**, supaya tim mobile tidak perlu handle format beda-beda per endpoint. Gunakan Resource + wrapper standar:

```php
// Sukses
{
  "success": true,
  "data": { ... },
  "message": null
}

// Error
{
  "success": false,
  "data": null,
  "message": "Produk tidak ditemukan"
}
```

Bikin trait/helper biar tidak nulis manual tiap controller:

```php
// app/Traits/ApiResponse.php
trait ApiResponse
{
    protected function success($data = null, string $message = null, int $code = 200)
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message], $code);
    }

    protected function error(string $message, int $code = 400)
    {
        return response()->json(['success' => false, 'data' => null, 'message' => $message], $code);
    }
}
```

### 23.4 Error Handling API (WAJIB status code benar)

Karena APK akan parsing response berdasarkan status code, pastikan `app/Exceptions/Handler.php` (atau `bootstrap/app.php` di Laravel 11+) mengembalikan JSON konsisten untuk API, bukan halaman error HTML:

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (ValidationException $e, $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    });

    $exceptions->render(function (AuthenticationException $e, $request) {
        if ($request->is('api/*')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }
    });
});
```

### 23.5 Versioning API

Kalau APK sudah dirilis ke publik (Play Store), **wajib versioning** dari awal — supaya update backend tidak langsung merusak APK versi lama yang masih dipakai user:

```php
// routes/api.php
Route::prefix('v1')->group(base_path('routes/api_v1.php'));
```

Jangan ubah struktur response endpoint yang sudah dipakai APK versi lama — kalau ada breaking change, buat `v2`, jangan timpa `v1`.

### 23.6 Rate Limiting

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // ...
});
```

### 23.7 Checklist Tambahan untuk API/APK

- [ ] Route API terpisah di `routes/api.php`, controller di namespace `Api/`.
- [ ] Auth pakai Sanctum token, bukan session.
- [ ] Semua response (sukses & error) pakai format seragam.
- [ ] Status code HTTP benar (200/201/401/403/404/422/500).
- [ ] Ada versioning (`/api/v1/...`) sejak awal kalau akan dipakai publik.
- [ ] Business logic tetap di Service — dipakai bareng oleh controller web & API, tidak duplikasi.

---

## 24. Hal yang DILARANG (Backend)

- ❌ Business logic panjang di dalam Controller.
- ❌ Query Eloquent kompleks ditulis berulang di banyak controller (harus jadi scope/service).
- ❌ `return $model` mentah tanpa Resource ke frontend.
- ❌ Validasi inline `$request->validate([...])` untuk request dengan banyak field — harus Form Request.
- ❌ `dd()`, `dump()`, `var_dump()` tertinggal di kode final.
- ❌ Nested if-else / if-else berantai panjang — pakai guard clause, `match`, atau Strategy Pattern (lihat § 0.1 & § 17).
- ❌ Membuat Repository/Interface untuk kasus yang cuma 1 implementasi tanpa kebutuhan nyata (over-engineering).
- ❌ N+1 query (lupa eager load relasi).
- ❌ Hardcode string status/role — gunakan **Enum**.

---

## 25. Checklist Sebelum Selesai (Backend)

- [ ] Controller ringkas, tidak ada business logic panjang.
- [ ] Validasi lewat Form Request.
- [ ] Response ke frontend lewat API Resource.
- [ ] Tidak ada N+1 query (sudah cek dengan `with()`/`load()` kalau ada relasi).
- [ ] Tidak ada `dd()`/`dump()`/kode debug tertinggal.
- [ ] Tidak ada if-else bertingkat (sudah refactor pakai guard clause/match/strategy).
- [ ] Otorisasi pakai Policy/Gate, bukan cek role manual tersebar.
- [ ] Ada minimal 1 Feature Test untuk fitur penting.
- [ ] Nama class/method/variabel jelas & konsisten dengan konvensi di atas.

---

# BAGIAN 3 — WORKFLOW BERSAMA

## 26. Git Workflow

- Setelah selesai ngoding (fitur/fix sudah jalan dan lolos checklist di atas — frontend maupun backend), **commit lalu push ke GitHub**.
- Commit message **singkat, padat, jelas** — satu baris, present tense, langsung ke intinya (contoh: `fix: validasi form login`, `feat: tambah halaman index product`, `feat: tambah endpoint api login mobile`). Hindari deskripsi panjang bertele-tele.
- Jangan gabung banyak perubahan tidak berhubungan dalam satu commit — pisah per fitur/fix biar history gampang ditelusuri.
- Kalau perubahan menyentuh frontend & backend sekaligus untuk 1 fitur (mis. tambah kolom baru: migration + resource + halaman Vue), boleh 1 commit selama masih 1 fitur yang sama.

---

**Ringkas prinsipnya:** controller cuma orkestrasi, logic bisnis di service/model, validasi di Form Request, output lewat Resource, komponen Vue kecil & fokus, hindari nested if-else di mana pun — kode ringkas, jelas maksudnya, dan gampang di-scale oleh orang lain yang baca setelah kita.