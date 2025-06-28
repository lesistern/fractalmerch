@echo off
echo ===============================================
echo           PROYECTO BACKUP SCRIPT
echo ===============================================
echo.

:: Get current date and time for backup log
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "timestamp=%YYYY%-%MM%-%DD% %HH%:%Min%:%Sec%"

echo Iniciando backup: %timestamp%
echo.

:: Clear the backup directory first
echo Limpiando directorio de backup...
if exist "C:\xampp\htdocs\proyecto-back\*" (
    del /q "C:\xampp\htdocs\proyecto-back\*" 2>nul
    for /d %%x in ("C:\xampp\htdocs\proyecto-back\*") do @rd /s /q "%%x" 2>nul
)

echo Copiando archivos al backup...
echo.

:: Copy files using robocopy (more reliable than xcopy)
robocopy "C:\xampp\htdocs\proyecto" "C:\xampp\htdocs\proyecto-back" /MIR /XD node_modules .git /XF *.log /NFL /NDL /NJH /NJS /nc /ns /np

:: Check if robocopy succeeded
if %errorlevel% leq 3 (
    echo.
    echo ✓ Backup completado exitosamente
    echo ✓ Ubicacion: C:\xampp\htdocs\proyecto-back\
    echo ✓ Archivos excluidos: node_modules, .git, *.log
    echo.
) else (
    echo.
    echo ✗ Error durante el backup
    echo ✗ Codigo de error: %errorlevel%
    echo.
)

:: Log the backup
echo %timestamp% - Backup realizado >> backup.log

echo ===============================================
echo                   FINALIZADO
echo ===============================================
echo.
pause