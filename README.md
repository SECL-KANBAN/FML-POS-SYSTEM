# FML POS System

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
cd fml-pos
composer install
cp .env.example .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate
npm install
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

Run the Laravel test suite from the `fml-pos` directory:

```bash
php artisan test
```

## Project layout

```text
fml-pos/
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
