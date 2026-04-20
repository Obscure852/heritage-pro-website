<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;

class HandleExceptions extends BaseHandleExceptions {
    public function bootstrap(Application $app) {
        parent::bootstrap($app);

        error_reporting(E_ALL & ~E_DEPRECATED);
    }

    public function handleError($level, $message, $file = '', $line = 0, $context = []) {
        if ($level === E_DEPRECATED || $level === E_USER_DEPRECATED) {
            return true;
        }

        return parent::handleError($level, $message, $file, $line, $context);
    }
}
