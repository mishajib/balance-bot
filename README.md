# BalanceBot ⚡🤖

**BalanceBot** is a Laravel-based application and Telegram Bot integration built to track and monitor **DESCO (Dhaka Electric Supply Company Limited)** prepaid electricity meter balances in real-time. It provides interactive Telegram bot commands, low-balance warnings, and automated CLI tasks to push balance updates to Telegram chats or groups.

---

## 🌟 Features

- 🔌 **DESCO Prepaid Meter Monitoring**: Real-time balance fetching for multiple meters (e.g., Home and Godown).
- 💬 **Interactive Telegram Bot**: Supports interactive commands directly inside Telegram (`/home_balance`, `/godown_balance`, `/info`, etc.).
- ⚠️ **Low Balance Alerts**: Automatic warnings when account balances drop below a specified threshold (default: `৳ 100.00`).
- 🛠️ **Artisan CLI Tools**: Commands to fetch and dispatch balance reports directly to configured Telegram chats.
- 📡 **Webhook Management**: Dedicated Artisan commands and web routes for registering, inspecting, and deleting Telegram webhooks.

---

## 📋 Requirements

- **PHP**: `>= 8.2`
- **Composer**: `>= 2.0`
- **Database**: MySQL / MariaDB or SQLite
- **Node.js & NPM**: (For compiling frontend assets with Vite)
- **Telegram Bot**: Token from [@BotFather](https://t.me/BotFather)
- **Public HTTPS Domain or Tunnel** (e.g., Ngrok, Cloudflare Tunnel) for Webhook delivery in development.

---

## ⚙️ Environment Configuration (`.env`)

Copy the sample environment file to create your local `.env`:

```bash
cp .env.example .env
```

### Configuration Variables Breakdown

#### 1. Core Application Settings
| Variable | Default / Example | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `BalanceBot` | Name of the application |
| `APP_ENV` | `local` / `production` | Application environment state |
| `APP_KEY` | `base64:...` | Application encryption key (`php artisan key:generate`) |
| `APP_DEBUG` | `true` / `false` | Enable or disable debug mode |
| `APP_URL` | `http://localhost` | Base URL of the deployed application |

#### 2. Database Settings
| Variable | Default / Example | Description |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `mysql` / `sqlite` | Database driver |
| `DB_HOST` | `127.0.0.1` | Database host server |
| `DB_PORT` | `3306` | Database connection port |
| `DB_DATABASE` | `balance_bot` | Database name |
| `DB_USERNAME` | `root` | Database username |
| `DB_PASSWORD` | `password` | Database password |

#### 3. Telegram Bot Configuration
| Variable | Example | Description |
| :--- | :--- | :--- |
| `TELEGRAM_BOT_TOKEN` | `8459231468:AAF...` | Bot access token issued by `@BotFather` |
| `TELEGRAM_WEBHOOK_URL` | `https://your-domain.com/telegram/webhook` | Complete HTTPS URL for Telegram webhook callbacks |
| `TELEGRAM_BOT_USERNAME` | `@midescobot` | Public username handle of your Telegram bot |
| `TELEGRAM_CHAT_ID` | `7194856636` | Telegram Chat/User ID where automated balance updates are sent |

#### 4. DESCO Meter Account Configuration
| Variable | Example | Description |
| :--- | :--- | :--- |
| `DESCO_HOME_ACCOUNT_NO` | `23108921` | 8-digit DESCO prepaid customer account number for **Home** |
| `DESCO_GODOWN_ACCOUNT_NO` | `23108321` | 8-digit DESCO prepaid customer account number for **Godown** |

*(Optional DESCO settings supported via `config/services.php`: `DESCO_BASE_URL` defaults to `https://prepaid.desco.org.bd/api/unified/customer`, and `DESCO_LOW_BALANCE_THRESHOLD` defaults to `100.00`.)*

---

## 🚀 Installation & Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/mishajib/balance-bot.git
   cd balance-bot
   ```

2. **Install PHP & Node Dependencies**:
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configure Environment File**:
   ```bash
   cp .env.example .env
   ```
   *Edit `.env` and insert your Telegram Bot credentials and DESCO account numbers.*

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations**:
   ```bash
   php artisan migrate
   ```

6. **Set Telegram Webhook**:
   Run the Artisan command to register your webhook with Telegram:
   ```bash
   php artisan telegram:set-webhook
   ```

---

## 🛠️ Usage & Commands

### 🤖 Telegram Chat Commands
Send these commands directly to your Telegram Bot in chat:

- `/start` - Initialize bot conversation.
- `/help` - View list of all available commands.
- `/home_balance` - Fetch real-time balance & meter stats for the Home account.
- `/godown_balance` - Fetch real-time balance & meter stats for the Godown account.
- `/info` - Display PHP, Laravel, and server environment information.
- `/dev_info` - Display developer information.
- `/user` - View your Telegram user details.
- `/time` - Display current server time.

### 💻 Artisan Console Commands

#### Send Balance Info to Telegram Chat
Fetch DESCO balance and send a formatted message to your configured `TELEGRAM_CHAT_ID`:
```bash
# Send Home balance report
php artisan telegram:send-balance-info --accountNo=23108921 --type=home

# Send Godown balance report
php artisan telegram:send-balance-info --accountNo=23108321 --type=godown
```

#### Webhook Management Commands
```bash
# Set Telegram Webhook
php artisan telegram:set-webhook

# Check Webhook Status
php artisan telegram:get-webhook-info

# Remove Webhook
php artisan telegram:remove-webhook
```

---

## 🌐 Web Routes

| Method | Endpoint | Protection | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/telegram/webhook` | Public | Receives incoming messages and commands from Telegram |
| `GET` | `/telegram/set-webhook` | Auth | Registers the webhook URL with Telegram |
| `GET` | `/telegram/webhook-info` | Auth | Returns current webhook status JSON |
| `GET` | `/telegram/remove-webhook` | Auth | Removes the configured Telegram webhook |

---

## 📄 License

The BalanceBot application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
