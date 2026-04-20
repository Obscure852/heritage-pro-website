<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logging extends Model{
    use HasFactory, SoftDeletes;
    
    protected $table = 'loggings';
    
    protected $fillable = [
        'location',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'input',
        'changes',
    ];
    

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function getChangesArrayAttribute(){
        return json_decode($this->changes, true) ?? [];
    }
    
    public function getActionAttribute(){
        $changes = $this->changes_array;
        
        if (isset($changes['action'])) {
            return $changes['action'];
        }
        
        if (isset($changes['data']) && isset($changes['data']['action'])) {
            return $changes['data']['action'];
        }
        return 'Unknown';
    }
    
    public function getUserTypeAttribute(){
        $changes = $this->changes_array;
        
        if (isset($changes['data']) && isset($changes['data']['user_type'])) {
            return $changes['data']['user_type'];
        }
        
        if (isset($changes['user_type'])) {
            return $changes['user_type'];
        }
        return 'user';
    }
    
    public function getUserDisplayNameAttribute(){
        if ($this->user_id && $this->user) {
            return $this->user->fullName ?? 'User #' . $this->user_id;
        }
        
        $changes = $this->changes_array;
        $userType = $this->user_type;
        $userId = null;
        $email = null;
        
        if (isset($changes['data']) && isset($changes['data']['user_id'])) {
            $userId = $changes['data']['user_id'];
            $email = $changes['data']['email'] ?? null;
        } elseif (isset($changes['user_id'])) {
            $userId = $changes['user_id'];
            $email = $changes['email'] ?? null;
        }
        
        if ($email) {
            return ucfirst($userType) . ': ' . $email;
        } elseif ($userId) {
            return ucfirst($userType) . ' #' . $userId;
        }
        
        return 'System';
    }
}
