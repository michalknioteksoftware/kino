# Delete a cinema room by id (fails with 422 if room has reservations)
# Usage: .\delete.ps1 -Id <id> [JWT_TOKEN]
param(
    [Parameter(Mandatory=$true)][int]$Id,
    [string]$Token = $env:JWT_TOKEN
)
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
try {
    $r = Invoke-WebRequest -Uri "$BaseUrl/api/cinema-rooms/$Id" -Method Delete -Headers $headers -UseBasicParsing
    Write-Host "HTTP $($r.StatusCode)"
} catch {
    Write-Host "HTTP $($_.Exception.Response.StatusCode.value__)"
    if ($_.ErrorDetails.Message) { Write-Host $_.ErrorDetails.Message }
}
