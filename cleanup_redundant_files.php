<?php

echo "=== Project Cleanup Script ===\n\n";

// Files to delete - categorized for safety
$filesToDelete = [
    'Test Scripts' => [
        'check_current_advisor.php',
        'create_missing_advisors.php',
        'createGroup_method.php',
        'debug_admin_groups.php',
        'debug_advisor_detection.php',
        'enhance_advisor_detection.php',
        'final_advisor_test.php',
        'fix_admin_group.php',
        'fix_admin_view_js.php',
        'fix_advisor_api_id.php',
        'fix_advisor_detection_syntax.php',
        'fix_delete_button.php',
        'fix_delete_method.php',
        'fix_js_syntax.php',
        'fix_routes.php',
        'fix_sqlite_regexp.php',
        'test_admin_groups_route.php',
        'test_admin_groups.php',
        'test_advisor_autodetection.php',
        'test_advisor_groups.php',
        'test_advisor_view.php',
        'test_complete_delete.php',
        'test_delete_route.php',
        'test_group_deletion.php',
        'test_route.php'
    ],
    'Temporary Files' => [
        'add_delete_routes.txt',
        'temp_methods.txt',
        'advisor_groups_view_fix.blade.php'
    ],
    'Duplicate View Files' => [
        'resources/views/admin/groups/enhanced_create_form.blade.php',
        'resources/views/admin/groups/index_fixed.blade.php',
        'resources/views/admin/groups/index_updated.blade.php',
        'resources/views/admin/groups/index_with_delete.blade.php',
        'resources/views/advisor/groups/index_fixed.blade.php',
        'resources/views/advisor/groups/test.blade.php'
    ],
    'Duplicate Controller Files' => [
        'app/Http/Controllers/Admin/GroupManagementController_with_delete.php',
        'app/Http/Controllers/Advisor/GroupController_backup.php',
        'app/Http/Controllers/Advisor/GroupController_complete.php',
        'app/Http/Controllers/Advisor/GroupController_updated.php'
    ]
];

$totalDeleted = 0;
$totalSize = 0;

foreach ($filesToDelete as $category => $files) {
    echo "🗂️  {$category}:\n";
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $size = filesize($file);
            $totalSize += $size;
            
            if (unlink($file)) {
                echo "   ✅ Deleted: {$file} (" . formatBytes($size) . ")\n";
                $totalDeleted++;
            } else {
                echo "   ❌ Failed to delete: {$file}\n";
            }
        } else {
            echo "   ⏭️  Not found: {$file}\n";
        }
    }
    echo "\n";
}

echo "📊 Cleanup Summary:\n";
echo "   Files deleted: {$totalDeleted}\n";
echo "   Space saved: " . formatBytes($totalSize) . "\n";

// Create a backup list of deleted files
$backupList = "DELETED_FILES_BACKUP_LIST.txt";
file_put_contents($backupList, "Files deleted on " . date('Y-m-d H:i:s') . ":\n\n");

foreach ($filesToDelete as $category => $files) {
    file_put_contents($backupList, "{$category}:\n", FILE_APPEND);
    foreach ($files as $file) {
        file_put_contents($backupList, "- {$file}\n", FILE_APPEND);
    }
    file_put_contents($backupList, "\n", FILE_APPEND);
}

echo "\n📝 Created backup list: {$backupList}\n";

// Verify core functionality files are intact
echo "\n🔍 Verifying core files are intact:\n";
$coreFiles = [
    'app/Http/Controllers/Admin/GroupManagementController.php',
    'app/Http/Controllers/Advisor/GroupController.php',
    'resources/views/admin/groups/index.blade.php',
    'resources/views/advisor/groups/index.blade.php',
    'routes/web.php',
    'app/Models/Group.php',
    'app/Models/User.php'
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ {$file}\n";
    } else {
        echo "   ❌ MISSING: {$file}\n";
    }
}

echo "\n✅ Cleanup completed successfully!\n";
echo "\nNext steps:\n";
echo "1. Test the application to ensure everything works\n";
echo "2. Consider consolidating documentation files\n";
echo "3. Commit the cleaned-up project to version control\n";

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}