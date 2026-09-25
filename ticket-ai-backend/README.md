# Ticket AI Backend

Laravel API for storing, listing, and AI-analyzing support tickets.

## Stack

- PHP 8.3+
- Laravel 13
- SQLite
- Gemini via an OpenAI-compatible API

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- Gemini API key

## Setup

From `ticket-ai-backend`:

```bash
composer run setup
```

Configure the LLM in `.env`:

```env
LLM_PROVIDER=gemini
GEMINI_API_KEY=your-api-key
GEMINI_MODEL=gemini-2.0-flash
```

## Development

Start the Laravel app, queue worker, and Vite server with:

```bash
composer run dev
```

The API is available at `http://localhost:8000`.

## API

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/tickets` | List tickets with optional category and priority filters |
| `POST` | `/api/tickets` | Create and analyze a ticket |
| `POST` | `/api/tickets/{ticket}/reanalyze` | Reanalyze an existing ticket |

## Tests and checks

```bash
composer run lint:check
composer run types:check
php artisan test
composer run ci:check
```
