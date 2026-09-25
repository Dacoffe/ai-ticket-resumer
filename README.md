# AI Ticket Resumer

A full-stack application for creating, analyzing, and managing support tickets with the help of Artificial Intelligence.

The project is divided into two independent applications:

- **Backend**: a Laravel API responsible for ticket persistence and AI-powered analysis using a Gemini model.
- **Frontend**: a Vue interface for creating, reviewing, filtering, and reanalyzing tickets.

Each application has its own README with more specific information. This document provides an overview of the project and explains how to run both components together.

## Project structure

```text
ai-ticket-resumer/
├── ticket-ai-backend/     # Laravel API and AI integration
├── ticket-ai-frontend/    # Vue + TypeScript interface
└── README.md              # General project documentation
```

## Main features

- Create support tickets.
- Automatically analyze tickets using Artificial Intelligence.
- Classify tickets by category and priority.
- List existing tickets.
- Filter tickets by category and priority.
- Reanalyze existing tickets.
- Connect the Vue frontend to the Laravel API through HTTP endpoints.

## Technologies

### Backend

- PHP 8.3+
- Laravel 13
- SQLite
- Composer
- Gemini through an OpenAI-compatible API
- Pest for testing
- Laravel Pint and PHPStan for code quality and static analysis

### Frontend

- Vue 3
- TypeScript
- Vite
- Tailwind CSS 4
- npm

## Requirements

Before getting started, make sure you have the following installed:

- PHP 8.3 or later
- Composer
- Node.js 20 or later
- npm
- A Gemini API key

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/Dacoffe/ai-ticket-resumer.git
cd ai-ticket-resumer
```

### 2. Set up the backend

Go to the backend directory and run the setup command:

```bash
cd ticket-ai-backend
composer run setup
```

This command installs the dependencies, creates the `.env` file, generates the application key, runs the database migrations, installs JavaScript dependencies, and builds the assets.

Configure the AI-related variables in `ticket-ai-backend/.env`:

```env
LLM_PROVIDER=gemini
GEMINI_API_KEY=your-api-key
GEMINI_MODEL=gemini-2.0-flash
```

By default, the backend uses SQLite as its database.

### 3. Set up the frontend

In a new terminal window, go to the frontend directory and install the dependencies:

```bash
cd ticket-ai-frontend
npm install
```

The frontend is configured to communicate with the backend at `http://localhost:8000`. If you need to use a different API URL, create `ticket-ai-frontend/.env`:

```env
VITE_API_URL=http://localhost:8000
```

## Running the application in development

The backend and frontend must be started separately.

### Backend

From `ticket-ai-backend`:

```bash
composer run dev
```

The backend will be available at:

```text
http://localhost:8000
```

### Frontend

From `ticket-ai-frontend`:

```bash
npm run dev
```

The frontend will be available at:

```text
http://localhost:5173
```

The Vite development server proxies `/api` requests to the Laravel backend.

## Application flow

1. The user accesses the Vue frontend.
2. The user creates a new support ticket.
3. The frontend sends the ticket to the Laravel API.
4. The backend stores the ticket and sends it to the Gemini model for analysis.
5. The analysis returns information such as the category, priority, and summary.
6. The analyzed ticket is displayed in the interface.
7. The user can filter tickets or request a new analysis for an existing ticket.

## API endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/tickets` | Lists tickets with optional category and priority filters. |
| `POST` | `/api/tickets` | Creates and analyzes a new ticket. |
| `POST` | `/api/tickets/{ticket}/reanalyze` | Reanalyzes an existing ticket. |

## Useful commands

### Backend

```bash
cd ticket-ai-backend

# Start the application in development mode
composer run dev

# Check code formatting
composer run lint:check

# Run static analysis
composer run types:check

# Run the tests
php artisan test

# Run all CI checks
composer run ci:check
```

### Frontend

```bash
cd ticket-ai-frontend

# Start the development server
npm run dev

# Validate types and create a production build
npm run build

# Preview the production build
npm run preview
```

## Production build

### Frontend

```bash
cd ticket-ai-frontend
npm run build
```

The generated files will be placed in the `dist` directory.

### Backend

To prepare the backend for production, configure the Laravel environment, database, Gemini API key, and cache, session, and queue settings correctly. See the README in `ticket-ai-backend/` for backend-specific details.

## Component documentation

- [Backend documentation](ticket-ai-backend/README.md)
- [Frontend documentation](ticket-ai-frontend/README.md)

## Important environment variables

### Backend

```env
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
LLM_PROVIDER=gemini
GEMINI_API_KEY=your-api-key
GEMINI_MODEL=gemini-2.0-flash
```

### Frontend

```env
VITE_API_URL=http://localhost:8000
```

Do not commit `.env` files or API keys. These files are excluded by `.gitignore`.

## Testing and code quality

Before submitting changes, run the backend checks:

```bash
cd ticket-ai-backend
composer run ci:check
```

Then validate the frontend build:

```bash
cd ticket-ai-frontend
npm run build
```

## License

This project uses the license defined by the backend application. Check the project files for more information.
