@echo off
color 0B
title ServisPilot Evden Test Modu
cd /d "c:\xampp\htdocs\servispilot"
echo ==================================================
echo.
echo Bu pencereyi acik tuttugunuz surece evdeki telefonunuz
echo dukkanin sistemine internet uzerinden baglanabilir.
echo.
echo Isiniz bitince pencereyi kapatabilirsiniz.
echo.
echo ==================================================
node evden_test.cjs
pause
