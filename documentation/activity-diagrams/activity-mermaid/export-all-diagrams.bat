@echo off
REM Script to export all Mermaid diagrams to various formats
REM Requires: @mermaid-js/mermaid-cli (mmdc)

echo ========================================
echo Exporting Mermaid Diagrams
echo ========================================
echo.

REM Check if mmdc is installed
where mmdc >nul 2>nul
if %errorlevel% neq 0 (
    echo Error: mermaid-cli ^(mmdc^) is not installed!
    echo Install it with: npm install -g @mermaid-js/mermaid-cli
    pause
    exit /b 1
)

REM Create output directories
if not exist "output\png" mkdir "output\png"
if not exist "output\svg" mkdir "output\svg"
if not exist "output\pdf" mkdir "output\pdf"

REM Process each markdown file
for %%f in (*.md) do (
    echo Processing: %%f
    
    REM Extract mermaid code to temp file
    powershell -Command "Get-Content '%%f' | Select-String -Pattern '```mermaid' -Context 0,100 | ForEach-Object { $_.Context.PostContext } | Select-String -Pattern '```' -Context 100,0 | ForEach-Object { $_.Context.PreContext } | Out-File -FilePath 'temp.mmd' -Encoding UTF8"
    
    if exist temp.mmd (
        REM Generate PNG
        echo   - Generating PNG...
        mmdc -i temp.mmd -o "output\png\%%~nf.png" -t dark -b transparent
        
        REM Generate SVG
        echo   - Generating SVG...
        mmdc -i temp.mmd -o "output\svg\%%~nf.svg" -t dark
        
        REM Generate PDF
        echo   - Generating PDF...
        mmdc -i temp.mmd -o "output\pdf\%%~nf.pdf" -t dark
        
        echo   - Complete
    ) else (
        echo   - No mermaid code found in %%f
    )
)

REM Clean up temp file
if exist temp.mmd del temp.mmd

echo.
echo ========================================
echo Export Complete!
echo ========================================
echo Outputs:
echo   - PNG files: output\png\
echo   - SVG files: output\svg\
echo   - PDF files: output\pdf\
echo.
pause