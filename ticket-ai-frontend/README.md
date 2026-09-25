# Ticket AI Frontend

Vue interface for creating, reviewing, filtering, and reanalyzing support
tickets.

## Stack

- Vue 3 with `<script setup>`
- TypeScript
- Vite
- Tailwind CSS 4

## Requirements

- Node.js 20+
- npm
- Laravel backend running at `http://localhost:8000`

## Setup

From `ticket-ai-frontend`:

```bash
npm install
```

The Vite development server proxies `/api` requests to the backend. To use a
different API URL, create a `.env` file:

```env
VITE_API_URL=http://localhost:8000
```

## Development

```bash
npm run dev
```

The app is available at `http://localhost:5173`.

## Build

```bash
npm run build
npm run preview
```
