<?php

namespace Nano\Core\View;

class Form
{
    public static function session(object $req): object
    {
        $csrf = $req->session('csrf');
        $errors = $req->session('errors');

        if ($req->hasSession('errors')) {
            $req->removeSession('errors');
        }

        return (object) [
            'csrf' => $csrf,
            'errors' => $errors
        ];
    }
}
