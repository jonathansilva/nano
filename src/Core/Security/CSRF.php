<?php

namespace Nano\Core\Security;

class CSRF
{
    public static function assert(object $req, object $res): void
    {
        if ($req->method() == 'GET') {
            $req->setSession('path', $req->path());
            $req->setSession('csrf', bin2hex(random_bytes(32)));

            return;
        }

        if (!str_starts_with($req->path(), '/api/')) {
            $field = $req->data('csrf');

            if (!(mb_strlen(trim($field)) > 0 && hash_equals($req->session('csrf'), $field))) {
                $res->redirect($req->session('path'));
            }
        }
    }
}
