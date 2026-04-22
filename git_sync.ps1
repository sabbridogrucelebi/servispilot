while ($true) {
    $status = git status --porcelain
    if ($status) {
        Write-Host "Degisiklikler algilandi, GitHub'a gonderiliyor... - $(Get-Date)"
        git add .
        git commit -m "Otomatik Saatlik Yedekleme: $(Get-Date -Format 'dd.MM.yyyy HH:mm')"
        git push origin main
    } else {
        Write-Host "Herhangi bir degisiklik yok, 1 saat sonra tekrar kontrol edilecek... - $(Get-Date)"
    }
    Start-Sleep -Seconds 3600
}
