<?php

namespace Nano\Core\Security;

class CSRF
{
    public static function assert(object $req, object $res): void
    {
        if (str_starts_with($req->path(), '/api/')) {
            return;
        }

        if ($req->method() == 'GET') {
            $req->setSession('path', $req->path());
            $req->setSession('csrf', bin2hex(random_bytes(32)));

            return;
        }

        $field = trim($req->data('csrf'));

        if (empty($field)) {
            error_log('The "csrf" field is empty');

            $res->redirect($req->session('path'));
        }

        if (!hash_equals($req->session('csrf'), $field)) {
            error_log('The "csrf" field is invalid');

            $res->redirect($req->session('path'));
        }
    }
}
