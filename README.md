


# F.M.L POS System

A web-based point-of-sale application for managing products, processing sales, and reviewing store performance. The system includes a barcode-scanning checkout flow, stock tracking, and downloadable PDF receipts.

## Features

- **Product and inventory management** — Create, update, and remove products, set prices and stock levels, and upload product images.
- **Fast checkout** — Add products to a cart, adjust quantities, and complete sales using cash, card, GCash, Maya, or bank transfer.
- **Camera scanning** — Scan product codes with a supported device camera; generated product codes use Data Matrix.
- **Inventory-aware sales** — Validate stock during checkout and automatically update inventory when a sale is completed.
- **Receipts and transaction history** — View completed transactions and generate printable or downloadable PDF receipts.
- **Sales analytics** — Review revenue, transaction counts, average sale, payment methods, and top-selling products.
- **Account management** — Register, sign in, verify an email address, and manage profile details.

## Built with

- [Laravel 13](https://laravel.com/) and PHP 8.3+
- Blade, [Alpine.js](https://alpinejs.dev/), and [Tailwind CSS](https://tailwindcss.com/)
- [Vite](https://vite.dev/) for frontend assets
- [ZXing](https://github.com/zxing-js/browser) for browser-based code scanning
- [bwip-js](https://github.com/metafloor/bwip-js) for Data Matrix generation
- SQLite by default (configurable through Laravel's database settings)

## Requirements

- PHP 8.3 or later
- [Composer](https://getcomposer.org/)
- [Node.js and npm](https://nodejs.org/)
- SQLite, or another database supported by Laravel

## Getting started

Run the following commands from the repository root:

```bash
composer install
cp .env.example .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan storage:link
```

By default, the application uses SQLite. To use another database, update the `DB_*` values in `.env` before running migrations.

Start the local development services:

```bash
composer run dev
```

Open [http://localhost:8000](http://localhost:8000), register an account, and verify your email to access the dashboard. With the default `MAIL_MAILER=log` setting, locally generated verification messages are written to the Laravel log.

Camera scanning requires a browser that supports camera access and a secure context: use `localhost` during development or HTTPS when deployed. Grant camera permission when prompted.

## Tests

Run the Laravel test suite from the repository root:

```bash
php artisan test
```

## Deploy to Vercel

This repository includes a [FrankenPHP](https://frankenphp.dev/) container definition for Vercel's [Container Runtime](https://vercel.com/docs/functions/runtimes/container), currently in Beta. Vercel selects the container preset from `Dockerfile.vercel`; the image builds the frontend assets and serves Laravel through Caddy.

1. Push the repository to GitHub and import it into Vercel. Keep the project root set to the repository root.
2. Create a managed PostgreSQL or MySQL database. Vercel containers are ephemeral, so do not use the default SQLite database for production.
3. Create a **public** Blob store in your Vercel project's **Storage** tab, connect it to the project, and enable its `BLOB_READ_WRITE_TOKEN` environment variable.
4. Add these environment variables in **Vercel → Project Settings → Environment Variables**:

   | Variable | Value |
   | --- | --- |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | Generate with `php artisan key:generate --show`; keep the same key across deployments |
   | `APP_URL` | Your production HTTPS URL |
   | `DB_CONNECTION` | `pgsql` or `mysql`, matching your database |
   | `DB_URL` or `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Credentials for the managed database |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `QUEUE_CONNECTION` | `sync` unless you have separately configured a persistent queue worker |
   | `MAIL_*` | Production mail provider settings, required for account verification emails |
   | `PRODUCT_IMAGE_STORAGE` | `vercel_blob` |
   | `BLOB_READ_WRITE_TOKEN` | Read-write token from your Vercel Blob store |

   Set any additional TLS or connection options required by your database provider. Do not put production secrets in the repository or Docker image.
5. Run `php artisan migrate --force` once against the production database from a secure runner configured with those same database credentials. Run new migrations as part of your release process; the web container does not migrate the database on startup.
6. Deploy from the Vercel CLI:

   ```bash
   vercel login
   vercel link
   vercel deploy --prod
   ```

The container defaults `PRODUCT_IMAGE_STORAGE` to `vercel_blob`; local development defaults to `local`. Existing image paths saved on local storage are not migrated automatically; re-upload those images or migrate them before switching production traffic. Camera scanning works on the deployed HTTPS domain when the user grants camera permission.

You can test the image locally with Docker:

```bash
docker build -f Dockerfile.vercel -t fml-pos .
```

## Project layout

```text
├── app/                 # Application models and controllers
├── database/            # Migrations, factories, and seeders
├── resources/
│   ├── js/              # Alpine.js components and scanner integration
│   └── views/           # Blade pages and receipt templates
├── routes/              # Web and authentication routes
└── tests/               # Automated tests
```

## Configuration and security

- Keep secrets and local configuration in `.env`; do not commit that file.
- For production, set `APP_ENV=production`, disable debug output with `APP_DEBUG=false`, configure a production mailer and database, and serve the application over HTTPS.
- Restrict access to the application and its reports to authorized users.
