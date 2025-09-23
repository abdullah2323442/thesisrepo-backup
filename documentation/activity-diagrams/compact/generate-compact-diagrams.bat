@echo off
REM Generate compact diagrams optimized for A4 printing

echo ========================================
echo Generating Compact A4-Optimized Diagrams
echo ========================================
echo.

if not exist plantuml.jar (
    echo ERROR: plantuml.jar not found!
    echo Download from: https://plantuml.com/download
    pause
    exit /b 1
)

REM Create output directories
if not exist "output-a4\png" mkdir "output-a4\png"
if not exist "output-a4\pdf" mkdir "output-a4\pdf"
if not exist "output-a4\svg" mkdir "output-a4\svg"

echo Generating high-quality PNGs for A4 printing...
java -jar plantuml.jar -tpng -dpi 300 -o output-a4/png *.puml
echo.

echo Generating PDFs for direct A4 printing...
java -jar plantuml.jar -tpdf -o output-a4/pdf *.puml
echo.

echo Generating SVGs for web documentation...
java -jar plantuml.jar -tsvg -o output-a4/svg *.puml
echo.

echo ========================================
echo A4-optimized diagrams generated!
echo ========================================
echo Outputs:
echo - PNG (300 DPI): output-a4\png\
echo - PDF (A4 Ready): output-a4\pdf\
echo - SVG (Scalable): output-a4\svg\
echo.
echo Print Settings:
echo - Paper: A4 (210x297mm)
echo - Orientation: Portrait (Landscape for #11)
echo - Scale: Fit to page
echo.
pause