<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Named rate limiter for external API-backed login, values from config/external_api.php
        RateLimiter::for('external_api_login', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.login.max_attempts') ?? 5);
            $decay = (int) (config('external_api.rate_limit.login.decay_minutes') ?? 1);

            $userIdentifier = (string) $request->input('user', 'guest');
            $key = strtolower($userIdentifier) . '|' . $request->ip();

            return Limit::perMinutes($decay, $max)->by($key);
        });

        // Advisor: students listing
        RateLimiter::for('external_api_advisor_students', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.advisor_students.max_attempts') ?? 60);
            $decay = (int) (config('external_api.rate_limit.advisor_students.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('advisor_students|' . $request->ip());
        });
        // Advisor: students show
        RateLimiter::for('external_api_advisor_students_show', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.advisor_students_show.max_attempts') ?? 120);
            $decay = (int) (config('external_api.rate_limit.advisor_students_show.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('advisor_students_show|' . $request->ip());
        });
        // Advisor: students refresh
        RateLimiter::for('external_api_advisor_students_refresh', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.advisor_students_refresh.max_attempts') ?? 30);
            $decay = (int) (config('external_api.rate_limit.advisor_students_refresh.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('advisor_students_refresh|' . $request->ip());
        });
        // Advisor: dashboard
        RateLimiter::for('external_api_advisor_dashboard', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.advisor_dashboard.max_attempts') ?? 60);
            $decay = (int) (config('external_api.rate_limit.advisor_dashboard.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('advisor_dashboard|' . $request->ip());
        });
        // Advisor: groups
        RateLimiter::for('external_api_advisor_groups', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.advisor_groups.max_attempts') ?? 120);
            $decay = (int) (config('external_api.rate_limit.advisor_groups.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('advisor_groups|' . $request->ip());
        });
        // Advisor: available supervisors
        RateLimiter::for('external_api_advisor_available_supervisors', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.advisor_available_supervisors.max_attempts') ?? 60);
            $decay = (int) (config('external_api.rate_limit.advisor_available_supervisors.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('advisor_available_supervisors|' . $request->ip());
        });

        // Student: dashboard
        RateLimiter::for('external_api_student_dashboard', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.student_dashboard.max_attempts') ?? 60);
            $decay = (int) (config('external_api.rate_limit.student_dashboard.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('student_dashboard|' . $request->ip());
        });

        // Admin: supervisors and batches sync/compare
        RateLimiter::for('external_api_admin_supervisors_sync', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.admin_supervisors_sync.max_attempts') ?? 10);
            $decay = (int) (config('external_api.rate_limit.admin_supervisors_sync.decay_minutes') ?? 5);
            return Limit::perMinutes($decay, $max)->by('admin_supervisors_sync|' . $request->ip());
        });
        RateLimiter::for('external_api_admin_supervisors_refresh', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.admin_supervisors_refresh.max_attempts') ?? 30);
            $decay = (int) (config('external_api.rate_limit.admin_supervisors_refresh.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('admin_supervisors_refresh|' . $request->ip());
        });
        RateLimiter::for('external_api_admin_batches_sync', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.admin_batches_sync.max_attempts') ?? 10);
            $decay = (int) (config('external_api.rate_limit.admin_batches_sync.decay_minutes') ?? 5);
            return Limit::perMinutes($decay, $max)->by('admin_batches_sync|' . $request->ip());
        });
        RateLimiter::for('external_api_admin_batches_compare', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.admin_batches_compare.max_attempts') ?? 30);
            $decay = (int) (config('external_api.rate_limit.admin_batches_compare.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('admin_batches_compare|' . $request->ip());
        });

        // Raw endpoints (services or controllers)
        RateLimiter::for('external_api_teacher_list', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.teacher_list.max_attempts') ?? 120);
            $decay = (int) (config('external_api.rate_limit.teacher_list.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('teacher_list|' . $request->ip());
        });
        RateLimiter::for('external_api_student_list', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.student_list.max_attempts') ?? 120);
            $decay = (int) (config('external_api.rate_limit.student_list.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('student_list|' . $request->ip());
        });
        RateLimiter::for('external_api_batch_list', function (Request $request) {
            $max = (int) (config('external_api.rate_limit.batch_list.max_attempts') ?? 60);
            $decay = (int) (config('external_api.rate_limit.batch_list.decay_minutes') ?? 1);
            return Limit::perMinutes($decay, $max)->by('batch_list|' . $request->ip());
        });
    }
}
