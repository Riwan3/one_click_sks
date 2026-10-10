# Edge — reverse proxy bersama untuk banyak aplikasi di satu VPS

Satu container Caddy memegang port 80/443 dan meneruskan tiap domain ke aplikasinya (OneClick, Control Center, dst).
Aplikasi tidak membuka port ke internet; mereka hanya bergabung ke jaringan Docker `edge`.

```
Internet ─▶ edge_caddy (80/443, HTTPS otomatis)
              ├─ oneclick.contoh.co.id ─▶ oneclick_app:80
              └─ control.contoh.co.id  ─▶ oneclick_cc_app:80
```

## Setup pertama (server baru)

1. Pastikan DNS A record tiap domain sudah mengarah ke IP server, dan port 80 & 443 terbuka di firewall.
2. Buat jaringan bersama (sekali per server):
   ```bash
   docker network create edge
   ```
3. Jalankan aplikasi-aplikasinya (lihat README/panduan masing-masing) dengan `COMPOSE_PROFILES=` **kosong** di `.env`
   (Caddy bawaan per aplikasi tidak dipakai di mode ini).
4. Daftarkan domain di Caddy bersama:
   ```bash
   cd ~/oneclick/infra/edge
   cp sites/oneclick.caddy.example sites/oneclick.caddy
   cp sites/control-center.caddy.example sites/control-center.caddy
   nano sites/oneclick.caddy          # ganti oneclick.contoh.co.id dengan domain asli
   nano sites/control-center.caddy    # ganti control.contoh.co.id dengan domain asli
   docker compose up -d
   docker compose logs --tail=50 caddy      # cari: certificate obtained successfully
   ```

## Menambah aplikasi baru

1. Di compose aplikasi itu: sambungkan service webnya ke jaringan `edge` (`networks: [default-nya, edge]`, dan
   `edge: { name: edge, external: true }`), beri `container_name` yang unik.
2. Buat `sites/nama-app.caddy` yang berisi domain dan `reverse_proxy <container_name>:80`.
3. Muat ulang tanpa downtime:
   ```bash
   docker compose exec caddy caddy reload --config /etc/caddy/Caddyfile
   ```

## Pindah dari Caddy bawaan OneClick (server yang sudah jalan)

Sebelumnya Caddy ada di dalam compose OneClick (profile `caddy`). Urutan pindahnya, dengan downtime HTTPS singkat:

```bash
cd ~/oneclick
git pull
docker network create edge                      # abaikan error "already exists"
nano .env                                       # COMPOSE_PROFILES=   (kosongkan)
docker compose up -d                            # aplikasi bergabung ke jaringan edge
docker rm -f oneclick_caddy                     # bebaskan port 80/443
cd infra/edge
cp sites/oneclick.caddy.example sites/oneclick.caddy && nano sites/oneclick.caddy
docker compose up -d
docker compose logs --tail=50 caddy
```

Sertifikat diterbitkan ulang oleh Caddy bersama (butuh sekitar setengah menit).

## Opsional: Cloudflare Tunnel (tanpa membuka port, HTTPS oleh Cloudflare)

Cocok bila port 80/443 server tidak bisa dibuka, atau Anda ingin proteksi Cloudflare (DDoS/WAF) di depan aplikasi.
`cloudflared` membuat koneksi KELUAR ke Cloudflare, lalu meneruskan trafik ke container aplikasi lewat jaringan `edge`.

```
Internet ─▶ Cloudflare (HTTPS) ─▶ tunnel ─▶ edge_cloudflared ─▶ oneclick_app:80 / oneclick_cc_app:80
```

**Syarat:** domain harus memakai nameserver Cloudflare (tambahkan domain di dashboard Cloudflare lalu ganti nameserver
di registrar, mis. Rumahweb). Selama nameserver belum berpindah, tunnel tidak bisa dipakai.

1. Dashboard Cloudflare > **Zero Trust > Networks > Tunnels > Create a tunnel** > pilih *Cloudflared* > beri nama > salin **token**.
2. Di tunnel itu tambahkan **Public Hostname** satu per aplikasi (Type `HTTP`):

   | Hostname | URL (service) |
   |---|---|
   | `oneclick.domainanda.co.id` | `oneclick_app:80` |
   | `control.domainanda.co.id` | `oneclick_cc_app:80` |

   Cloudflare otomatis membuat DNS CNAME-nya. Hapus A record lama untuk hostname yang sama bila ada.
3. Di server:
   ```bash
   cd ~/oneclick/infra/edge
   cp .env.example .env && nano .env          # tempel CLOUDFLARE_TUNNEL_TOKEN=
   docker compose --profile tunnel up -d cloudflared
   docker compose logs --tail=30 cloudflared  # cari: "Registered tunnel connection"
   ```
4. Server yang **hanya** memakai tunnel tidak perlu Caddy; jalankan perintah di atas saja (bukan `docker compose up -d`).
   Jangan melayani hostname yang sama lewat Caddy dan tunnel sekaligus; pilih satu per hostname.

Pada mode ini aplikasi tetap memakai `APP_URL=https://hostname-anda` dan `SESSION_SECURE_COOKIE=true`. IP pengunjung
dibaca dari header `X-Forwarded-For` (aplikasi sudah mempercayai proxy).

## Catatan

- Minimal satu file `sites/*.caddy` harus ada sebelum `docker compose up -d`, kalau tidak Caddy tidak punya situs yang dilayani.
- File `sites/*.caddy` tidak ikut git (berisi domain server); yang ikut hanya `*.caddy.example`.
- Cek jaringan: `docker network inspect edge` menampilkan semua container yang tersambung.
