# Mini Wallet

Mini Wallet is a lightweight digital wallet composed of a Laravel 12 API and a Vue 3 single-page app. It lets users move money between accounts, tracks balances with a 1.5% fee for senders, and pushes real-time updates through Pusher.

## Tech Stack

- Laravel 12 with Sanctum for the API and authentication
- Vue 3 + Vite for the frontend
- MySQL or Postgres for persistence
- Tailwind CSS for styling
- Pusher Channels for realtime events

## Features

- Register, log in, and stay authenticated with Sanctum tokens
- Transfer funds between users with automatic commission handling
- Live balance and transaction updates through private Pusher channels
- Paginated transaction history with sender/receiver context
- Utility Artisan command to re-check balances against transactions

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node 18+
- MySQL or Postgres
- Pusher account (app id/key/secret/cluster)

### Backend (Laravel API)

```bash
cd api
composer install
cp .env.example .env  # supply DB credentials and Pusher keys
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

The API defaults to `http://localhost:8000`.

> **Realtime note:** Broadcasting uses queued jobs. Run `php artisan queue:work` so events reach Pusher. If you prefer to skip the queue in development, switch the `TransactionCreated` event to `ShouldBroadcastNow`.

### Frontend (Vue SPA)

```bash
cd app
npm install
cp .env.example .env  # set VITE_API_BASE_URL and Pusher config
npm run dev
```

The development server defaults to `http://localhost:5173`.

## Using the App

Seed data includes four demo users:

- Lee — `lee@test.com` / `123456` (balance $1000)
- Zendi — `zendi@test.com` / `123456` (balance $500)
- Mark — `mark@test.com` / `123456` (balance $750)
- Daisy — `daisy@test.com` / `123456` (balance $300)

Log in as any user, note your 6-digit user ID, and initiate a transfer. The sender pays the amount plus 1.5% (for example, $100 transfer costs $101.50). Both users see updated balances and the shared transaction entry immediately once the queue worker processes the broadcast.

## Helpful Artisan Commands

```bash
# Rebuild the schema and seed demo data
php artisan migrate:fresh --seed

# Verify that balances match the transaction ledger
php artisan balance:verify
```

## Database Overview

- `users`: id, name, email, password, balance
- `transactions`: id, sender_id, receiver_id, amount, commission_fee
