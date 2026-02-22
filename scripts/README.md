# Manual scripts for Cinema Rooms CRUD

Scripts call the cinema rooms API. **If `JWT_TOKEN` is not set, they generate one via the CLI** (`docker compose exec app php bin/console app:jwt:generate`) and use the last line of output as the token. Docker must be running from the project root.

- **curl/** – Bash scripts for Linux/macOS (use `curl`; optional `jq` for pretty JSON).
- **windows/** – PowerShell scripts and .bat wrappers for Windows.

## Environment (optional)

- `JWT_TOKEN` – Bearer token; if unset, generated via CLI (requires Docker from project root).
- `BASE_URL` – API base URL (default: `http://localhost:8000`).
- `BODY` – JSON body for create/update (see script defaults).

---

## curl (Linux / macOS)

From project root (or anywhere; scripts resolve project root for token generation):

```bash
./scripts/curl/list.sh
./scripts/curl/show.sh 1
./scripts/curl/create.sh
BODY='{"rows":8,"columns":12,"movie":"New Movie","movieDatetime":"2025-12-15T19:00:00+00:00"}' ./scripts/curl/create.sh
./scripts/curl/update.sh 1
./scripts/curl/delete.sh 1
```

Optionally set `JWT_TOKEN` or pass it as the last argument to skip CLI token generation. If `jq` is not installed, output is still printed (scripts use `jq .` when available).

---

## Windows (PowerShell or .bat)

**Option A – use the .bat wrappers** (no execution policy change; token auto-generated if not set):

```cmd
scripts\windows\list.bat
scripts\windows\show.bat 1
scripts\windows\create.bat
scripts\windows\update.bat 1
scripts\windows\delete.bat 1
```

Run from project root so Docker can generate the token. The `.bat` files run the PowerShell scripts with `-ExecutionPolicy Bypass`.

**Option B – run the .ps1 scripts directly** (from `scripts\windows` or project root; token also auto-generated if not set):

```powershell
.\scripts\windows\list.ps1
.\scripts\windows\show.ps1 -Id 1
.\scripts\windows\create.ps1
.\scripts\windows\update.ps1 -Id 1
.\scripts\windows\delete.ps1 -Id 1
```

If you get “running scripts is disabled”, either use the `.bat` files above or allow scripts for your user (one-time):

```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```
