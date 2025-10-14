#!/bin/bash
# Generate IEEE-Compliant Activity Diagrams
# Requires PlantUML and Java to be installed

echo "========================================"
echo "IEEE Activity Diagram Generator"
echo "========================================"
echo ""

# Check if Java is available
if ! command -v java &> /dev/null; then
    echo "ERROR: Java is not installed or not in PATH"
    echo "Please install Java to use PlantUML"
    exit 1
fi

# Create output directory
mkdir -p output

echo "Generating diagrams..."
echo ""

# Generate all diagrams
for file in *.puml; do
    echo "Processing: $file"
    java -jar plantuml.jar -tpng -o output "$file"
done

echo ""
echo "========================================"
echo "Generation Complete!"
echo "========================================"
echo ""
echo "PNG files are in the 'output' folder"
echo "Ready for inclusion in IEEE report"
echo ""
