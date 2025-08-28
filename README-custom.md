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