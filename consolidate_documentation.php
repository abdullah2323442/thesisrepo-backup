<?php

echo "=== Documentation Consolidation Script ===\n\n";

// Documentation files to consolidate
$docFiles = [
    'ADMIN_GROUP_CREATION_SOLUTION.md',
    'ADVISOR_DETECTION_FIX_SUMMARY.md',
    'ADVISOR_VIEW_FIX_INSTRUCTIONS.md',
    'API_RATE_LIMITING_SUMMARY.md',
    'API_TEST_FIX_SUMMARY.md',
    'COMPREHENSIVE_TEST_SUMMARY.md',
    'COMPREHENSIVE_TESTING_DOCUMENTATION.md',
    'DELETE_ISSUE_RESOLUTION.md',
    'DOCUMENTATION_INDEX.md',
    'EXCEL_UPLOAD_GUIDE.md',
    'FINAL_TEST_REPORT.md',
    'GROUP_DELETE_FEATURE_SUMMARY.md',
    'IMPLEMENTATION_SUMMARY.md',
    'LOGOUT_FUNCTIONALITY_SUMMARY.md',
    'MULTIPLE_AREAS_OF_INTEREST_FEATURE.md',
    'PERFORMANCE_MONITORING_GUIDE.md',
    'RANDOM_GROUP_ASSIGNMENT_FEATURE.md',
    'SENIOR_DEVELOPER_REVIEW.md',
    'STUDENT_DASHBOARD_FEATURES.md',
    'supervisor_assignment_algo.md',
    'TEST_STATUS_REPORT.md',
    'UAT_Supervisor_Assignment_Tests.md',
    'secure.md',
    'REDUNDANT_FILES_ANALYSIS.md'
];

// Create consolidated documentation
echo "📚 Creating consolidated documentation files...\n\n";

// 1. FEATURES_GUIDE.md - All feature documentation
$featuresContent = "# Thesis Management System - Features Guide\n\n";
$featuresContent .= "This document consolidates all feature documentation for the Thesis Management System.\n\n";
$featuresContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
$featuresContent .= "---\n\n";

$featureFiles = [
    'ADMIN_GROUP_CREATION_SOLUTION.md',
    'ADVISOR_DETECTION_FIX_SUMMARY.md',
    'DELETE_ISSUE_RESOLUTION.md',
    'GROUP_DELETE_FEATURE_SUMMARY.md',
    'LOGOUT_FUNCTIONALITY_SUMMARY.md',
    'MULTIPLE_AREAS_OF_INTEREST_FEATURE.md',
    'RANDOM_GROUP_ASSIGNMENT_FEATURE.md',
    'STUDENT_DASHBOARD_FEATURES.md',
    'EXCEL_UPLOAD_GUIDE.md'
];

foreach ($featureFiles as $file) {
    if (file_exists($file)) {
        $featuresContent .= "# " . str_replace(['_', '.md'], [' ', ''], $file) . "\n\n";
        $featuresContent .= file_get_contents($file) . "\n\n";
        $featuresContent .= "---\n\n";
    }
}

file_put_contents('FEATURES_GUIDE.md', $featuresContent);
echo "✅ Created: FEATURES_GUIDE.md\n";

// 2. DEVELOPER_GUIDE.md - Technical implementation details
$devContent = "# Thesis Management System - Developer Guide\n\n";
$devContent .= "This document consolidates all technical implementation details and developer information.\n\n";
$devContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
$devContent .= "---\n\n";

$devFiles = [
    'IMPLEMENTATION_SUMMARY.md',
    'API_RATE_LIMITING_SUMMARY.md',
    'API_TEST_FIX_SUMMARY.md',
    'ADVISOR_VIEW_FIX_INSTRUCTIONS.md',
    'supervisor_assignment_algo.md',
    'PERFORMANCE_MONITORING_GUIDE.md',
    'secure.md',
    'SENIOR_DEVELOPER_REVIEW.md'
];

foreach ($devFiles as $file) {
    if (file_exists($file)) {
        $devContent .= "# " . str_replace(['_', '.md'], [' ', ''], $file) . "\n\n";
        $devContent .= file_get_contents($file) . "\n\n";
        $devContent .= "---\n\n";
    }
}

file_put_contents('DEVELOPER_GUIDE.md', $devContent);
echo "✅ Created: DEVELOPER_GUIDE.md\n";

// 3. TESTING_GUIDE.md - All testing documentation
$testContent = "# Thesis Management System - Testing Guide\n\n";
$testContent .= "This document consolidates all testing documentation and reports.\n\n";
$testContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
$testContent .= "---\n\n";

$testFiles = [
    'COMPREHENSIVE_TEST_SUMMARY.md',
    'COMPREHENSIVE_TESTING_DOCUMENTATION.md',
    'FINAL_TEST_REPORT.md',
    'TEST_STATUS_REPORT.md',
    'UAT_Supervisor_Assignment_Tests.md'
];

