# Ditco Affiliate Gateway

Backend engine for affiliate landing pages: profile lookup, click tracking, click
statistics, and redirect logic to the main product site.

**Stack:** PHP 8.1+ (OOP, PSR-4), MySQL, Node.js (Express notification service)

## What It Does

1. An affiliate registers (`POST /auth/register`) with business info and a unique
   slug (e.g. `acme`). Taken slugs are automatically suffixed (`acme-1`), and a
   notification is fired to the Node.js micro-service.
2. The landing page fetches `GET /affiliate/{slug}` — business profile + product.
3. Visitors clicking "Go to main product" hit `POST /click-track` (rate limited)
   before redirecting.
4. Affiliates log in (`POST /auth/login`) and view their own click counts via
   `GET /affiliate/{slug}/stats`, update contact info, and manage their product.

## Setup & Run

Prerequisites: PHP 8.1+ with `pdo_mysql`, Composer, Node.js, a MySQL server.

```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment (DB credentials + JWT secret)
cp .env.example .env
#    - Set DB_HOST, DB_PORT, DB_USER, DB_PASS for your MySQL server
#    - Set JWT_SECRET to a random string of at least 32 characters

# 3. Create the database (e.g. "ditco_affiliate") and load the schema
mysql -h 127.0.0.1 -P 6034 -u db_user -p ditco_affiliate < schema.sql
#    Alternative (no mysql client): run php load.php from the project root

# 4. Start the PHP API
php -S 127.0.0.1:8000 -t public
```

Notification service (separate terminal):

```bash
cd notification-service
npm install
npm start        # runs on http://localhost:4000
```

`POST /auth/register` logs the notification to the Node console, e.g.
`[NOTIFICATION] affiliate.registered: Acme Corp <acme@example.com> ...`.

## API Endpoints

| Method | Endpoint                     | Auth | Description                                        |
|--------|------------------------------|------|----------------------------------------------------|
| GET    | `/`                          | No   | Service info with full endpoint list               |
| POST   | `/auth/register`             | No   | Register an affiliate (triggers notification)      |
| POST   | `/auth/login`                | No   | Log in with email/password, returns a JWT          |
| GET    | `/affiliate/{slug}`          | No   | Affiliate + product info for the landing page      |
| PUT    | `/affiliate/{slug}`          | JWT  | Update own contact info                            |
| POST   | `/affiliate/{slug}/product`  | JWT  | Create own product                                 |
| PUT    | `/affiliate/{slug}/product`  | JWT  | Update own product                                 |
| DELETE | `/affiliate/{slug}/product`  | JWT  | Delete own product                                 |
| POST   | `/click-track`               | No   | Record a click (rate limited: 5/IP per 60s)        |
| GET    | `/affiliate/{slug}/stats`    | JWT  | View own click counts (total / 24h / 7d)           |

Authenticated requests send `Authorization: Bearer <token>`.

Example registration body:

```json
{
  "business_name": "Acme Corp",
  "email": "acme@example.com",
  "phone": "+1-555-0100",
  "slug": "acme",
  "password": "AaBb1!x9",
  "logo_url": "https://example.com/logo.png"
}
```

Password policy: at least 2 uppercase, 2 lowercase, 1 number, 1 special character.

## Database Schema

Three normalized tables, defined in `schema.sql`:

- **affiliates** — business profile, unique slug, hashed password
- **products** — one destination product per affiliate (FK to affiliates)
- **click_logs** — one row per click, FK to affiliates, indexed on `(ip_address, clicked_at)` for rate limiting

## Security

- All queries use PDO prepared statements — no string-built SQL
- Slugs are whitelisted to `[a-z0-9-]` before every lookup
- Business name, phone, and email are sanitized on input and output
- `logo_url` and product `destination_url` are validated with `FILTER_VALIDATE_URL`
- Passwords enforce a complexity policy and are stored with `password_hash()`
- `/click-track` is capped at 5 requests per IP per 60 seconds
- Credentials and the JWT secret live in `.env`, excluded from version control
- JWT endpoints enforce ownership (token's affiliate must match the resource)

## Tests

```bash
composer test    # runs PHPUnit unit tests (tests/)
```

## Git Workflow

```bash
git init
git checkout -b click-tracking
# implement click tracking
git add .
git commit -m "Add click tracking endpoint with rate limiting"
git checkout main
git merge click-tracking
git push origin main
```

## Handling an Accidental API Key Leak

1. Revoke and rotate the exposed key/secret immediately at the provider (or
   regenerate `JWT_SECRET`, which invalidates all issued tokens).
2. Remove the key from git history (`git filter-repo` or BFG Repo-Cleaner),
   then force-push.
3. Audit access logs for the window the key was exposed, looking for unexpected
   usage.
4. Confirm `.env` is in `.gitignore` and add a pre-commit hook or secret scanner
   (e.g. gitleaks) to prevent recurrence.
5. Notify affected stakeholders if any data may have been accessed.