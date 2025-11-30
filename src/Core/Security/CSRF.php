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

        $token = trim($req->data('csrf') ?? '');

        if (empty($token)) {
            if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = trim($_SERVER['HTTP_X_CSRF_TOKEN']);
            }
        }

        if (empty($token)) {
            error_log('[CSRF] The token was not found or empty in the form or header');

            $res->redirect($req->session('path'));
        }

        if (!hash_equals($req->session('csrf'), $token)) {
            error_log('[CSRF] Invalid token in the form or header');

            $res->redirect($req->session('path'));
        }
    }
}
