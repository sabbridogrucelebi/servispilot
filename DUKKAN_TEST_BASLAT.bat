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
color 0B
echo ==================================================
echo DUKKANDAN TEST ICIN ESKI ISLEMLER TEMIZLENIYOR...
echo ==================================================
taskkill /f /im node.exe >nul 2>&1
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im ngrok.exe >nul 2>&1

cd /d "c:\xampp\htdocs\servispilot"
node evden_test.cjs
pause
