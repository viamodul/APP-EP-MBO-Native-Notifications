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

## NEXT
### Ver como ligar as várias aplicações
  Modelo de negócio:
    > Deve poder ser utilizado como aplicação isolada - qualquer cliente pode ligar à sua loja e definir o URL WebHook.
    > Deve poder ser utilizado por outras aplicações que não terão valor de subscrição - o registo da loja pela app externa é feito por API. Deve ter alguma assinatura ou validação (talvez só com um token se for para ser mais fácil)
### Ver se queremos passar tantos dados no payload do webhook
  Podem existir diferentes tipos de webhooks (estes níveis podem fazer parte de uma subscrição com diferentes custos):
    > Webhook com todos os dados do pedido
    > Webhook com dados resumidos do pedido
    > Webhook com dados personalizados do pedido [seleccionar os parâmetros que queremos enviar]

### Automatizar o tratamento das lojas
  * Lojas podem ser adicionadas por API;
  * Lojas podem ser adicionadas a partir da instalação da App Store;
  * Lojas podem ser removidas por API;
  * Lojas podem ser removidas através da remoção da própia conta;
  * Lojas podem ser desactivadas por API;
  * Lojas podem ser desactivadas através da remoção da própia conta;
  * Lojas podem ser reactivadas por API;
  * Lojas podem ser reactivadas através da reactivação da própia conta;
  * Lojas poderm ser desactivadas por admin
  * Lojas poderm ser desactivadas automaticamente, quando o endereço API da loja não existe mais ao fim de 5 tentativas - verificar a cada 24h durante 1 semana.
  * Lojas poderm ser reactivadas por admin manualmente

## Instalação através de App Store (Implementado)

A aplicação suporta dois cenários de utilização:

### Cenário 1: Aplicação Isolada (via App Store ePages)

O utilizador instala a app directamente da App Store ePages, cria uma conta e configura o webhook URL.

**Fluxo de instalação completo:**
```
ePages App Store
    ↓
GET /epages/register (callback com code + api_url)
    ↓
Cria EpagesShop (tokens) + Shop (polling)
    ↓
GET /epages/onboarding/register
    ↓
Utilizador cria conta (nome, email, password) + define webhook URL
    ↓
GET /epages/onboarding/success
    ↓
Dashboard (gestão de shops e webhooks)
```

### Cenário 2: Integração via API

Outra aplicação regista lojas via API REST. Não passa pelo fluxo de App Store.

**Autenticação da API:**
Todos os pedidos à API de gestão de lojas (`/api/v1/shops/*`) requerem um Bearer Token válido no cabeçalho `Authorization` para garantir que apenas aplicações autorizadas efetuam alterações.

Para gerar um novo token para uma aplicação externa, corre o seguinte comando no terminal:
```bash
php artisan app:generate-token "Nome da App Externa"
```
*Atenção: O token gerado será mostrado apenas uma vez. Guarda-o num local seguro.*

**Exemplo de Pedido:**
```bash
POST /api/v1/shops
Header: "Authorization: Bearer TEU_TOKEN_GERADO"
{
  "name": "Minha Loja",
  "shop_url": "https://loja.epages.com/rs/shops/loja",
  "api_token": "TOKEN",
  "webhook_url": "https://meu-sistema.com/webhook"
}
```

---

### Configuração do ePages Developer Portal

