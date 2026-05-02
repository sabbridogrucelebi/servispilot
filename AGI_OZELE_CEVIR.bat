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
echo Ag Profili Ozel Ag (Private) Olarak Degistiriliyor...
echo ==================================================
powershell -Command "Get-NetConnectionProfile | Set-NetConnectionProfile -NetworkCategory Private"
echo.
echo ==================================================
echo ISLEM TAMAMLANDI! 
echo Guvenlik duvari artik baglantilari engellemeyecek.
echo.
echo LUTFEN SUNLARI KONTROL EDIN:
echo 1. Telefonunuzun "Hucresel Verisini (4G/5G)" kapatin.
echo 2. Telefonunuzun, bilgisayarinizin bagli oldugu Wi-Fi aginda oldugundan emin olun.
echo ==================================================
pause
