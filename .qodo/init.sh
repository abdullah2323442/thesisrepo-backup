#!/bin/bash

# Qodo Permanent Initialization Script
# This script ensures Qodo remains permanently initialized

echo "🚀 Ensuring Qodo Permanent Initialization..."

# Check if .qodo directory exists
if [ ! -d ".qodo" ]; then
    echo "❌ .qodo directory not found. Creating..."
    mkdir -p .qodo
fi

# Verify all required files exist
required_files=(".qodo/config.json" ".qodo/rules.json" ".qodo/templates.json" ".qodo/ignore.txt" ".qodo/README.md" ".qodo/.initialized" ".qodoignore")

for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo "❌ Missing required file: $file"
        echo "Please run 'qodo init' to restore missing files."
        exit 1
    fi
done

# Update .initialized file with current timestamp
echo "Qodo initialized successfully on $(date +%Y-%m-%d)" > .qodo/.initialized
echo "Project: Laravel Thesis Repository" >> .qodo/.initialized
echo "Type: Laravel Application" >> .qodo/.initialized
echo "Framework: Laravel 12.0" >> .qodo/.initialized
echo "Language: PHP" >> .qodo/.initialized
echo "" >> .qodo/.initialized
echo "Configuration files verified:" >> .qodo/.initialized
echo "- config.json: Main configuration ✓" >> .qodo/.initialized
echo "- rules.json: Code quality rules ✓" >> .qodo/.initialized
echo "- templates.json: Code templates and snippets ✓" >> .qodo/.initialized
echo "- ignore.txt: Files to exclude from analysis ✓" >> .qodo/.initialized
echo "- README.md: Documentation ✓" >> .qodo/.initialized
echo "" >> .qodo/.initialized
echo "Root files verified:" >> .qodo/.initialized
echo "- .qodoignore: Global ignore patterns ✓" >> .qodo/.initialized
echo "" >> .qodo/.initialized
echo "Qodo is permanently initialized and ready to assist with your Laravel development!" >> .qodo/.initialized

# Set proper permissions
chmod +x .qodo/init.sh

echo "✅ Qodo permanent initialization verified!"
echo "📁 All configuration files are in place"
echo "🔧 Project configured for Laravel development"
echo "🛡️  Security and quality rules active"
echo "📝 Templates and snippets ready"
echo "🎯 Ready for AI-assisted development"

echo ""
echo "🎉 Qodo is permanently initialized!"
echo "You can now use all Qodo features for your Laravel thesis project."