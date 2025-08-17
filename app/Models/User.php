<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'api_id',
        'department_id',
        'program_id',
        'roll',
        'status',
        'department_name',
        'program_name',
        'batch',
        'profile_image_url',
        'phone',
        'login_type',
        'address',
        'advisor',
        'user_info_id',
        'type_id',
        'username',
        'designation',
        'salt',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate or retrieve secure password for API users
     * Only generates new password if user doesn't exist
     */
    private static function getOrCreateSecurePassword(string $userType, string $identifier): string
    {
        // Check if user already exists
        $existingUser = null;
        
        if ($userType === 'student') {
            $existingUser = self::where('roll', $identifier)->first();
        } else {
            $existingUser = self::where('username', $identifier)
                              ->where('login_type', 'teacher')
                              ->first();
        }

        // If user exists, return existing password (don't regenerate)
        if ($existingUser) {
            return $existingUser->password;
        }

        // Generate secure password for new users
        $prefix = $userType === 'student' ? 'STU_' : 'TCH_';
        $randomPart = Str::random(12);
        $timestamp = substr(time(), -4);
        $securePassword = $prefix . $randomPart . $timestamp;

        return Hash::make($securePassword);
    }

    /**
     * Create or update user from API response (Student)
     */
    public static function createOrUpdateFromApi(array $apiData): self
    {
        return self::updateOrCreate(
            ['roll' => $apiData['Roll']],
            [
                'api_id' => $apiData['Id'],
                'department_id' => $apiData['DepartmentId'],
                'program_id' => $apiData['ProgramId'],
                'name' => $apiData['Name'],
                'roll' => $apiData['Roll'],
                'status' => $apiData['Status'] ?? 'Active',
                'department_name' => $apiData['DepartmentName'] ?? 'Computer Science & Engineering',
                'program_name' => $apiData['ProgramName'] ?? 'Bachelor of Science in Computer Science & Engineering',
                'batch' => $apiData['Batch'],
                'profile_image_url' => $apiData['Url'],
                'phone' => $apiData['Phone'],
                'login_type' => $apiData['LoginType'],
                'email' => $apiData['Email'],
                'address' => $apiData['Address'],
                'advisor' => $apiData['Advisor'],
                'salt' => $apiData['Salt'] ?? 0,
                'password' => self::getOrCreateSecurePassword('student', $apiData['Roll']),
            ]
        );
    }

    /**
     * Create or update teacher from API response
     */
    public static function createOrUpdateTeacherFromApi(array $apiData): self
    {
        $identifier = $apiData['UserName'] ?? $apiData['Name'] ?? 'teacher_' . $apiData['Id'];
        $typeIds = self::determineTypeId($apiData['TypeId'] ?? null);

        return self::updateOrCreate(
            ['username' => $identifier, 'login_type' => 'teacher'],
            [
                'api_id' => $apiData['Id'],
                'user_info_id' => $apiData['UserInfoId'] ?? $apiData['Id'],
                'department_id' => $apiData['DeptId'] ?? $apiData['DepartmentId'] ?? 1,
                'type_id' => json_encode($typeIds),
                'username' => $identifier,
                'name' => $apiData['Name'],
                'phone' => $apiData['Phone'] ?? '',
                'designation' => $apiData['designation'] ?? '',
                'email' => $apiData['Email'] ?? '',
                'address' => $apiData['Address'] ?? '',
                'salt' => $apiData['Salt'] ?? 0,
                'login_type' => 'teacher',
                'status' => $apiData['Status'] ?? 'Active',
                'password' => self::getOrCreateSecurePassword('teacher', $identifier),
            ]
        );
    }

    /**
     * Parse stored type_id to an array
     */
    public function getTypeIdsAttribute(): array
    {
        if (!$this->type_id) return ['2']; // Default to Teacher

        $decoded = json_decode($this->type_id, true);
        if (is_array($decoded)) return $decoded;

        return self::determineTypeId($this->type_id);
    }

    /**
     * Accepts raw type_id and returns valid array ['1', '2']
     */
    public static function determineTypeId($typeId): array
    {
        $allowedTypes = ['1', '2']; // Only Admin and Teacher

        if (!$typeId) return ['2']; // Default to Teacher

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
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return in_array('1', $this->type_ids);
    }

    /**
     * Check if user has teacher role
     */
    public function isTeacher(): bool
    {
        return in_array('2', $this->type_ids);
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->login_type === 'student';
    }

    /**
     * Get the user's profile image URL with fallback
     */
    public function getProfileImageAttribute(): string
    {
        return $this->profile_image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=ffffff';
    }

    /**
     * Get display identifier (roll for students, username for teachers)
     */
    public function getDisplayIdentifierAttribute(): string
    {
        return $this->isTeacher() ? ($this->username ?? $this->name) : $this->roll;
    }
}
