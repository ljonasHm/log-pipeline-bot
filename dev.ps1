$ErrorActionPreference = "Stop"

Set-Location $PSScriptRoot

# 1. Build the Docker image and start the container
Write-Host "Starting Docker containers..." -ForegroundColor Cyan

docker compose up --build -d
if ($LASTEXITCODE -ne 0) {
    throw "Failed to start Docker containers"
}

# 2. Run ngrok in a separate window
Write-Host "Starting ngrok..." -ForegroundColor Cyan

$ngrok = Start-Process -FilePath "ngrok" `
    -ArgumentList @("http", "http://localhost:8000") `
    -PassThru

# 3. Wait for ngrok tunnel to be ready
Write-Host "Waiting for ngrok tunnel..." -ForegroundColor Yellow

$tunnelUrl = $null
$deadline = (Get-Date).AddSeconds(30)

while ((Get-Date) -lt $deadline) {
    try {
        $tunnels = Invoke-RestMethod `
            -Uri "http://127.0.0.1:4040/api/tunnels" `
            -TimeoutSec 2

        $tunnel = $tunnels.tunnels |
            Where-Object { $_.public_url -like "https://*" } |
            Select-Object -First 1

        if ($tunnel) {
            $tunnelUrl = $tunnel.public_url
            break
        }
    }
    catch {}

    Start-Sleep -Seconds 1
}

if (-not $tunnelUrl) {
    throw "Could not obtain ngrok HTTPS URL"
}

Write-Host "ngrok URL: $tunnelUrl" -ForegroundColor Green

# 4. Wait for Laravel in the app container to be ready
Write-Host "Waiting for Laravel container..." -ForegroundColor Cyan

$deadline = (Get-Date).AddSeconds(60)
$appReady = $false

while ((Get-Date) -lt $deadline) {
    docker compose exec -T app php artisan --version *> $null

    if ($LASTEXITCODE -eq 0) {
        $appReady = $true
        break
    }

    Start-Sleep -Seconds 2
}

if (-not $appReady) {
    throw "Laravel container is not ready"
}

# 5. Set Nutgram webhook
$webhookUrl = "$tunnelUrl/api/telegram/webhook"

Write-Host "Setting Telegram webhook..." -ForegroundColor Cyan
Write-Host $webhookUrl

docker compose exec -T app php artisan nutgram:hook:set $webhookUrl

if ($LASTEXITCODE -ne 0) {
    throw "Failed to set Telegram webhook"
}

Write-Host "Development environment is ready!" -ForegroundColor Green