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
color 0A
echo ==================================================
echo.
echo Windows Guvenlik Duvari Izinleri Ayarlaniyor...
echo.
echo ==================================================
netsh advfirewall firewall add rule name="Expo ve Laravel" dir=in action=allow protocol=TCP localport=8000,8081
echo.
echo ==================================================
echo ISLEM TAMAMLANDI! Telefonunuz artik baglanabilir.
echo Bu pencereyi kapatabilirsiniz.
echo ==================================================
pause
