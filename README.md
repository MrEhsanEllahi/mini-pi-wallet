# Mini Wallet

A simple digital wallet built with **Laravel 12** (API) and **Vue.js**
(frontend). Users can send money to each other with real-time balance
updates via **Pusher**.

## 🔧 Stack

-   Laravel 12 (API)
-   Vue.js 3 (frontend, Vite)
-   MySQL/Postgres
-   Laravel Sanctum (auth)
-   Tailwind CSS
-   Pusher (real-time)

## ✨ Features

-   User signup & login
-   Send money between users (1.5% fee)
-   Live balance updates
-   Transaction history with pagination
-   Auto-formatted 6-digit user IDs
-   Audit trail & balance verification

## ⚙️ Setup

### Backend (Laravel)

``` bash
cd api
composer install
cp .env.example .env   # update DB + Pusher keys
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

API runs at `http://localhost:8000`.

### Frontend (Vue)

``` bash
cd app
npm install
cp .env.example .env   # update API + Pusher keys
npm run dev
```

Frontend runs at `http://localhost:5173`.

## 🚀 Usage

1.  Test users are **already seeded** during setup. You can log in with
    any of these accounts:
    -   **Lee** -- `lee@test.com` / password: `123456` (balance:
        \$1000)\
    -   **Zendi** -- `zendi@test.com` / password: `123456` (balance:
        \$500)\
    -   **Mark** -- `mark@test.com` / password: `123456` (balance:
        \$750)\
    -   **Daisy** -- `daisy@test.com` / password: `123456` (balance:
        \$300)
2.  After logging in, note your **user ID** (e.g., `000001`).\
3.  Send money to another user by entering their ID and amount.\
4.  Balances and history update instantly for both users.

### Example Fee

Send **\$100** → Receiver gets \$100, Sender pays \$101.50 (\$100 +
\$1.50 fee).

## 📜 Artisan Helpers

``` bash
# verify balances
php artisan balance:verify

# reset DB
php artisan migrate:fresh --seed
```

## 🗄️ DB Schema

**users**: id, name, email, password, balance\
**transactions**: id, sender_id, receiver_id, amount, commission_fee
