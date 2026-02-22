# List all cinema rooms
# Usage: .\list.ps1 [JWT_TOKEN]  (token auto-generated via CLI if not set)
param([string]$Token = $env:JWT_TOKEN)
$BaseUrl = if ($env:BASE_URL) { $env:BASE_URL } else { "http://localhost:8000" }
if (-not $Token) {
  $scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
  $projectRoot = (Resolve-Path (Join-Path $scriptDir "..\..")).Path
  $output = & { Set-Location $projectRoot; docker compose exec -T app php bin/console app:jwt:generate 2>$null }
  $Token = ($output | Select-Object -Last 1)
  if (-not $Token) { Write-Host "Failed to get token. Is Docker running?"; exit 1 }
}
$headers = @{
    "Authorization" = "Bearer $Token"
    "Content-Type"  = "application/json"
}
Invoke-RestMethod -Uri "$BaseUrl/api/cinema-rooms" -Method Get -Headers $headers | ConvertTo-Json -Depth 10
