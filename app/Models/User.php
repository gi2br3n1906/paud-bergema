<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'address',
        'photo_url',
        'teacher_competency',
        'teacher_motto',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Get the students associated with this parent user (many-to-many).
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student')
            ->withPivot('relationship_type', 'is_primary_contact')
            ->withTimestamps();
    }

    /**
     * Get the classroom where this user is the homeroom teacher.
     */
    public function classroom(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * Get daily logs recorded by this teacher.
     */
    public function dailyLogs(): HasMany
    {
        return $this->hasMany(StudentDailyLog::class, 'recorded_by');
    }

    /**
     * Get growth records recorded by this teacher.
     */
    public function growthRecords(): HasMany
    {
        return $this->hasMany(GrowthRecord::class, 'recorded_by');
    }

    /**
     * Get report cards created by this teacher.
     */
    public function createdReportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'created_by');
    }

    /**
     * Get report cards reviewed by this admin/principal.
     */
    public function reviewedReportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'reviewed_by');
    }

    // Scopes

    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::ADMIN);
    }

    public function scopeTeachers($query)
    {
        return $query->where('role', UserRole::TEACHER);
    }

    public function scopeParents($query)
    {
        return $query->where('role', UserRole::PARENT);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::TEACHER;
    }

    public function isParent(): bool
    {
        return $this->role === UserRole::PARENT;
    }
}
