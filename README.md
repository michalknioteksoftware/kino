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
  -d '{"rows":5,"columns":10,"movie":"Example Movie","movieDatetime":"2026-10-01T20:00:00+00:00"}' \
  http://localhost:8000/api/cinema-rooms
```

### Public: cinema rooms list (no JWT)

Returns all cinema rooms with reserved seats (row, column, and name of person who reserved).

| Method | Path                         | Description |
|--------|------------------------------|-------------|
| GET    | `/api/public/cinema-rooms`   | List all rooms with reserved seats (no auth) |

Example response shape: `{ "data": [ { "id", "rows", "columns", "movie", "movieDatetime", "reservedSeats": [ { "row", "column", "reservedByName" }, ... ] }, ... ] }`.

```bash
curl http://localhost:8000/api/public/cinema-rooms
```

### Public: cinema room reservation (no JWT)

Reserve one or more seats in a cinema room. All seats are reserved in a single transaction; if any seat is invalid or already taken, the whole request fails (422) and nothing is saved.

| Method | Path                  | Description |
|--------|-----------------------|-------------|
| POST   | `/api/reservations`   | Create reservations (no auth) |

**Request body (JSON):**

- `cinemaRoomId` (integer, required) – room ID
- `reservedByName` (string, required, non-empty) – name of the person who reserves
- `seats` (array, required) – at least one seat: `{ "row": 1, "column": 2 }` (row/column within room limits)

**Validations:** Room must exist; seats must be within room rows/columns; seats must not be already reserved; reservation time must not be after the room’s `movieDatetime`. Duplicate seats in the same request are rejected.

```bash
curl -X POST http://localhost:8000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{"cinemaRoomId":1,"reservedByName":"John Doe","seats":[{"row":1,"column":2},{"row":1,"column":3}]}'
```

**Windows scripts:** Use `scripts\windows\reserve.bat` or `reserve.ps1` (see script comments for usage; e.g. `reserve.bat 1 "John Doe" 1,2 1,3`).

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
