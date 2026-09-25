# AI Ticket Resumer

Aplicação full-stack para criação, análise e gestão de tickets de suporte com recurso a Inteligência Artificial.

O projeto está dividido em duas aplicações independentes:

- **Backend**: API desenvolvida com Laravel, responsável pela persistência dos tickets e pela análise através de um modelo Gemini.
- **Frontend**: interface desenvolvida com Vue, responsável pela criação, consulta, filtragem e reanálise dos tickets.

Cada aplicação possui o seu próprio README com detalhes específicos. Este documento apresenta a visão geral do projeto e os passos necessários para executar os dois componentes em conjunto.

## Estrutura do projeto

```text
ai-ticket-resumer/
├── ticket-ai-backend/     # API Laravel e integração com IA
├── ticket-ai-frontend/    # Interface Vue + TypeScript
└── README.md              # Documentação geral do projeto
```

## Funcionalidades principais

- Criação de tickets de suporte.
- Análise automática dos tickets com Inteligência Artificial.
- Identificação ou classificação por categoria e prioridade.
- Listagem dos tickets existentes.
- Filtragem de tickets por categoria e prioridade.
- Reanálise de tickets já existentes.
- Comunicação entre o frontend Vue e a API Laravel através de endpoints HTTP.

## Tecnologias utilizadas

### Backend

- PHP 8.3+
- Laravel 13
- SQLite
- Composer
- Gemini através de uma API compatível com OpenAI
- Pest para testes
- Laravel Pint e PHPStan para qualidade e análise estática

### Frontend

- Vue 3
- TypeScript
- Vite
- Tailwind CSS 4
- npm

## Requisitos

Antes de começar, certifique-se de que tem instalado:

- PHP 8.3 ou superior
- Composer
- Node.js 20 ou superior
- npm
- Uma chave de API do Gemini

## Instalação

### 1. Clonar o repositório

```bash
git clone https://github.com/Dacoffe/ai-ticket-resumer.git
cd ai-ticket-resumer
```

### 2. Configurar o backend

Entre na pasta do backend e execute o setup automático:

```bash
cd ticket-ai-backend
composer run setup
```

Este comando instala as dependências, cria o ficheiro `.env`, gera a chave da aplicação, executa as migrations, instala as dependências JavaScript e gera os assets.

Configure as variáveis relacionadas com a IA no ficheiro `ticket-ai-backend/.env`:

```env
LLM_PROVIDER=gemini
GEMINI_API_KEY=your-api-key
GEMINI_MODEL=gemini-2.0-flash
```

Por predefinição, o backend utiliza SQLite como base de dados.

### 3. Configurar o frontend

Numa nova janela do terminal, entre na pasta do frontend e instale as dependências:

```bash
cd ticket-ai-frontend
npm install
```

O frontend está configurado para comunicar com o backend em `http://localhost:8000`. Se for necessário utilizar outro endereço, crie o ficheiro `ticket-ai-frontend/.env`:

```env
VITE_API_URL=http://localhost:8000
```

## Executar em desenvolvimento

O backend e o frontend devem ser executados separadamente.

### Backend

A partir de `ticket-ai-backend`:

```bash
composer run dev
```

O backend ficará disponível em:

```text
http://localhost:8000
```

### Frontend

A partir de `ticket-ai-frontend`:

```bash
npm run dev
```

A interface ficará disponível em:

```text
http://localhost:5173
```

O servidor de desenvolvimento do Vite encaminha os pedidos `/api` para o backend Laravel.

## Fluxo da aplicação

1. O utilizador acede à interface Vue através do frontend.
2. O utilizador cria um novo ticket de suporte.
3. O frontend envia o ticket para a API Laravel.
4. O backend guarda o ticket e solicita uma análise ao modelo Gemini.
5. A análise devolve informação como categoria, prioridade e resumo.
6. O ticket analisado é apresentado na interface.
7. O utilizador pode filtrar os tickets ou solicitar uma nova análise de um ticket existente.

## API disponível

| Método | Endpoint | Descrição |
| --- | --- | --- |
| `GET` | `/api/tickets` | Lista tickets, com filtros opcionais por categoria e prioridade. |
| `POST` | `/api/tickets` | Cria e analisa um novo ticket. |
| `POST` | `/api/tickets/{ticket}/reanalyze` | Executa novamente a análise de um ticket existente. |

## Comandos úteis

### Backend

```bash
cd ticket-ai-backend

# Iniciar a aplicação em desenvolvimento
composer run dev

# Verificar formatação do código
composer run lint:check

# Executar análise estática
composer run types:check

# Executar os testes
php artisan test

# Executar todas as verificações de CI
composer run ci:check
```

### Frontend

```bash
cd ticket-ai-frontend

# Iniciar o servidor de desenvolvimento
npm run dev

# Validar tipos e gerar a build de produção
npm run build

# Pré-visualizar a build
npm run preview
```

## Build de produção

### Frontend

```bash
cd ticket-ai-frontend
npm run build
```

Os ficheiros gerados serão colocados na pasta `dist`.

### Backend

Para preparar o backend para produção, configure corretamente o ambiente Laravel, a base de dados, a chave da API do Gemini e as variáveis de cache, sessão e filas. Consulte o README em `ticket-ai-backend/` para os detalhes específicos da API.

## Documentação específica

- [Documentação do backend](ticket-ai-backend/README.md)
- [Documentação do frontend](ticket-ai-frontend/README.md)

## Variáveis de ambiente importantes

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

Não versionar ficheiros `.env` nem chaves de API. Estes ficheiros já estão excluídos pelo `.gitignore`.

## Testes e qualidade

Antes de submeter alterações, recomenda-se executar as verificações do backend:

```bash
cd ticket-ai-backend
composer run ci:check
```

E validar a build do frontend:

```bash
cd ticket-ai-frontend
npm run build
```

## Licença

Este projeto utiliza a licença definida na aplicação backend. Consulte os ficheiros do projeto para obter mais informações.
