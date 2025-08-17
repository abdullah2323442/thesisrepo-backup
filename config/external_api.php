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

];