<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external API endpoints used by the application.
    | These values should be set in your .env file for security.
    |
    */

    'base_url' => env('EXTERNAL_API_BASE_URL', 'http://puc.ac.bd:8012/api'),
    
    'endpoints' => [
        'student_login' => env('EXTERNAL_API_STUDENT_LOGIN', '/Login/LoginAction'),
        'teacher_login' => env('EXTERNAL_API_TEACHER_LOGIN', '/Teacher/Login'),
        'teacher_list' => env('EXTERNAL_API_TEACHER_LIST', '/Teacher/TeacherList'),
        'student_list' => env('EXTERNAL_API_STUDENT_LIST', '/Student/batchwiseStudentList'),
        'batch_list' => env('EXTERNAL_API_BATCH_LIST', '/Student/programwiseBatch'),
    ],

    'timeout' => env('EXTERNAL_API_TIMEOUT', 30),
    
    'department_id' => env('EXTERNAL_API_DEPARTMENT_ID', 1), // Computer Science & Engineering
    
    'retry_attempts' => env('EXTERNAL_API_RETRY_ATTEMPTS', 3),
    
    'retry_delay' => env('EXTERNAL_API_RETRY_DELAY', 1000), // milliseconds

    // Centralized rate limiting for external API use-cases
    'rate_limit' => [
        // Login attempts hitting external API
        'login' => [
            'max_attempts' => env('EXTERNAL_API_LOGIN_MAX_ATTEMPTS', 5),
            'decay_minutes' => env('EXTERNAL_API_LOGIN_DECAY_MINUTES', 1),
        ],

        // Advisor side: listing students, previewing/available supervisors, assignment previews
        'advisor_students' => [
            'max_attempts' => env('EXTERNAL_API_ADVISOR_STUDENTS_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('EXTERNAL_API_ADVISOR_STUDENTS_DECAY_MINUTES', 1),
        ],
        'advisor_students_show' => [
            'max_attempts' => env('EXTERNAL_API_ADVISOR_STUDENTS_SHOW_MAX_ATTEMPTS', 120),
            'decay_minutes' => env('EXTERNAL_API_ADVISOR_STUDENTS_SHOW_DECAY_MINUTES', 1),
        ],
        'advisor_students_refresh' => [
            'max_attempts' => env('EXTERNAL_API_ADVISOR_STUDENTS_REFRESH_MAX_ATTEMPTS', 30),
            'decay_minutes' => env('EXTERNAL_API_ADVISOR_STUDENTS_REFRESH_DECAY_MINUTES', 1),
        ],
        'advisor_dashboard' => [
            'max_attempts' => env('EXTERNAL_API_ADVISOR_DASHBOARD_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('EXTERNAL_API_ADVISOR_DASHBOARD_DECAY_MINUTES', 1),
        ],
        'advisor_groups' => [
            'max_attempts' => env('EXTERNAL_API_ADVISOR_GROUPS_MAX_ATTEMPTS', 120),
            'decay_minutes' => env('EXTERNAL_API_ADVISOR_GROUPS_DECAY_MINUTES', 1),
        ],
        'advisor_available_supervisors' => [
            'max_attempts' => env('EXTERNAL_API_ADVISOR_AVAILABLE_SUPERVISORS_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('EXTERNAL_API_ADVISOR_AVAILABLE_SUPERVISORS_DECAY_MINUTES', 1),
        ],

        // Student side: dashboard and profile access
        'student_dashboard' => [
            'max_attempts' => env('EXTERNAL_API_STUDENT_DASHBOARD_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('EXTERNAL_API_STUDENT_DASHBOARD_DECAY_MINUTES', 1),
        ],

        // Admin side: syncing supervisors and batches from external API
        'admin_supervisors_sync' => [
            'max_attempts' => env('EXTERNAL_API_ADMIN_SUPERVISORS_SYNC_MAX_ATTEMPTS', 10),
            'decay_minutes' => env('EXTERNAL_API_ADMIN_SUPERVISORS_SYNC_DECAY_MINUTES', 5),
        ],
        'admin_supervisors_refresh' => [
            'max_attempts' => env('EXTERNAL_API_ADMIN_SUPERVISORS_REFRESH_MAX_ATTEMPTS', 30),
            'decay_minutes' => env('EXTERNAL_API_ADMIN_SUPERVISORS_REFRESH_DECAY_MINUTES', 1),
        ],
        'admin_batches_sync' => [
            'max_attempts' => env('EXTERNAL_API_ADMIN_BATCHES_SYNC_MAX_ATTEMPTS', 10),
            'decay_minutes' => env('EXTERNAL_API_ADMIN_BATCHES_SYNC_DECAY_MINUTES', 5),
        ],
        'admin_batches_compare' => [
            'max_attempts' => env('EXTERNAL_API_ADMIN_BATCHES_COMPARE_MAX_ATTEMPTS', 30),
            'decay_minutes' => env('EXTERNAL_API_ADMIN_BATCHES_COMPARE_DECAY_MINUTES', 1),
        ],

        // Low-level raw endpoints if used directly in services
        'teacher_list' => [
            'max_attempts' => env('EXTERNAL_API_TEACHER_LIST_MAX_ATTEMPTS', 120),
            'decay_minutes' => env('EXTERNAL_API_TEACHER_LIST_DECAY_MINUTES', 1),
        ],
        'student_list' => [
            'max_attempts' => env('EXTERNAL_API_STUDENT_LIST_MAX_ATTEMPTS', 120),
            'decay_minutes' => env('EXTERNAL_API_STUDENT_LIST_DECAY_MINUTES', 1),
        ],
        'batch_list' => [
            'max_attempts' => env('EXTERNAL_API_BATCH_LIST_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('EXTERNAL_API_BATCH_LIST_DECAY_MINUTES', 1),
        ],
    ],

];