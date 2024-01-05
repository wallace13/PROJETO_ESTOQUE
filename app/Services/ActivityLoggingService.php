<?php
namespace App\Services;

use App\Services\ActivityLogger;

class ActivityLoggingService
{
    public static function logActivity($model, $event, $logName)
    {
        $causador = backpack_auth()->user();
        $eventName = $event;
        $descricao = ucfirst($logName) ." {$event} por {$causador->name}";
        ActivityLogger::logActivity($model, $eventName, $causador, $descricao, $logName, $model->attributes);
    }
}