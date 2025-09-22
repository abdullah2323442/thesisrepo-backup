@echo off
REM Batch script to generate all activity diagrams in various formats
REM Requires PlantUML jar file in the same directory or in PATH

echo ========================================
echo Generating Activity Diagrams
echo ========================================
echo.

REM Check if plantuml.jar exists
if not exist plantuml.jar (
    echo ERROR: plantuml.jar not found!
    echo Please download from: https://plantuml.com/download
    echo Place it in the same directory as this script
    pause
    exit /b 1
)

REM Create output directories
if not exist "output\png" mkdir "output\png"
if not exist "output\svg" mkdir "output\svg"
if not exist "output\pdf" mkdir "output\pdf"

echo Generating PNG files...
java -jar plantuml.jar -tpng -o output/png *.puml
echo PNG files generated in output/png/

echo.
echo Generating SVG files...
java -jar plantuml.jar -tsvg -o output/svg *.puml
echo SVG files generated in output/svg/

echo.
echo Generating PDF files...
java -jar plantuml.jar -tpdf -o output/pdf *.puml
echo PDF files generated in output/pdf/

echo.
echo ========================================
echo All diagrams generated successfully!
echo ========================================
echo.
echo Output locations:
echo - PNG: output\png\
echo - SVG: output\svg\
echo - PDF: output\pdf\
echo.
pause