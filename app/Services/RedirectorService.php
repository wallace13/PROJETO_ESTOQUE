<?php
namespace App\Services;

class RedirectorService
{
    public static function redirecionamentoRotas($saveAction, $entry, $local)
    {
        if ($saveAction === 'save_and_back') {
            return redirect("/admin/${local}");
        } elseif ($saveAction === 'save_and_edit') {
            return redirect("/admin/${local}/{$entry->id}/edit");
        } elseif ($saveAction === 'save_and_preview') {
            return redirect("/admin/${local}/{$entry->id}/show");
        } elseif ($saveAction === 'save_and_new') {
            return redirect("/admin/${local}/create");
        }
    }
}