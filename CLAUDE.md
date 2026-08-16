# CLAUDE.md — Frontend Standard (Laravel + Inertia.js + Vue)

Dokumen ini adalah acuan wajib bagi Claude (dan siapa pun) saat menulis kode frontend di project ini.
Stack: **Laravel + Inertia.js + Vue 3 (Composition API) + Tailwind CSS**.

Prinsip utama: **scalable, ringkas, tidak bertele-tele, mudah dipahami orang lain.**

---

## 1. Prinsip Umum

- **Tulis kode sesedikit mungkin tanpa mengorbankan kejelasan.** Jangan bikin abstraksi/lapisan baru kalau belum benar-benar dibutuhkan (hindari over-engineering).
- **Konsisten > kreatif.** Ikuti pola yang sudah ada di project sebelum membuat pola baru.
- **Komponen kecil, fokus satu tanggung jawab.** Kalau file Vue > ~150-200 baris, pertimbangkan pecah jadi sub-komponen atau composable.
- **Jangan duplikasi logic.** Kalau logic dipakai di 2+ tempat → tarik ke composable (`resources/js/composables/`) atau helper.
- **Tidak ada dead code / console.log / kode di-comment yang menumpuk.** Bersihkan sebelum commit.
- **Explicit lebih baik daripada implicit.** Nama variabel/fungsi harus jelas maksudnya tanpa perlu baca isi fungsinya.

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

## 9. Reusability & Skala

Sebelum bikin komponen/logic baru, cek urutan ini:
1. Sudah ada komponen serupa di `Components/ui/`? → pakai/extend, jangan bikin baru.
2. Logic mirip sudah ada di composable lain? → refactor jadi composable yang lebih generik.
3. Kalau memang beda kebutuhan → baru buat baru, dengan penamaan jelas.

Kalau ada 1 komponen dipakai di banyak page dengan variasi kecil (misal beda warna/label), gunakan **props**, bukan duplikasi file (`ButtonPrimary.vue`, `ButtonSecondary.vue` → cukup `Button.vue` dengan prop `variant`).

---

## 10. Hal yang DILARANG

- ❌ Menulis logic bisnis di dalam file `Pages/**`.
- ❌ `console.log` / `debugger` tertinggal di kode final.
- ❌ Import komponen tapi tidak dipakai (unused import).
- ❌ Nested ternary lebih dari 1 level di template — pindahkan ke computed/method.
- ❌ Membuat abstraksi/wrapper baru untuk hal yang cuma dipakai 1 kali (YAGNI).
- ❌ Copy-paste komponen untuk kebutuhan yang mirip — refactor jadi reusable.
- ❌ Mengubah struktur folder/konvensi project tanpa alasan kuat & tanpa dijelaskan.

---

## 11. Checklist Sebelum Selesai

- [ ] Komponen fokus 1 tanggung jawab, tidak kepanjangan.
- [ ] Tidak ada duplikasi logic (sudah dicek composable/utils yang ada).
- [ ] Penamaan variabel/fungsi/komponen jelas & konsisten dengan konvensi di atas.
- [ ] Tidak ada `console.log`, kode mati, atau komentar basi.
- [ ] Sudah responsive (mobile-first).
- [ ] Props & emits dideklarasikan eksplisit.
- [ ] Kalau ada state form → pakai `useForm()` Inertia, bukan state manual.

---

## 12. Git Workflow

- Setelah selesai ngoding (fitur/fix sudah jalan dan lolos checklist di atas), **commit lalu push ke GitHub**.
- Commit message **singkat, padat, jelas** — satu baris, present tense, langsung ke intinya (contoh: `fix: validasi form login`, `feat: tambah halaman index product`). Hindari deskripsi panjang bertele-tele.
- Jangan gabung banyak perubahan tidak berhubungan dalam satu commit — pisah per fitur/fix biar history gampang ditelusuri.

---

**Ringkas prinsipnya:** kode secukupnya, jelas maksudnya, tidak duplikasi, dan gampang di-scale oleh orang lain yang baca setelah kita.