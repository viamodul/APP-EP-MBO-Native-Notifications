# About this app

This is a Laravel-based ePages webhook notification system that acts as an order polling and webhook relay service. Here's what the app does:

## Core Functionality:
  - Polls ePages shops for new orders via their REST API
  - Sends webhook notifications when new orders are detected
  - Manages multiple shops with individual polling intervals and API credentials

## Key Components:

  1. Shop Management (app/Models/Shop.php):
    - Stores shop configuration (URL, API tokens, polling intervals)
    - Encrypts API tokens for security
    - Tracks last polling times and processed orders
  2. Order Polling (app/Jobs/PollShopOrders.php):
    - Background job that fetches new orders from ePages API
    - Compares with last processed order date to avoid duplicates
    - Queued job system for scalability
  3. Webhook Delivery (app/Services/WebhookService.php):
    - Transforms ePages order data into standardized webhook payload
    - Delivers webhooks to configured endpoint with retry logic
    - Logs all webhook attempts with status tracking
  4. ePages Integration (app/Services/EpagesApiService.php):
    - Handles authentication with Bearer tokens
    - Fetches orders with date filtering and pagination
    - Error handling and logging for API failures
  5. Automation (app/Console/Commands/PollAllShops.php):
    - Command to poll all active shops (can run via cron)
    - Supports both sync and async execution modes

  Use Case:
  This app bridges ePages e-commerce platforms with external systems that need real-time order notifications, essentially creating a webhook system for ePages stores that
  may not have native webhook capabilities.


  ## Working sample
  Reset shop to allow request already done orders. On the shop, last_order_check and last_processed_order_date to null. Can remove entries from webhook_logs.

  We have already a shop inside the table shops.
  We have a queue running: php artisan queue:work  
  We execite the job that verifies shops and send them to the queue: php artisan shops:poll  

  When execute, for each new order call the webhook:
  WEBHOOK_URL=https://webhook.site/76056518-e842-41c2-8dd4-92cd9f52552f

## Run date from when get orders

On first poll (when `last_processed_order_date` is NULL), you can control whether to fetch historical orders or start from now.

**Environment variable:**
```bash
# false = fetch all historical orders (development/testing)
# true  = skip historical orders, start from now (production)
POLL_SKIP_HISTORICAL_ORDERS=false
```

**Behavior:**

| Scenario | `POLL_SKIP_HISTORICAL_ORDERS` | Result |
|----------|-------------------------------|--------|
| First run (dev) | `false` | Fetches all historical orders |
| First run (prod) | `true` | Ignores historical, starts from now |
| Subsequent runs | (ignored) | Uses `last_processed_order_date` |

**Files involved:**
- `.env` - environment variable
- `config/services.php` - epages configuration
- `app/Jobs/PollShopOrders.php` - skip logic implementation

        

## TODO
- on webhook_logs set a limit of time the orders are stored.
