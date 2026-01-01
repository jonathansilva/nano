<?php

namespace Nano\Core\View;

class Form
{
    public static function session(object $request): object
    {
        $csrf = $request->session('csrf');
        $errors = $request->session('errors');

        if ($request->hasSession('errors')) {
            $request->removeSession('errors');
        }

        return (object) [
            'csrf' => $csrf,
            'errors' => $errors
        ];
    }
}
