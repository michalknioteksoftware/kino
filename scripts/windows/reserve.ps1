# Reserve seats in a cinema room (no JWT required)
# Usage:
#   .\reserve.ps1 -RoomId 1 -Name "John Doe" -Seats "1,2","1,3","2,4"
#   .\reserve.ps1 1 "John Doe" "1,2" "1,3"
#   $env:BODY = '{"cinemaRoomId":1,"reservedByName":"John","seats":[{"row":1,"column":2}]}'; .\reserve.ps1
param(
  [Parameter(Position = 0)]
  [int]$RoomId = 0,
  [Parameter(Position = 1)]
  [string]$Name = $env:RESERVE_NAME,
  [Parameter(Position = 2, ValueFromRemainingArguments = $true)]
  [string[]]$Seats = @(),
  [string]$Body = $env:BODY
)
$BaseUrl = if ($env:BASE_URL) { $env:BASE_URL } else { "http://localhost:8000" }

if ($Body) {
  $jsonBody = $Body
} else {
  if ($RoomId -le 0) {
    Write-Host "Usage: .\reserve.ps1 -RoomId <id> -Name ""Reserver Name"" -Seats ""row,col"" [""row,col"" ...]" -ForegroundColor Yellow
    Write-Host "   or: .\reserve.ps1 <roomId> ""Name"" ""1,2"" ""1,3""" -ForegroundColor Yellow
    Write-Host "   or: `$env:BODY = '{""cinemaRoomId"":1,""reservedByName"":""John"",""seats"":[{""row"":1,""column"":2}]}'; .\reserve.ps1"
    exit 1
  }
  if (-not $Name -or $Name -eq "") { $Name = "John Doe" }
  $seatList = @()
  foreach ($s in $Seats) {
    $parts = $s -split ","
    if ($parts.Length -ne 2) {
      Write-Host "Invalid seat format: $s (use row,col e.g. 1,2)" -ForegroundColor Red
      exit 1
    }
    $seatList += @{ row = [int]$parts[0].Trim(); column = [int]$parts[1].Trim() }
  }
  if ($seatList.Count -eq 0) {
    Write-Host "At least one seat required (e.g. -Seats ""1,2"")" -ForegroundColor Red
    exit 1
  }
  $payload = @{ cinemaRoomId = $RoomId; reservedByName = $Name; seats = $seatList }
  $jsonBody = $payload | ConvertTo-Json -Depth 5 -Compress
}

$headers = @{ "Content-Type" = "application/json" }
try {
  $r = Invoke-WebRequest -Uri "$BaseUrl/api/reservations" -Method Post -Headers $headers -Body $jsonBody -UseBasicParsing
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
