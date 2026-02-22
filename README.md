# Describr

AI-powered product description generator. Paste product URLs, and Describr scrapes their content and generates polished descriptions using **OpenAI** or **Anthropic**.

## Tech Stack

| Layer     | Technology                                          |
|-----------|-----------------------------------------------------|
| Backend   | Laravel 12, PHP 8.2+                                |
| Frontend  | React 18, TypeScript, Inertia.js                    |
| Styling   | Tailwind CSS 3                                      |
| Database  | PostgreSQL (default) / MySQL                        |
| Queue     | Database driver (configurable)                      |
| Auth      | Laravel Breeze                                      |
| AI        | OpenAI, Anthropic (selectable per product)          |
| Scraping  | Symfony DomCrawler + CSS Selector                   |
| Testing   | Pest                                                |

## Features

- **Multi-URL scraping** — submit multiple product links; each is scraped in the background via queued jobs
- **AI provider selection** — choose between OpenAI and Anthropic per product
- **Background processing** — scraping and description generation run asynchronously through Laravel's queue
- **Status tracking** — products move through `pending → scraping → generating → completed / failed`
- **Description history** — every generated description is stored, allowing regeneration over time
- **User dashboard** — view products, stats, and generated descriptions via an Inertia/React SPA

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- PostgreSQL or MySQL
- An **OpenAI** and/or **Anthropic** API key

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

OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
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
| `queue:listen` | Queue worker for background jobs |
| `pail`        | Real-time log tailing          |
| `vite`        | Frontend dev server with HMR   |

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

The container runs **Nginx + PHP-FPM + Queue Worker** via Supervisor. On startup the entrypoint script automatically runs migrations and caches config/routes/views.

## Project Structure

```
app/
├── DTOs/                   # Data Transfer Objects (e.g. ScrapedData)
├── Exceptions/             # Custom exceptions
├── Http/Controllers/       # Product & Profile controllers
├── Interfaces/             # AIProviderInterface, ScraperInterface
├── Jobs/                   # ScrapeProduct, ScrapeProductLink, GenerateProductDescription
├── Models/                 # User, Product, ProductLink, GeneratedDescription
├── Services/
│   ├── AIProviders/        # OpenAIProvider, AnthropicProvider, AIProviderFactory
│   ├── Scrapers/           # DomCrawlerScraper
│   ├── AIProviderService   # Orchestrates prompt building & AI calls
│   └── ScrapingService     # Orchestrates URL scraping
└── Providers/
resources/
├── js/                     # React/TypeScript pages & components
└── prompts/                # AI prompt templates
docker/
├── Dockerfile
├── entrypoint.sh
├── nginx.conf
└── supervisord.conf
```

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
