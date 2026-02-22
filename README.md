# Kino REST API

REST API boilerplate built with **PHP 8.4**, **Symfony 7.2**, and **MySQL 8.0**. Dockerized and ready to run with Docker Compose. Uses PHPUnit for tests. No API Platform.

## Requirements

- Docker & Docker Compose
- (Optional) Composer locally for dev without Docker

## Quick start

### 1. Start the stack

```bash
docker compose up --build -d
```

On first run, the app container will install Composer dependencies and then start the built-in PHP server. The API will be available at **http://localhost:8000**.

**If you get "Connection refused"**: Check that the app container is running (`docker compose ps`). If the app container is restarting or exited, view logs: `docker compose logs app`. Fix any `composer install` or PHP errors shown there, then run `docker compose up -d` again.

### 2. Run database migrations (when you add entities)

```bash
docker compose exec app php bin/console doctrine:migrations:diff
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Or create the schema from entities:

```bash
docker compose exec app php bin/console doctrine:schema:update --force
```

### 3. Run tests

```bash
docker compose exec app php bin/phpunit
```

Or with coverage:

```bash
docker compose exec app php bin/phpunit --coverage-html var/coverage
```

## JWT token (CLI)

Generate a JWT for testing or API auth:

```bash
# Default: 60 min expiry, uses APP_SECRET or JWT_SECRET from .env
docker compose exec app php bin/console app:jwt:generate

# Custom expiry (minutes), subject, and extra claims
docker compose exec app php bin/console app:jwt:generate --exp=1440 --sub=user123 --payload='{"role":"admin"}'

# Custom secret
docker compose exec app php bin/console app:jwt:generate --secret=my-secret-key
```

Options: `--secret` / `-s`, `--exp` (minutes), `--sub`, `--payload` / `-p` (JSON), `--alg` (default HS256).

## API endpoints

| Method | Path           | Description   |
|--------|----------------|---------------|
| GET    | `/api/health`  | Health check (no auth) |

Example:

```bash
curl http://localhost:8000/api/health
```

### Cinema rooms (JWT required)

All cinema room endpoints require a valid JWT in the header: `Authorization: Bearer <token>`. Use the CLI to generate a token (see above).

| Method | Path                     | Description |
|--------|--------------------------|-------------|
| GET    | `/api/cinema-rooms`      | List all   |
| GET    | `/api/cinema-rooms/{id}` | Get one    |
| POST   | `/api/cinema-rooms`      | Create (JSON body: rows, columns, movie, movieDatetime required) |
| PUT    | `/api/cinema-rooms/{id}` | Update (JSON body; rows/columns validated against existing reservations) |
| DELETE | `/api/cinema-rooms/{id}` | Delete (fails with 422 if room has reservations) |

Request/response format: JSON. Example:

```bash
TOKEN=$(docker compose exec app php bin/console app:jwt:generate --no-ansi 2>/dev/null | tail -1)
curl -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"rows":5,"columns":10,"movie":"Example Movie"}' \
  http://localhost:8000/api/cinema-rooms
```

## Project structure

```
├── config/           # Symfony config (packages, routes)
├── public/           # Web root (index.php)
├── src/
│   ├── Controller/   # REST API controllers (e.g. Api/)
│   ├── Entity/       # Doctrine entities
│   └── Kernel.php
├── tests/            # PHPUnit tests
├── docker-compose.yml
├── Dockerfile        # PHP 8.4 + extensions
├── composer.json
└── phpunit.xml.dist
```

## Stack

- **PHP**: 8.4 (Alpine, CLI)
- **Symfony**: 7.2 (Framework, Doctrine, Serializer, Validator)
- **MySQL**: 8.0
- **PHPUnit**: 11.x
- **Composer**: 2

## Environment

- `.env` – local/env config; `DATABASE_URL` is set for Docker (service name `db`).
- For production, set `APP_SECRET` and use a real database URL.

## Development without Docker

1. Install PHP 8.4, Composer, MySQL 8.
2. Run `composer install`.
3. Create DB: `mysql -u root -p -e "CREATE DATABASE kino;"` and set `DATABASE_URL` in `.env`.
4. Start server: `php -S localhost:8000 -t public`.
5. Run tests: `php bin/phpunit`.

## License

Proprietary.
