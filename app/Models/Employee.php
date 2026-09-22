<?php
// app/Models/Employee.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id',
        'photo',
        'first_name_am',
        'last_name_am',
        'first_name_en',
        'last_name_en',
        'gender',
        'age',
        'email',
        'phone',
        'emergency_contact',
        'location_type',
        'sub_city_id',
        'woreda_id',
        'kebele',
        'house_number',
        'position',
        'job_level',
        'rank',
        'employee_type',
        'salary',
        'hire_date',
        'birth_date',
        'birthplace',
        'education_level',
        'field_of_study',
        'institution',
        'national_id',
        'ethio_coder',
        'shirt_size',
        'pant_size',
        'shoe_size_casual',
        'shoe_size_leather',
        'hat_size',
        'cloth_size',
        'rain_cloth_size',
        'jacket_size',
        't_shirt_size',
        'training_round',
        'last_training_date',
        'training_notes',
        'status',
        'is_suspended_payment',
        'suspension_reason',
        'suspension_date',
        'walkie_talkie_serial',
        'stick_issued',
        'other_equipment'
    ];

    public static function jobPositionOptions(): array
    {
        return [
            'የደንብ መተላለፍ ቁጥጥርና ኢንስፔክሽን ቡድን መሪ' => 'የደንብ መተላለፍ ቁጥጥርና ኢንስፔክሽን ቡድን መሪ',
            'የደንብ መተላለፍ ቁጥጥርና ኢንስፔክሽን ባለሙያ' => 'የደንብ መተላለፍ ቁጥጥርና ኢንስፔክሽን ባለሙያ',
            'የደንብ መተላለፍ ቁጥጥር እርምጃ አወሳሰድ ሠራተኛ' => 'የደንብ መተላለፍ ቁጥጥር እርምጃ አወሳሰድ ሠራተኛ',
            'የግብረ ኃይል ቡድን አስተባባሪ' => 'የግብረ ኃይል ቡድን አስተባባሪ',
            'የግብረ ኃይል ባለሙያ' => 'የግብረ ኃይል ባለሙያ',
            'የፓራሚሊተሪ የአይሱዙ ሹፌር' => 'የፓራሚሊተሪ የአይሱዙ ሹፌር',
            'የፓራሚሊተሪ የፓትሮል ሹፌር' => 'የፓራሚሊተሪ የፓትሮል ሹፌር',
            'የግብረ ሃይል ቡድን አስተባባሪ' => 'የግብረ ሃይል ቡድን አስተባባሪ',
            'የግብረ ሃይል ሽፈት አስተባባሪ' => 'የግብረ ሃይል ሽፈት አስተባባሪ',
            'የደንብ መተላለፍ ክትትል ቁጥጥር እርምጃ አወሳሰድ ሽፍት አስተባባሪ' => 'የደንብ መተላለፍ ክትትል ቁጥጥር እርምጃ አወሳሰድ ሽፍት አስተባባሪ',
            'የደንብ መተላለፍ ቅድመ መከላከል ሽፍት አስተባባሪ' => 'የደንብ መተላለፍ ቅድመ መከላከል ሽፍት አስተባባሪ',
            'የደንብ መተላለፍ ቅድመ መከላከል ሽፍት መሪ' => 'የደንብ መተላለፍ ቅድመ መከላከል ሽፍት መሪ',
            'የደንብ መተላለፍ ቁጥጥር እርምጃ አወሳሰድ ባለሙያ' => 'የደንብ መተላለፍ ቁጥጥር እርምጃ አወሳሰድ ባለሙያ',
            'ቅድመ መከላከል ኦፈሰር' => 'ቅድመ መከላከል ኦፈሰር',
            'የመረጃ ሥራ አመራር ባለሙያ' => 'የመረጃ ሥራ አመራር ባለሙያ',
            'የፋይናንስ ንብረት አሳባሰብ አያያዝ አወጋገድ ቡድን አስተባባሪ' => 'የፋይናንስ ንብረት አሳባሰብ አያያዝ አወጋገድ ቡድን አስተባባሪ',
            'የንብረት ሥራ አመራር ባለሙያ' => 'የንብረት ሥራ አመራር ባለሙያ',
            'አካውንታንት' => 'አካውንታንት',
        ];
    }

    public static function jobLevelOptions(): array
    {
        return [
            'VIII' => 'VIII',
            'IX' => 'IX',
            'X' => 'X',
            'XI' => 'XI',
            'XII' => 'XII',
            'XIII' => 'XIII',
            'XIV' => 'XIV',
            'XV' => 'XV',
            'XVI' => 'XVI',
        ];
    }

    public static function uniformClothingSizeOptions(): array
    {
        return [
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            'XXL' => 'XXL',
            'XXXL' => 'XXXL',
        ];
    }

    public static function uniformShoeSizeOptions(): array
    {
        return array_combine(range(36, 45), range(36, 45));
    }

    public static function uniformHatSizeOptions(): array
    {
        return [
            '54' => '54',
            '55' => '55',
            '56' => '56',
        ];
    }

    public static function uniformSizeOptionsForItem(?string $itemType): array
    {
        return match ($itemType) {
            'shoe_casual', 'shoe_leather', 'footwear' => static::uniformShoeSizeOptions(),
            'hat', 'headgear' => static::uniformHatSizeOptions(),
            default => static::uniformClothingSizeOptions(),
        };
    }

    public static function generateParamilitaryId(): string
    {
        $prefix = 'CEA';

        $lastNumber = static::query()
            ->where('employee_id', 'like', "{$prefix}%")
            ->pluck('employee_id')
            ->map(fn (string $employeeId): int => (int) preg_replace('/\D/', '', $employeeId))
            ->max() ?? 0;

        do {
            $lastNumber++;
            $employeeId = $prefix . str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('employee_id', $employeeId)->exists());

        return $employeeId;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? url('storage/' . ltrim($this->photo, '/')) : null;
    }

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'last_training_date' => 'date',
        'suspension_date' => 'date',
        'is_suspended_payment' => 'boolean',
    ];

    public function subCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class);
    }

    public function uniformDistributions(): HasMany
    {
        return $this->hasMany(UniformDistribution::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class)->where('status', LeaveRequest::STATUS_APPROVED);
    }

    public function disciplineHistories(): HasMany
    {
        return $this->hasMany(EmployeeDisciplineHistory::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function dailyShiftReports(): HasMany
    {
        return $this->hasMany(DailyShiftReport::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function getFullNameAmAttribute()
    {
        return $this->first_name_am . ' ' . $this->last_name_am;
    }

    public function getFullNameEnAttribute()
    {
        return $this->first_name_en . ' ' . $this->last_name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailableForWork($query, mixed $date = null)
    {
        $date = Carbon::parse($date ?? now('Africa/Addis_Ababa'))->toDateString();

        return $query
            ->where('status', 'active')
            ->whereDoesntHave('leaveRequests', function ($leaveQuery) use ($date) {
                $leaveQuery->where('status', LeaveRequest::STATUS_APPROVED)
                    ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            });
    }

    public function isOnApprovedLeave(mixed $date = null): bool
    {
        $date = Carbon::parse($date ?? now('Africa/Addis_Ababa'))->toDateString();

        return $this->leaveRequests()
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeBySubCity($query, $subCityId)
    {
        return $query->where('sub_city_id', $subCityId);
    }
}