1. Criar app no [ePages Developer Portal](https://developer.epages.com)

2. Configurar os callbacks:
   - **Installation URL:** `https://teu-dominio.com/epages/register`
   - **Uninstall URL:** `https://teu-dominio.com/epages/unregister`

3. Configurar no `.env`:
```bash
EPAGES_CLIENT_ID=teu_client_id
EPAGES_CLIENT_SECRET=teu_client_secret
EPAGES_REDIRECT_URI="${APP_URL}/epages/callback"
EPAGES_SCOPES=read_orders
EPAGES_VERIFY_SIGNATURE=true
EPAGES_ENCRYPT_TOKENS=true
```

---

### Sistema de Autenticação (Laravel Breeze)

A aplicação utiliza Laravel Breeze para autenticação de utilizadores.

**Funcionalidades disponíveis:**
- Registo de conta durante onboarding
- Login com conta existente durante onboarding
- Login/Logout normal
- Recuperação de password
- Edição de perfil (nome, email, password)
- Eliminação de conta

---

### Dashboard e Gestão de Lojas

Após autenticação, o utilizador tem acesso ao dashboard para gerir as suas lojas.

**Funcionalidades do Dashboard:**

| Funcionalidade | Rota | Descrição |
|----------------|------|-----------|
| Dashboard | `GET /dashboard` | Lista todas as lojas do utilizador |
| Ver loja | `GET /shops/{id}` | Detalhes da loja e webhooks recentes |
| Editar loja | `GET /shops/{id}/edit` | Alterar webhook URL, intervalo, estado |
| Lista webhooks | `GET /shops/{id}/webhooks` | Todos os webhooks enviados (com filtros) |
| Detalhes webhook | `GET /shops/{id}/webhooks/{id}` | Payload, response, dados da ordem |
| Retry webhook | `POST /shops/{id}/webhooks/{id}/retry` | Reenviar webhook falhado |
| Perfil | `GET /profile` | Editar dados do utilizador |

**O que o utilizador pode fazer:**
- Ver todas as suas lojas conectadas
- Editar webhook URL de cada loja
- Alterar intervalo de polling (1-60 minutos)
- Activar/desactivar lojas
- Ver histórico completo de webhooks
- Filtrar webhooks por estado (sent, failed, pending)
- Ver detalhes de cada webhook (payload enviado, resposta recebida)
- Reenviar webhooks falhados (até 3 tentativas)
- Gerir o seu perfil (alterar password, eliminar conta)

---

### Rotas Completas

#### Rotas de Onboarding (sem autenticação)

| Rota | Método | Descrição |
|------|--------|-----------|
| `/epages/register` | GET | Callback de instalação (chamado pelo ePages) |
| `/epages/unregister` | DELETE | Callback de desinstalação |
| `/epages/onboarding/register` | GET | Form de registo de utilizador |
| `/epages/onboarding/register` | POST | Processar registo |
| `/epages/onboarding/login` | GET | Form de login (utilizador existente) |
| `/epages/onboarding/login` | POST | Processar login |
| `/epages/onboarding/success` | GET | Página de sucesso |

#### Rotas de Dashboard (com autenticação)

| Rota | Método | Descrição |
|------|--------|-----------|
| `/dashboard` | GET | Dashboard principal |
| `/shops/{id}` | GET | Ver detalhes da loja |
| `/shops/{id}/edit` | GET | Formulário de edição |
| `/shops/{id}` | PATCH | Guardar alterações |
| `/shops/{id}/webhooks` | GET | Lista de webhooks |
| `/shops/{id}/webhooks/{id}` | GET | Detalhes do webhook |
| `/shops/{id}/webhooks/{id}/retry` | POST | Reenviar webhook |

---

### Arquitectura de Modelos

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│      User       │────────>│      Shop       │────────>│   WebhookLog    │
│  (autenticação) │  hasMany│   (polling)     │  hasMany│   (histórico)   │
└─────────────────┘         └─────────────────┘         └─────────────────┘
                                    │
                                    │ belongsTo
                                    ▼
                            ┌─────────────────┐
                            │   EpagesShop    │
                            │  (instalação)   │
                            └─────────────────┘
```

**User** (tabela `users`):
- Autenticação via Laravel Breeze
- Pode ter múltiplas lojas

**EpagesShop** (tabela `epages_shops`):
- Guarda tokens de acesso (encriptados)
- Gerido pelo package `epages-integration`
- Criado automaticamente na instalação via App Store

**Shop** (tabela `shops`):
- Configuração de polling e webhook
- Pertence a um User
- Pode ter EpagesShop associado (instalação via App Store)
- Campo `source`: `appstore` ou `api`
- Campo `webhook_url`: URL específico por loja

**WebhookLog** (tabela `webhook_logs`):
- Histórico de todos os webhooks enviados
- Guarda payload, response, estado, tentativas

---

### Ficheiros Principais

```
app/
├── Http/Controllers/
│   ├── DashboardController.php      # Dashboard principal
│   ├── OnboardingController.php     # Fluxo de onboarding (registo/login)
│   ├── ShopSettingsController.php   # Gestão de lojas (frontend)
│   ├── WebhookLogController.php     # Gestão de webhooks (frontend)
│   ├── ShopController.php           # API REST de lojas
│   └── WebhookController.php        # API REST de webhooks
├── Models/
│   ├── User.php                     # Utilizador (hasMany shops)
│   ├── Shop.php                     # Loja (belongsTo user, hasMany webhookLogs)
│   └── WebhookLog.php               # Log de webhook
├── Services/
│   ├── EpagesApiService.php         # Integração com API ePages
│   └── WebhookService.php           # Envio de webhooks
└── Jobs/
    └── PollShopOrders.php           # Job de polling

packages/
└── epages-integration/              # Package de integração App Store
    └── src/
        ├── Http/Controllers/
        │   ├── InstallController.php    # Callback de instalação
        │   └── OAuthController.php      # OAuth flow
        └── Models/
            └── EpagesShop.php           # Modelo de instalação

resources/views/
├── dashboard.blade.php              # Dashboard
├── onboarding/
│   ├── register.blade.php           # Form de registo
│   ├── login.blade.php              # Form de login
│   └── success.blade.php            # Página de sucesso
└── shops/
    ├── show.blade.php               # Detalhes da loja
    ├── edit.blade.php               # Editar loja
    └── webhooks/
        ├── index.blade.php          # Lista de webhooks
        └── show.blade.php           # Detalhes do webhook
```