foreach ($testFiles as $file) {
    if (file_exists($file)) {
        $testContent .= "# " . str_replace(['_', '.md'], [' ', ''], $file) . "\n\n";
        $testContent .= file_get_contents($file) . "\n\n";
        $testContent .= "---\n\n";
    }
}

file_put_contents('TESTING_GUIDE.md', $testContent);
echo "✅ Created: TESTING_GUIDE.md\n";

// 4. Update README.md with proper structure
$readmeContent = "# Thesis Management System\n\n";
$readmeContent .= "A comprehensive web application for managing thesis projects, student groups, and supervisor assignments.\n\n";
$readmeContent .= "## 📚 Documentation\n\n";
$readmeContent .= "- **[Setup Guide](PROJECT_SETUP_GUIDE.md)** - Installation and configuration\n";
$readmeContent .= "- **[Features Guide](FEATURES_GUIDE.md)** - All system features and functionality\n";
$readmeContent .= "- **[Developer Guide](DEVELOPER_GUIDE.md)** - Technical implementation details\n";
$readmeContent .= "- **[Testing Guide](TESTING_GUIDE.md)** - Testing documentation and reports\n";
$readmeContent .= "- **[Qodo Setup](QODO_PERMANENT_SETUP.md)** - AI assistant configuration\n\n";
$readmeContent .= "## 🚀 Quick Start\n\n";
$readmeContent .= "1. Follow the [Setup Guide](PROJECT_SETUP_GUIDE.md) for installation\n";
$readmeContent .= "2. Review the [Features Guide](FEATURES_GUIDE.md) to understand system capabilities\n";
$readmeContent .= "3. Check the [Developer Guide](DEVELOPER_GUIDE.md) for technical details\n\n";
$readmeContent .= "## 🎯 Key Features\n\n";
$readmeContent .= "- **Admin Panel**: Complete group and supervisor management\n";
$readmeContent .= "- **Advisor Dashboard**: Group creation and student assignment\n";
$readmeContent .= "- **Student Interface**: Group viewing and thesis tracking\n";
$readmeContent .= "- **Supervisor Assignment**: Automated and manual assignment algorithms\n";
$readmeContent .= "- **Excel Integration**: Bulk operations and data import/export\n";
$readmeContent .= "- **Performance Monitoring**: System health and usage tracking\n\n";
$readmeContent .= "## 📊 System Status\n\n";
$readmeContent .= "- ✅ **Admin Group Management**: Fully functional\n";
$readmeContent .= "- ✅ **Advisor Auto-Detection**: Working for all advisors\n";
$readmeContent .= "- ✅ **Group Deletion**: With automatic renumbering\n";
$readmeContent .= "- ✅ **Multiple Areas of Interest**: Supported\n";
$readmeContent .= "- ✅ **Excel Upload/Download**: Operational\n";
$readmeContent .= "- ✅ **Performance Monitoring**: Active\n\n";
$readmeContent .= "Last updated: " . date('Y-m-d H:i:s') . "\n";

file_put_contents('README.md', $readmeContent);
echo "✅ Updated: README.md\n";

// Create a list of files to delete after consolidation
echo "\n📝 Creating list of files that can now be deleted...\n";
$filesToDelete = [];
foreach ($docFiles as $file) {
    if (file_exists($file) && $file !== 'README.md' && $file !== 'PROJECT_SETUP_GUIDE.md' && $file !== 'QODO_PERMANENT_SETUP.md') {
        $filesToDelete[] = $file;
    }
}

$deleteList = "DOCUMENTATION_FILES_TO_DELETE.txt";
file_put_contents($deleteList, "Documentation files that can be deleted after consolidation:\n\n");
foreach ($filesToDelete as $file) {
    file_put_contents($deleteList, "- {$file}\n", FILE_APPEND);
}
file_put_contents($deleteList, "\nTotal: " . count($filesToDelete) . " files\n", FILE_APPEND);
file_put_contents($deleteList, "These files have been consolidated into the new documentation structure.\n", FILE_APPEND);

echo "✅ Created: {$deleteList}\n";

echo "\n📊 Consolidation Summary:\n";
echo "   Original documentation files: " . count($docFiles) . "\n";
echo "   New consolidated files: 4 (README.md, FEATURES_GUIDE.md, DEVELOPER_GUIDE.md, TESTING_GUIDE.md)\n";
echo "   Files that can be deleted: " . count($filesToDelete) . "\n";

echo "\n✅ Documentation consolidation completed!\n";
echo "\nNew documentation structure:\n";
echo "├── README.md (Main overview)\n";
echo "├── PROJECT_SETUP_GUIDE.md (Setup instructions)\n";
echo "├── FEATURES_GUIDE.md (All features)\n";
echo "├── DEVELOPER_GUIDE.md (Technical details)\n";
echo "├── TESTING_GUIDE.md (Testing documentation)\n";
echo "└── QODO_PERMANENT_SETUP.md (AI assistant setup)\n";

echo "\nNext step: Review the consolidated files and delete the old ones if satisfied.\n";