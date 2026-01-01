<?php

namespace Nano\Core\Security;

class CSRF
{
    public static function assert(object $request, object $response): void
    {
        if ($request->method() == 'GET') {
            $request->setSession('path', $request->path());
            $request->setSession('csrf', bin2hex(random_bytes(32)));

            return;
        }

        $token = trim($request->data('csrf') ?? '');

        if (empty($token)) {
            if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = trim($_SERVER['HTTP_X_CSRF_TOKEN']);
            }
        }

        if (empty($token)) {
            error_log('[CSRF] The token was not found or empty in the form or header');

            $response->redirect($request->session('path'));
        }

        if (!hash_equals($request->session('csrf'), $token)) {
            error_log('[CSRF] Invalid token in the form or header');

            $response->redirect($request->session('path'));
        }
    }
}
