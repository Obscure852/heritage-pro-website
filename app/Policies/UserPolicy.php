<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy{
    use HandlesAuthorization;

    public function __construct(){
        //
    }

    function canAllocateRoles(User $user){
        return  $user->hasRoles('Administrator') || $user->hasRoles('HR Admin');
    }

    function canViewUserData(User $user){
        return $user->hasRoles('HR View');
    }


    function canImportData(User $user){
        return $user->hasRoles('Data Importing');
    }
}
