# Describr

AI-powered product description generator for e-commerce teams. Paste product URLs, let Describr scrape the sources in the background, generate polished descriptions with AI, translate them with DeepL, export them to PDF, and monitor the whole async pipeline through Redis + Horizon.

## Tech Stack

| Layer     | Technology                                          |
|-----------|-----------------------------------------------------|
| Backend   | Laravel 12, PHP 8.2+                                |
| Frontend  | React 18, TypeScript, Inertia.js                    |
| Styling   | Tailwind CSS 3                                      |
| Database  | PostgreSQL (default) / MySQL                        |
| Queue     | Redis-backed Laravel queues + Horizon               |
| Cache     | Redis                                               |
| Auth      | Laravel Breeze                                      |
| AI        | OpenAI, Anthropic (selectable per product)          |
| Translation | DeepL API                                         |
| Export    | DomPDF                                              |
| Scraping  | Symfony DomCrawler + CSS Selector                   |
| Testing   | Pest                                                |

## Features

- **Multi-URL scraping** — submit multiple product links; each is scraped asynchronously via queued jobs
- **AI provider selection** — choose between OpenAI and Anthropic per product
- **Event-driven workflow** — `ProductScraped`, `DescriptionGenerated`, and `DescriptionFailed` listeners keep the pipeline decoupled
- **DeepL translation flow** — translate the latest generated description through a dedicated HTTP client + service layer
- **PDF export** — export both the original description and completed translations to PDF
- **HMAC-secured API** — create products and fetch descriptions through signed API endpoints scoped to API clients
- **Redis queues and caching** — jobs run through Redis; DeepL metadata and dashboard stats are cached in Redis
- **Horizon observability** — monitor queues, workers, throughput, tags, failures, and workload at `/horizon`
- **Status tracking** — products move through `pending → scraping → generating → completed / failed`
- **Description history** — generated descriptions and translations are persisted for later review/export
- **User dashboard** — view products, stats, generated descriptions, translations, and scraped images via an Inertia/React SPA

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- PostgreSQL or MySQL
- Redis
- An **OpenAI** and/or **Anthropic** API key
- A **DeepL** API key

## Getting Started

### 1. Clone & install

```bash
git clone <repo-url> && cd product-description-maker
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:

```dotenv
DB_CONNECTION=pgsql          # or mysql
DB_DATABASE=product_description_maker
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
DEEPL_API_KEY=your-deepl-key
```

### 3. Run migrations & seed

```bash
php artisan migrate
php artisan db:seed          # optional — seeds sample data
```

### 4. Start development

The project ships with a single `composer dev` command that starts everything concurrently:

```bash
composer dev
```

This launches:

| Process       | Description                    |
|---------------|--------------------------------|
| `serve`       | PHP development server         |
| `horizon`     | Redis-backed queue workers + Horizon dashboard |
| `pail`        | Real-time log tailing          |
| `vite`        | Frontend dev server with HMR   |

After startup, Horizon is available at:

```bash
http://127.0.0.1:8000/horizon
```

The dashboard is protected by normal app authentication.

## API

Describr exposes HMAC-protected API endpoints for machine-to-machine use:

- `POST /api/products`
- `GET /api/products/{product}/description`

Requests must include:

- `X-Describr-Client`
- `X-Describr-Timestamp`
- `X-Describr-Signature`

API clients are scoped to a specific user, so one client cannot access another user's products or descriptions.

## Queue / Cache / Horizon

- Redis is used as the queue backend for:
  - `ScrapeProduct`
  - `ScrapeProductLink`
  - `GenerateProductDescription`
  - `TranslateGeneratedDescription`
- Redis is also used to cache:
  - DeepL usage responses
  - DeepL language metadata
  - homepage product stats
- Horizon manages the actual workers and exposes queue observability at `/horizon`
- Horizon tags are attached to important jobs, including:
  - `product:{id}`
  - `provider:{name}`
  - `pipeline:scrape|generate|translate`
  - `translation:{id}`
  - `language:{code}`

## Testing

```bash
composer test
```

Runs Pest across both `tests/Feature` and `tests/Unit`.

## Docker

A production-ready Docker setup lives in `docker/`:

```bash
docker build -f docker/Dockerfile -t describr .
docker run -p 80:80 --env-file .env describr
```

The container runs **Nginx + PHP-FPM + Horizon** via Supervisor. On startup the entrypoint script automatically runs migrations and caches config/routes/views.

Because the application now uses Redis queues, the container expects an accessible Redis service via the configured `REDIS_HOST`.

## Project Structure

```
app/
├── DTOs/                   # Data Transfer Objects (e.g. ScrapedData)
├── Exceptions/             # Custom exceptions
├── Http/Controllers/       # Web, API, image, translation, and PDF controllers
├── Interfaces/             # AIProviderInterface, ScraperInterface
├── Jobs/                   # ScrapeProduct, ScrapeProductLink, GenerateProductDescription, TranslateGeneratedDescription
├── Models/                 # User, Product, ProductLink, GeneratedDescription, DescriptionTranslation, ApiClient
├── Services/
│   ├── AIProviders/        # OpenAIProvider, AnthropicProvider, AIProviderFactory
│   ├── Network/            # DeepL HTTP client
│   ├── Scrapers/           # DomCrawlerScraper
│   ├── Translations/       # DeepL translation service
│   ├── AIProviderService   # Orchestrates prompt building & AI calls
│   ├── ProductDescriptionPdfService
│   ├── ProductImageDownloadService
│   └── ScrapingService     # Orchestrates URL scraping
└── Providers/              # App provider + Horizon provider
resources/
├── js/                     # React/TypeScript pages & components
├── prompts/                # AI prompt templates
└── views/pdf/              # PDF export template
docker/
├── Dockerfile
├── entrypoint.sh
├── nginx.conf
└── supervisord.conf        # Runs Horizon inside the container
```

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
