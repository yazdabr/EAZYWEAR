param(
    [string]$Message = "Update website"
)

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host " EAZYWEAR LOCAL DEPLOYMENT" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan

Write-Host ""
Write-Host "[1/3] Staging changes..." -ForegroundColor Yellow
git add .

if ($LASTEXITCODE -ne 0) {
    Write-Host "Git add gagal." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[2/3] Creating commit..." -ForegroundColor Yellow
git commit -m "$Message"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Tidak ada perubahan untuk di-commit, atau commit gagal." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[3/3] Pushing to GitHub..." -ForegroundColor Yellow
git push origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "Git push gagal." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host " PUSH SUCCESSFUL" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green