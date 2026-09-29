# Pengalaman & Pembelajaran: Magang MBKM Udacoding Batch 21

Catatan pribadi, bukan laporan formal. Isinya hal-hal yang beneran nyangkut selama ngerjain 12 task magang ini, dari kelas Algoritma sampai Next.js+Supabase. Ditulis biar nggak lupa lagi pas masuk fase real project nanti.

## Kelas Algoritma dulu, baru ngerti kenapa

Sebelum masuk ke task-task web dev, ada latihan logika di `Algoritma/algoritma-app`: hitung belanja, nilai akhir, tarif parkir, validasi password, perbandingan angka. Kedengarannya remeh (cuma if-else dan looping), tapi ini yang bikin kepala nggak nge-blank pas nulis validasi beneran di Laravel nanti. Kasus kayak "tarif parkir jam pertama beda sama jam berikutnya" atau "nilai akhir dari beberapa komponen dengan bobot beda-beda" itu polanya sama persis kayak nulis business rule di controller. Cuma dibungkus dengan nama yang lebih keren.

Pelajaran paling kepake dari sini: pisahin dulu "apa aturannya" dari "gimana cara nulis kodenya". Kalau alur logikanya udah jelas di kepala (atau di kertas), nulis kodenya jadi jauh lebih cepat, apapun bahasanya.

## Task 1-4: HTML/CSS/JS murni

Landing page statis, terus upgrade jadi ada carousel, terus jadi todo list, terus todo list-nya disambungin ke API cuaca. Nggak ada framework di fase ini, dan itu justru bagus. Jadi ngerti persis apa yang sebenarnya dikerjain React di belakang layar (DOM manipulation, event listener, fetch API) sebelum dimanjain sama abstraksi.

Hal yang paling nempel: `localStorage` itu gampang disalahgunain. Task 3-4 nyimpen todo dan kota cuaca terakhir ke situ, dan baru sadar belakangan kalau localStorage itu enak buat preferensi kecil per-device (tema, filter terakhir), tapi bukan tempat buat data yang harus konsisten atau perlu dibagi ke device lain. Ini balik lagi jadi relevan pas Task 12 (Supabase pakai cookie httpOnly buat sesi, bukan localStorage, justru karena alasan keamanan yang sama).

## Task 5-6: React pertama kali

Portfolio multi-page pakai React Router, terus dashboard omset pakai Recharts. Transisi paling kerasa: dari "manipulasi DOM langsung" (Task 1-4) ke "state yang nge-drive UI" (React). Butuh waktu buat beneran percaya bahwa nggak perlu `document.querySelector` lagi, cukup ubah state, biarin React yang re-render.

## Task 7-8: Laravel API, dan di sinilah tiga pelajaran besar ketauan

Ini bagian paling penting buat dicatat, karena tiga hal ini kelihatan jelas begitu ngebandingin kode Task 7/8 (dikerjain lebih awal) sama Task 12 (dikerjain belakangan, setelah dapet feedback langsung soal ini).

### 1. Jangan tumpuk semuanya di satu file

Untungnya di Task 7 dan 8 udah kepisah dari awal: `Controller` buat orkestrasi HTTP, `FormRequest` (`TaskRequest`, `ItemRequest`, `CategoryRequest`) buat validasi, `Resource` (`TaskResource`, dst.) buat bentuk response JSON, `Model` buat query dan aturan data. Nggak ada satu file pun yang isinya campur validasi-query-response jadi satu. Ini pola yang ternyata kepake lagi di Task 12, cuma nama lapisannya beda (Laravel: Controller/Request/Resource/Model, Next.js: `lib/actions` buat orkestrasi, `lib/validasi` buat aturan bisnis murni, `lib/data` buat akses database). Intinya sama: satu file, satu tanggung jawab, biar programmer lain (atau diri sendiri tiga bulan lagi) nggak perlu baca seluruh file cuma buat ngerti satu bagian kecil.

Yang belum konsisten: di Task 9 (React, konsumsi API Task 8) ada file `src/services/itemService.js` yang dibikin buat pola yang sama (misahin panggilan API dari komponen), tapi `Items.jsx` ternyata manggil `axiosClient` langsung, bukan lewat service itu. Jadi filenya ada, niatnya bener, tapi nggak beneran dipakai. Pelajaran: bikin lapisan pemisah itu nggak cukup, harus beneran dipakai konsisten di semua tempat yang sejenis, atau dihapus kalau ternyata nggak kepake.

### 2. Jangan biarin frontend nge-hit backend terus-terusan, dan paginate data yang banyak

