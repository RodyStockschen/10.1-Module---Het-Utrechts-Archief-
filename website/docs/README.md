# Ontmoet de Oudegracht

Interactieve historische video-ervaring voor Het Utrechts Archief. De
website combineert een film uit 1929 van de Oudegracht met een
synchrone minimap, tijdlijn en archiefstukken. Beheerders bewerken de
inhoud via een CMS.

**Stack:** PHP 8.2, MySQL 8, vanilla JS (ES modules), Vite voor bundling,
Docker Compose voor lokale ontwikkeling.

---

## Snel starten (lokaal)

1. **Kopieer env:**
   ```bash
   cp .env.example .env
   ```

2. **Start Docker:**
   ```bash
   docker compose up -d
   ```
   De eerste keer bouwt Docker de PHP image en importeert MySQL automatisch
   het schema + de seed data uit `db/01-schema.sql` en `db/02-seed.sql`.

3. **Frontend bundelen (eenmalig):**
   ```bash
   npm install
   npm run build
   ```
   Dit maakt `dist/` met gebundelde JS en CSS. PHP leest de manifest.

4. **Bezoek de site:**
   - Publieke site: <http://localhost:8080/>
   - CMS: <http://localhost:8080/cms/>
     - E-mail: `admin@hua.local`
     - Wachtwoord: `admin`
   - **Wijzig het admin-wachtwoord direct na installatie** (zie onder).

---

## Development met hot reload (optioneel)

Zet `APP_ENV=dev` in `.env` en start Vite naast Docker:

```bash
npm run dev   # draait op http://localhost:5173
```

PHP laadt dan de dev-server rechtstreeks in de HTML, met HMR voor CSS en JS.

---

## Projectstructuur

```
website/
├── index.php              # publieke homepage (video + kaart + tijdlijn)
├── 404.php
├── api/
│   ├── locaties.php       # JSON: alle locaties voor huidige taal
│   └── archiefstukken.php # JSON: archiefstukken per locatie
├── cms/                   # beheerder CRUD
│   ├── login.php, logout.php, index.php
│   ├── locaties.php, locatie-form.php, locatie-delete.php
│   └── archiefstukken.php, archiefstuk-form.php, archiefstuk-delete.php
├── includes/
│   ├── bootstrap.php, config.php, db.php, auth.php
│   ├── i18n.php + lang/{nl,en}.php
│   ├── header.php, footer.php  (publieke site)
│   └── vite.php           (Vite manifest resolver)
├── src/                   # Vite source
│   ├── js/   (main.js, player.js, modal.js, dropdowns.js, search-toggle.js, cms.js)
│   └── css/  (main.css, header.css, footer.css, player.css, modal.css, cms.css)
├── assets/
│   ├── img/               # logo, icons, plattegrond
│   ├── video/             # historische video
│   └── uploads/           # CMS image uploads (gitignored)
├── db/                    # SQL (auto-imported by Docker)
├── docker/apache-site.conf
├── docker-compose.yml, Dockerfile
├── package.json, vite.config.js
└── docs/README.md, cms-handleiding.md
```

---

## Database

Tabellen: `admins`, `locations`, `location_i18n`, `archive_items`,
`archive_i18n`. Vertalingen staan altijd in losse `*_i18n` tabellen met
PK `(parent_id, lang)`. Alle FK's hebben `ON DELETE CASCADE`.

Een locatie heeft:
- `video_start_sec` / `video_end_sec` — JS activeert de hotspot tijdens
  dit interval.
- `map_x_pct` / `map_y_pct` — positie op de plattegrond (0–100, zodat
  het schaalonafhankelijk werkt).

---

## Admin-wachtwoord wijzigen

```bash
docker compose exec app php -r "echo password_hash('nieuw_ww', PASSWORD_BCRYPT);"
```
Kopieer de output en update `admins.password_hash`:
```bash
docker compose exec db mysql -u hua -phua oudegracht \
  -e "UPDATE admins SET password_hash='<hash>' WHERE email='admin@hua.local';"
```

Of maak een nieuwe admin aan via dezelfde methode.

---

## Security checklist (PvA §9.3)

- [x] Alle queries via **PDO prepared statements** (zie `includes/db.php`).
- [x] Wachtwoorden via `password_hash` / `password_verify` (bcrypt).
- [x] **CSRF-token** per sessie op elke POST (`includes/auth.php`).
- [x] Output escaping via `e()` helper (htmlspecialchars).
- [x] Upload-validatie: alleen JPEG/PNG/WebP, max 5 MB, random filename.
- [x] Session cookie: `HttpOnly`, `SameSite=Lax`.
- [x] Apache vhost + `.htaccess` blokkeren `includes/`, `db/`, `src/`.
- [x] HTTP headers: `X-Content-Type-Options`, `X-Frame-Options`,
      `Referrer-Policy`, `Permissions-Policy`.

---

## Deploy naar eigen hosting

1. Trek alleen de productie-bestanden mee: **neem `dist/` mee** (gebuild),
   **sla `node_modules/` over**, en zorg dat `.env` de productie-waarden
   bevat (DB-credentials, `APP_URL`, `APP_ENV=prod`).
2. Zet alles in de public web root. Zorg dat Apache `mod_rewrite` en
   `mod_headers` aan staan (zie `.htaccess`).
3. Importeer `db/01-schema.sql` + `db/02-seed.sql` in de MySQL van de
   hosting.
4. Zorg dat `assets/uploads/` beschrijfbaar is voor de webserver
   (`chmod 775` + juiste owner).
5. Wijzig het admin-wachtwoord (zie boven).

---

## Verificatie (hoe weet je dat het werkt)

Zie `docs/cms-handleiding.md` voor beheerder-stappen. Technische tests:

```bash
# 1. Containers draaien
docker compose ps

# 2. Database heeft seed data
docker compose exec db mysql -u hua -phua oudegracht \
  -e "SELECT COUNT(*) FROM locations;"        # → 5

# 3. Homepage geeft 200
curl -sI http://localhost:8080/ | head -1    # → HTTP/1.1 200

# 4. API werkt
curl -s 'http://localhost:8080/api/archiefstukken.php?location_id=1&lang=nl'

# 5. CMS blokkeert zonder login
curl -sI http://localhost:8080/cms/ | grep -i location  # → /cms/login.php
```
