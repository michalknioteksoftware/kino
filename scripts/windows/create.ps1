# Create a cinema room
# Usage: .\create.ps1 [JWT_TOKEN]
# Optional: $env:BODY = '{"rows":5,"columns":10,"movie":"My Movie","movieDatetime":"2025-12-01T20:00:00Z"}'
param([string]$Token = $env:JWT_TOKEN)
$BaseUrl = if ($env:BASE_URL) { $env:BASE_URL } else { "http://localhost:8000" }
$Body = if ($env:BODY) { $env:BODY } else { '{"rows":5,"columns":10,"movie":"Example Movie 2345","movieDatetime":"2026-10-01T20:00:00+00:00"}' }
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
  $r = Invoke-WebRequest -Uri "$BaseUrl/api/cinema-rooms" -Method Post -Headers $headers -Body $Body -UseBasicParsing
  ($r.Content | ConvertFrom-Json) | ConvertTo-Json -Depth 10
} catch {
  $status = $_.Exception.Response.StatusCode.value__
  $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
  $reader.BaseStream.Position = 0
  $body = $reader.ReadToEnd()
  Write-Host "HTTP $status" -ForegroundColor Red
  Write-Host $body
  exit 1
}
