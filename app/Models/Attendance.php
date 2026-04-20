<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Attendance extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'student_id',
        'klass_id',
        'term_id',
        'date',
        'status',
        'year',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get all active attendance codes (cached for performance)
     */
    public static function getValidCodes()
    {
        return Cache::remember('attendance_valid_codes', 3600, function () {
            return AttendanceCode::active()->ordered()->pluck('code')->toArray();
        });
    }

    /**
     * Get code descriptions as array (cached for performance)
     */
    public static function getCodeDescriptions()
    {
        return Cache::remember('attendance_code_descriptions', 3600, function () {
            return AttendanceCode::active()->ordered()->pluck('description', 'code')->toArray();
        });
    }

    /**
     * Get codes with full details (cached for performance)
     */
    public static function getCodesWithDetails()
    {
        return Cache::remember('attendance_codes_details', 3600, function () {
            return AttendanceCode::active()->ordered()->get();
        });
    }

    /**
     * Get codes that represent "present" status
     */
    public static function getPresentCodes()
    {
        return Cache::remember('attendance_present_codes', 3600, function () {
            return AttendanceCode::active()->where('is_present', true)->pluck('code')->toArray();
        });
    }

    /**
     * Get codes that represent "absent" status
     */
    public static function getAbsentCodes()
    {
        return Cache::remember('attendance_absent_codes', 3600, function () {
            return AttendanceCode::active()->where('is_present', false)->pluck('code')->toArray();
        });
    }

    /**
     * Clear cached attendance codes (call when codes are updated)
     */
    public static function clearCodesCache()
    {
        Cache::forget('attendance_valid_codes');
        Cache::forget('attendance_code_descriptions');
        Cache::forget('attendance_codes_details');
        Cache::forget('attendance_present_codes');
        Cache::forget('attendance_absent_codes');
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function klass(){
        return $this->belongsTo(Klass::class);
    }

    public function term(){
        return $this->belongsTo(Term::class);
    }

    /**
     * Scope to filter present attendance records
     */
    public function scopePresent($query){
        $presentCodes = self::getPresentCodes();
        return $query->whereIn('status', $presentCodes);
    }

    /**
     * Scope to filter absent attendance records
     */
    public function scopeAbsent($query){
        $absentCodes = self::getAbsentCodes();
        return $query->whereIn('status', $absentCodes);
    }

    /**
     * Scope to filter by term
     */
    public function scopeForTerm($query, $termId){
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeBetween($query, $startDate, $endDate){
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by class
     */
    public function scopeForKlass($query, $klassId){
        return $query->where('klass_id', $klassId);
    }

    /**
     * Get the human-readable status description
     */
    public function getStatusDescriptionAttribute(){
        $descriptions = self::getCodeDescriptions();
        return $descriptions[$this->status] ?? 'Unknown';
    }
}
