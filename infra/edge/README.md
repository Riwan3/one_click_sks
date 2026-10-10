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

## Catatan

- Minimal satu file `sites/*.caddy` harus ada sebelum `docker compose up -d`, kalau tidak Caddy tidak punya situs yang dilayani.
- File `sites/*.caddy` tidak ikut git (berisi domain server); yang ikut hanya `*.caddy.example`.
- Cek jaringan: `docker network inspect edge` menampilkan semua container yang tersambung.
