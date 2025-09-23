#!/bin/bash

# Script to export all Mermaid diagrams to various formats
# Requires: @mermaid-js/mermaid-cli (mmdc)

echo "========================================"
echo "Exporting Mermaid Diagrams"
echo "========================================"

# Check if mmdc is installed
if ! command -v mmdc &> /dev/null; then
    echo "Error: mermaid-cli (mmdc) is not installed!"
    echo "Install it with: npm install -g @mermaid-js/mermaid-cli"
    exit 1
fi

# Create output directories
mkdir -p output/png
mkdir -p output/svg
mkdir -p output/pdf

# Export each diagram
for file in *.md; do
    if [ -f "$file" ]; then
        base_name="${file%.md}"
        echo "Processing: $file"
        
        # Extract mermaid code and save to temp file
        sed -n '/```mermaid/,/```/p' "$file" | sed '1d;$d' > temp.mmd
        
        if [ -s temp.mmd ]; then
            # Generate PNG
            echo "  → Generating PNG..."
            mmdc -i temp.mmd -o "output/png/${base_name}.png" -t dark -b transparent
            
            # Generate SVG
            echo "  → Generating SVG..."
            mmdc -i temp.mmd -o "output/svg/${base_name}.svg" -t dark
            
            # Generate PDF
            echo "  → Generating PDF..."
            mmdc -i temp.mmd -o "output/pdf/${base_name}.pdf" -t dark
            
            echo "  ✓ Complete"
        else
            echo "  ⚠ No mermaid code found in $file"
        fi
    fi
done

# Clean up temp file
rm -f temp.mmd

echo ""
echo "========================================"
echo "Export Complete!"
echo "========================================"
echo "Outputs:"
echo "  • PNG files: output/png/"
echo "  • SVG files: output/svg/"
echo "  • PDF files: output/pdf/"
echo ""