Task 8 (`ItemController::index`) udah bener dari awal: pakai `->paginate()`, balikin `meta` (current_page, last_page, per_page, total), dan Task 9 di sisi frontend konsumsi meta itu buat tombol "Sebelumnya"/"Berikutnya", bukan narik semua barang sekaligus terus dipotong-potong di JS. Search-nya juga di-debounce 400ms, jadi ngetik "beras" nggak jadi 5 request terpisah per huruf.

Yang kelewat: `TaskController::index` di Task 7 masih `$query->latest()->get()`. Nggak ada pagination sama sekali. Buat task pribadi mungkin nggak kerasa sekarang (datanya dikit), tapi kalau nanti dipakai beneran dan tugasnya numpuk ratusan, endpoint ini bakal narik semuanya sekaligus tiap kali dibuka. Dan di Task 12, feed papan bantuan (`daftarBantuan()`) juga masih cuma di-cap `.limit(60)`, bukan pagination beneran, cuma "berhenti di 60 baris". Kalau permintaan bantuan udah lebih dari 60, sisanya bakal ilang gitu aja tanpa ada halaman 2 buat lihatnya.

Pelajaran: pagination itu bukan fitur "nice to have" yang ditambahin belakangan, dia bagian dari desain endpoint dari awal begitu ada kemungkinan datanya bisa tumbuh besar. Nggak semua endpoint butuh (kategori yang cuma belasan baris nggak perlu), tapi begitu bentuknya "daftar yang terus nambah" (task, item, transaksi, postingan), pagination itu defaultnya, bukan pengecualian.

### 3. ID acak buat akun login, ID sederhana gapapa buat kategori/item/transaksi

Ini yang paling jelas kelihatan begitu dibandingin. Task 7 dan Task 8 sama-sama pakai pola ini di tiap model:

```php
protected static function booted(): void
{
    static::creating(function ($model) {
        if (empty($model->id)) {
            $model->id = Str::random(16);
        }
    });
}
```

Dipasang di `User`, terus di-copy-paste ke `Task`, `Category`, sama `Item` juga. Buat `User` itu emang tepat: ID akun login sebaiknya nggak gampang ditebak urutannya (bayangin kalau ID user auto-increment, tinggal ganti angka di URL buat coba akses akun orang lain). Tapi buat `Task`, `Category`, sama `Item`, ID random 16 karakter itu sebenarnya nggak perlu-perlu amat. Itu bukan data yang butuh disembunyiin urutannya, dan auto-increment (`1, 2, 3, ...`) sama sekali nggak bikin aplikasinya kurang aman. Yang penting otorisasinya (siapa boleh akses/ubah data apa) tetep dicek di level query atau policy, bukan digantungin ke ID-nya susah ditebak atau enggak.

Task 12 (Supabase) kebetulan ID user-nya udah otomatis UUID (itu default `auth.users` dari Supabase sendiri, bukan keputusan sendiri), dan `help_requests.id` juga ikutan UUID, jadi polanya konsisten sama Task 7/8. Cuma karena defaultnya emang begitu, bukan karena mikir dulu. Kalau ditanya sekarang: kategori bantuan (Medis, Sembako, dst.) di Task 12 itu konstanta di kode, bukan tabel, jadi kasusnya nggak kena. Tapi kalau nanti ada tabel kategori beneran, gapapa ID-nya integer biasa 1/2/3/4.

Intinya: random ID itu buat nutupin sesuatu (biar nggak ketebak/enumerable). Dipakenya pas ada alasan keamanan konkret, bukan default yang di-copy-paste ke semua model tanpa mikir ulang tiap kali bikin tabel baru.

## Task 9: fullstack itu dua project yang harus saling percaya

Yang paling kerasa beda dari task sebelumnya: sekarang ada DUA codebase yang harus sinkron (Laravel di satu terminal, React di terminal lain), dan port React (5173) harus persis sama kayak yang diizinkan di `config/cors.php` sisi Laravel. Baru ngerti CORS itu bukan checkbox yang tinggal di-`true`-in, tapi daftar origin yang beneran dipikirin satu-satu.

Hal teknis kecil yang ternyata penting: token disimpen di variabel biasa waktu pertama kali di-import bakal ketinggalan token baru hasil login, karena interceptor Axios harus baca token dari `localStorage` ULANG tiap request, bukan di-assign sekali di awal. Bug kecil, tapi kalau nggak ketauan bisa bikin kepikiran "kenapa abis login masih 401" padahal tokennya udah bener ada.

## Task 10: dashboard tanpa backend, dan itu ngajarin hal beda

