#!/bin/bash

# Qodo Permanent Initialization Verification Script
# Run this script to verify that Qodo is permanently initialized

echo "🔍 VERIFYING QODO PERMANENT INITIALIZATION..."
echo "=============================================="

# Color codes for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if .qodo directory exists
if [ -d ".qodo" ]; then
    echo -e "${GREEN}✅ .qodo directory exists${NC}"
else
    echo -e "${RED}❌ .qodo directory missing${NC}"
    exit 1
fi

# Required files check
echo -e "\n${BLUE}📁 CHECKING CORE FILES:${NC}"
required_files=(
    ".qodo/config.json"
    ".qodo/rules.json" 
    ".qodo/templates.json"
    ".qodo/ignore.txt"
    ".qodo/README.md"
    ".qodo/.initialized"
    ".qodoignore"
)

all_files_exist=true
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ $file${NC}"
    else
        echo -e "${RED}❌ $file${NC}"
        all_files_exist=false
    fi
done

# Permanent setup files check
echo -e "\n${BLUE}🔒 CHECKING PERMANENT SETUP FILES:${NC}"
permanent_files=(
    ".qodo/permanent-config.json"
    ".qodo/init.sh"
    ".qodo/verify-permanent.sh"
    ".qodo/backups/"
)

for file in "${permanent_files[@]}"; do
    if [ -e "$file" ]; then
        echo -e "${GREEN}✅ $file${NC}"
    else
        echo -e "${RED}❌ $file${NC}"
        all_files_exist=false
    fi
done

# Check Git tracking
echo -e "\n${BLUE}📋 CHECKING GIT INTEGRATION:${NC}"
if git ls-files --error-unmatch .qodo/config.json >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Qodo configs are tracked by Git${NC}"
else
    echo -e "${YELLOW}⚠️  Qodo configs not yet committed to Git${NC}"
fi

# Check project type
echo -e "\n${BLUE}🎯 CHECKING PROJECT CONFIGURATION:${NC}"
if grep -q "laravel" .qodo/config.json; then
    echo -e "${GREEN}✅ Laravel project type configured${NC}"
else
    echo -e "${RED}❌ Laravel configuration missing${NC}"
fi

# Check permanent config
echo -e "\n${BLUE}⚙️  CHECKING PERMANENT SETTINGS:${NC}"
if [ -f ".qodo/permanent-config.json" ]; then
    if grep -q "\"permanent_initialization\"" .qodo/permanent-config.json; then
        echo -e "${GREEN}✅ Permanent initialization enabled${NC}"
    else
        echo -e "${RED}❌ Permanent initialization not configured${NC}"
    fi
    
    if grep -q "\"auto_restore\": true" .qodo/permanent-config.json; then
        echo -e "${GREEN}✅ Auto-restore enabled${NC}"
    else
        echo -e "${YELLOW}⚠️  Auto-restore not enabled${NC}"
    fi
fi

# Final status
echo -e "\n${BLUE}📊 VERIFICATION SUMMARY:${NC}"
echo "========================"

if [ "$all_files_exist" = true ]; then
    echo -e "${GREEN}🎉 QODO PERMANENT INITIALIZATION: SUCCESS!${NC}"
    echo -e "${GREEN}✅ All required files present${NC}"
    echo -e "${GREEN}✅ Permanent setup complete${NC}"
    echo -e "${GREEN}✅ Ready for Laravel development${NC}"
    echo ""
    echo -e "${BLUE}🚀 You can now use Qodo with confidence!${NC}"
    echo -e "${BLUE}   - Code analysis and suggestions${NC}"
    echo -e "${BLUE}   - Laravel-specific templates${NC}"
    echo -e "${BLUE}   - Security and performance monitoring${NC}"
    echo -e "${BLUE}   - AI-assisted development${NC}"
    echo ""
    echo -e "${GREEN}💡 Tip: Run this script anytime to verify your setup!${NC}"
else
    echo -e "${RED}❌ QODO PERMANENT INITIALIZATION: INCOMPLETE${NC}"
    echo -e "${RED}Some required files are missing.${NC}"
    echo -e "${YELLOW}Run 'qodo init' to restore missing files.${NC}"
    exit 1
fi