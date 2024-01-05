<?php
namespace App\Services;

class ActivityLogger
{
    public static function logActivity($model, $eventName, $causador, $descricao, $logName, $attributes)
    {
        $log = activity()
            ->event($eventName)
            ->performedOn($model)
            ->causedBy($causador)
            ->withProperties($attributes)
            ->useLog($logName) // Se você precisa definir o logName, substitua esta linha
            ->log($descricao);
    }
}