Semua data di Task 10 lokal (dummy data + localStorage), jadi nggak ada isu API-hit-berulang di sini karena memang nggak ada API. Tapi ID entitasnya (klien, proyek, notifikasi) tetep dibikin pakai `uuidv4()`, padahal ini data lokal doang, nggak ada resiko keamanan sama sekali kalau dibikin urut. Sama kayak temuan di poin sebelumnya: kebiasaan pakai ID random itu kebawa ke tempat yang sebenernya nggak butuh, cuma karena udah kepake di project lain.

Custom hooks di sini (`useDebounce`, `useLocalStorage`, `useInterval`, `usePagination`) itu pertama kalinya beneran ngerasain manfaat React custom hooks. Bukan buat pamer, tapi karena logic yang sama (debounce search, sync ke localStorage) kepake di banyak tempat dan nulis ulang tiap kali bakal berantakan.

## Task 12: nyoba nerapin semua pelajaran sekaligus

Papan Bantuan Warga ini yang paling kena revisi berkali-kali, dan justru dari situ paling banyak belajar:

- **Arsitektur dipecah tiga lapis** dari awal: `lib/actions` (orkestrasi server action, setara controller), `lib/validasi` (fungsi murni, nggak nyentuh Next.js/Supabase sama sekali, gampang ditest sendirian), `lib/data` (satu-satunya lapis yang boleh manggil Supabase). Ini langsung niru pola Task 7/8 yang udah kebukti kepake, tinggal disesuain nama lapisannya ke istilah Next.js.
- **Status data nggak boleh berubah sepihak.** Awalnya klik "Saya Ingin Membantu" langsung nandain selesai, padahal yang minta bantuan nggak pernah dimintain konfirmasi. Diubah jadi tiga tahap (menunggu → diproses → selesai) biar yang minta bantuan yang mastiin beneran kelar, bukan sekadar dipercaya dari klaim satu pihak.
- **Chrome/tampilan ngikutin state yang sebenarnya, bukan asumsi.** Sempat ada bug sidebar nempel di halaman login (padahal orang yang belum login nggak butuh menu ke halaman yang mereka nggak bisa akses), dan bug dua menu kelihatan aktif bareng gara-gara `path.startsWith()` doang tanpa cek batas segmen URL (`/bantuan-saya` kena anggap "termasuk" `/bantuan` gara-gara sama-sama diawali string itu).
- **RLS (Row Level Security) di database, bukan cuma di frontend.** Anon key Supabase itu publik (ikut ke-bundle ke JS browser), jadi kalau aturan "siapa boleh apa" cuma dicek di komponen React, itu percuma. Orang bisa panggil API-nya langsung. Aturannya harus ada di level database.

## Benang merah dari semua task

Kalau ditarik satu kesimpulan dari 12 task ini: skill teknis (Laravel, React, Next.js, whatever stack-nya) itu cuma alat. Yang beneran kepake berkali-kali justru pertanyaan yang sama diulang-ulang tiap task baru:

- File ini isinya kebanyakan hal buat satu file? Pisah.
- Data ini bisa numpuk sampai ratusan/ribuan baris? Rencanain pagination dari awal, jangan pas udah lambat baru mikirin.
- ID ini butuh susah ditebak (data akun/auth) atau nggak masalah urut (kategori, item, transaksi)? Jangan asal random-in semua atau auto-increment semua.
- Aturan "siapa boleh akses/ubah apa" itu dicek di tempat yang beneran nggak bisa dilewatin (backend/database), atau cuma di tampilan yang gampang di-bypass?

Empat pertanyaan ini kepake dari kelas Algoritma sampai Task 12, cuma konteksnya beda-beda. Yang berubah tiap task cuma bahasa dan framework-nya.

## Update: tiga temuan di atas udah diperbaiki

Nggak dibiarin cuma jadi catatan. ID Task/Category/Item di Task 7 dan 8 udah diganti balik ke auto-increment biasa (lewat migrasi ulang, bukan tambal di atas data lama), `TaskController::index` di Task 7 sekarang paginate kayak `ItemController` di Task 8, dan papan bantuan di Task 12 udah pakai `.range()` beneran, bukan `.limit(60)`. Yang paling nempel dari proses ngebenerinnya: pas ubah tipe ID category jadi integer, validasi `ItemRequest` yang tadinya nuntut `category_id` bertipe string malah nolak data yang bener. Ketauan lewat testing manual (curl), bukan kelihatan dari baca kode doang. Jadi tambahan pelajaran: ganti satu tipe data itu efeknya nyebar ke tempat yang nggak keliatan langsung (validasi, cast di model), dan itu alasan kenapa perubahan skema harus dites end-to-end, nggak cukup cuma migrate terus dibilang selesai.

---
Ridho Dwi Syahputra, Web Developer Intern Udacoding Batch 21
