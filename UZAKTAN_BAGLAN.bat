@echo off
:: Admin yetkisi kontrolü
net session >nul 2>&1
if %errorLevel% == 0 (
    goto :RunCommand
) else (
    echo Yonetici izni isteniyor... Lutfen "Evet" diyerek onaylayin.
    powershell -Command "Start-Process '%~dpnx0' -Verb RunAs"
    exit /B
)

:RunCommand
color 0C
echo ==================================================
echo Arkada asili kalmis eski islemler temizleniyor...
echo ==================================================
taskkill /f /im node.exe >nul 2>&1
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im ngrok.exe >nul 2>&1

color 0A
cd /d "c:\xampp\htdocs\servispilot"
node evden_test_uzak.cjs
pause
