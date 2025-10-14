@echo off
REM Generate IEEE-Compliant Activity Diagrams
REM Requires PlantUML and Java to be installed

echo ========================================
echo IEEE Activity Diagram Generator
echo ========================================
echo.

REM Check if PlantUML is available
where java >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Java is not installed or not in PATH
    echo Please install Java to use PlantUML
    pause
    exit /b 1
)

REM Create output directory
if not exist "output" mkdir output

echo Generating diagrams...
echo.

REM Generate all diagrams
for %%f in (*.puml) do (
    echo Processing: %%f
    java -jar plantuml.jar -tpng -o output "%%f"
)

echo.
echo ========================================
echo Generation Complete!
echo ========================================
echo.
echo PNG files are in the 'output' folder
echo Ready for inclusion in IEEE report
echo.
pause
