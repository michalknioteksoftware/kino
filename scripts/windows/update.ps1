# Update a cinema room by id
# Usage: .\update.ps1 -Id <id> [JWT_TOKEN]
# Optional: $env:BODY = '{"rows":6,"columns":12,"movie":"Updated","movieDatetime":"2025-12-01T21:00:00Z"}'
param(
    [Parameter(Mandatory=$true)][int]$Id,
    [string]$Token = $env:JWT_TOKEN
)
$BaseUrl = if ($env:BASE_URL) { $env:BASE_URL } else { "http://localhost:8000" }
$Body = if ($env:BODY) { $env:BODY } else { '{"rows":5,"columns":10,"movie":"Updated Movie","movieDatetime":"2025-12-01T21:00:00+00:00"}' }
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
Invoke-RestMethod -Uri "$BaseUrl/api/cinema-rooms/$Id" -Method Put -Headers $headers -Body $Body | ConvertTo-Json -Depth 10
