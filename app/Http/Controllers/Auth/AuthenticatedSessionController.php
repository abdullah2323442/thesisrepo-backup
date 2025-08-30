<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user' => ['required', 'string'],
            'pass' => ['required', 'string'],
        ]);

        $loginType = $this->detectLoginType($validated['user']);

        try {
            if ($loginType === 'teacher') {
                return $this->attemptTeacherLogin($validated);
            } else {
                return $this->attemptStudentLogin($validated, $loginType);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login error:', ['error' => $e->getMessage(), 'login_type' => $loginType]);
            throw ValidationException::withMessages([
                'user' => ['Login failed: Unable to connect to the authentication server. Please try again later.'],
            ]);
        }
    }

    /**
     * Attempt teacher login - try multiple approaches
     */
    private function attemptTeacherLogin(array $validated): RedirectResponse
    {
        $teacherApiUrl = config('app.external_api_teacher_login_url');
        $query = http_build_query([
            'loginType' => 'teacher',
            'deptid' => 1,
            'user' => $validated['user'],
            'pass' => $validated['pass'],
        ]);

        try {
            $response = Http::timeout(60)->post($teacherApiUrl . '?' . $query);
            $data = $response->json();

            Log::info('Teacher API Response:', ['data' => $data, 'status' => $response->status()]);

            if (isset($data['Id']) && $data['MessageCode'] == 200) {
                return $this->handleSuccessfulTeacherLogin($data);
            }

            throw ValidationException::withMessages([
                'user' => [$data['Message'] ?? 'Login failed: Invalid credentials or login type.'],
            ]);
        } catch (\Exception $e) {
            Log::error('Teacher API error:', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages([
                'user' => ['Teacher login failed: ' . $e->getMessage()],
            ]);
        }
    }

    /**
     * Try the student API endpoint with teacher login type
     */
    private function attemptStudentApiForTeacher(array $validated): RedirectResponse
    {
        try {
            $apiUrl = config('app.external_api_login_url');
            $query = http_build_query([
                'user' => $validated['user'],
                'pass' => $validated['pass'],
                'logintype' => 'teacher',
            ]);

            Log::info('Attempting teacher login with student API', [
                'url' => $apiUrl . '?' . $query,
                'user' => $validated['user']
            ]);

            $response = Http::timeout(30)->post($apiUrl . '?' . $query);
            $data = $response->json();

            Log::info('Teacher login via student API Response:', ['data' => $data]);

            if (isset($data['Id']) && $data['MessageCode'] == 200) {
                if (isset($data['LoginType']) && $data['LoginType'] === 'teacher') {
                    return $this->handleSuccessfulTeacherLogin($data);
                } else {
                    $teacherData = $this->convertStudentToTeacherFormat($data);
                    return $this->handleSuccessfulTeacherLogin($teacherData);
                }
            }

            throw ValidationException::withMessages([
                'user' => [$data['Message'] ?? 'Invalid teacher credentials.'],
            ]);

        } catch (\Exception $e) {
            Log::error('Teacher login via student API error:', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages([
                'user' => ['Teacher login failed: ' . $e->getMessage()],
            ]);
        }
    }

    /**
     * Handle successful teacher login
     */
    private function handleSuccessfulTeacherLogin(array $data): RedirectResponse
    {
        Log::info('Teacher login successful, creating/updating user');

        // Create or update teacher in database
        $user = User::createOrUpdateTeacherFromApi($data);

        // Log the user in using Laravel's auth system
        Auth::login($user);

        // Determine TypeId array and store in session
        $typeIds = $this->determineTypeId($data['TypeId'] ?? null);
        $sessionData = array_merge($data, ['TypeId' => $typeIds]);
        Session::put('user', $sessionData);
        Session::put('logged_in', true);
        Session::put('user_id', $user->id);
        Session::put('user_type', 'teacher');

        Log::info('Teacher logged in successfully', [
            'user_id' => $user->id,
            'username' => $user->username ?? $user->name,
            'login_type' => 'teacher',
            'type_ids' => $typeIds,
            'raw_type_id' => $data['TypeId'] ?? null,
        ]);

        return redirect('/');
    }

    /**
     * Convert student API response to teacher format
     */
    private function convertStudentToTeacherFormat(array $studentData): array
    {
        return [
            'Id' => $studentData['Id'],
            'UserInfoId' => $studentData['Id'],
            'DeptId' => $studentData['DepartmentId'] ?? 1,
            'TypeId' => $this->determineTypeId($studentData['TypeId'] ?? null) ?? ['2'],
            'UserName' => $studentData['Roll'] ?? $studentData['Name'],
            'Name' => $studentData['Name'],
            'Phone' => $studentData['Phone'] ?? '',
            'designation' => '',
            'Email' => $studentData['Email'] ?? '',
            'Address' => $studentData['Address'] ?? '',
            'Salt' => 0,
            'Password' => '',
            'LoginType' => 'teacher',
            'Status' => $studentData['Status'] ?? null,
            'Message' => $studentData['Message'] ?? 'Successfully Logged in',
            'MessageCode' => $studentData['MessageCode'] ?? 200,
        ];
    }

    /**
     * Properly parse TypeId from API response - handles "'1''2''3'" format
     */
    private function determineTypeId($typeId): array
    {
        $allowedTypes = ['1', '2']; // Only Admin and Teacher

        if (!$typeId) {
            return ['2']; // Default to Teacher
        }

        if (is_array($typeId)) {
            return array_values(array_filter(array_map('strval', $typeId), fn($t) => in_array($t, $allowedTypes)));
        }

        if (is_string($typeId)) {
            if (preg_match_all("/'(\d+)'/", $typeId, $matches)) {
                return array_values(array_filter($matches[1], fn($t) => in_array($t, $allowedTypes)));
            }

            if (strpos($typeId, ',') !== false) {
                $types = explode(',', $typeId);
                return array_values(array_filter(array_map('trim', $types), fn($t) => in_array($t, $allowedTypes)));
            }

            $types = preg_split('/[\s,]+/', trim($typeId));
            return array_values(array_filter($types, fn($t) => in_array(trim($t), $allowedTypes)));
        }

        if (is_numeric($typeId)) {
            return in_array(strval($typeId), $allowedTypes) ? [strval($typeId)] : ['2'];
        }

        return ['2'];
    }


    /**
     * Attempt student login
     */
    private function attemptStudentLogin(array $validated, string $loginType): RedirectResponse
    {
        $apiUrl = config('app.external_api_login_url');
        $query = http_build_query([
            'user' => $validated['user'],
            'pass' => $validated['pass'],
            'logintype' => $loginType,
        ]);

        $response = Http::timeout(30)->post($apiUrl . '?' . $query);
        $data = $response->json();

        Log::info('Student API Response:', ['data' => $data, 'detected_login_type' => $loginType, 'api_url' => $apiUrl]);

        if (isset($data['Id']) && $data['MessageCode'] == 200) {
            Log::info('Student login successful, creating/updating user');

            $user = User::createOrUpdateFromApi($data);
            Auth::login($user);

            Session::put('user', $data);
            Session::put('logged_in', true);
            Session::put('user_id', $user->id);
            Session::put('user_type', 'student');

            Log::info('Student logged in successfully', [
                'user_id' => $user->id,
                'roll' => $user->roll,
                'login_type' => $loginType
            ]);

            return redirect('/');
        }

        if ($loginType === 'student') {
            return $this->attemptTeacherLogin($validated);
        }

        throw ValidationException::withMessages([
            'user' => [$data['Message'] ?? 'Invalid student credentials from API.'],
        ]);
    }

    /**
     * Detect login type based on user input pattern
     */
    private function detectLoginType(string $user): string
    {
        if (preg_match('/^\d{10,}$/', $user)) {
            return 'student';
        }

        if (preg_match('/^[a-zA-Z0-9]+$/', $user) && preg_match('/[a-zA-Z]/', $user)) {
            return 'teacher';
        }

        if (preg_match('/^\d{1,6}$/', $user)) {
            return 'teacher';
        }

        return 'student';
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        Session::forget(['user', 'logged_in', 'user_id', 'user_type']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
