<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\LogActivityHelper;

class GeneralObserver{

    public function created(Model $model){
        $this->logActivity('Created', $model);
    }

    public function updated(Model $model){
        $this->logActivity('Updated', $model);
    }

    public function deleted(Model $model){
        $this->logActivity('Deleted', $model);
    }

    public function forceDeleted(Model $model){
        $this->logActivity('Force Deleted', $model);
    }

    protected function logActivity(string $action, Model $model){
        LogActivityHelper::logActivity($action, $model);
    }
}
