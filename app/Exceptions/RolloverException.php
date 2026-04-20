<?php

namespace App\Exceptions;
use Exception;

class RolloverException extends Exception{
    protected $stage;
    protected $contextData;

    public function __construct($message, $stage = null, $contextData = [], $code = 0, Exception $previous = null){
        parent::__construct($message, $code, $previous);

        $this->stage = $stage;
        $this->contextData = $contextData;
    }

    public function getStage(){
        return $this->stage;
    }

    public function getContextData(){
        return $this->contextData;
    }

    public function getLogMessage(){
        $contextString = json_encode($this->contextData);
        return "Rollover Error in stage '{$this->stage}': {$this->message}. Context: {$contextString}";
    }

    public function getDisplayMessage(){
        return "An error occurred during the year rollover process" . 
               ($this->stage ? " in the '{$this->stage}' stage" : "") . 
               ". Please contact the system administrator.";
    }
}
