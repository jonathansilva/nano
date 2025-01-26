<?php

namespace Nano\Core\View;

use Nano\Core\Env;

class Template
{
	private static array $blocks = [];

    private static bool $cache_enabled;

	private static string $cache_path = 'cache/';
    private static string $views_path = '/views';

	public static function render(string $file, ?array $data = []): void
    {
        self::$cache_enabled = (bool) Env::fetch('TEMPLATE_ENGINE_CACHE') ?? false;

		$cached_file = self::cache($file);

	    extract($data, EXTR_SKIP);

	   	require $cached_file;
	}

	private static function cache(string $file): string
    {
		if (!file_exists(self::$cache_path)) {
		  	mkdir(self::$cache_path, 0744);
		}

	    $cached_file = self::$cache_path . str_replace(array('/', '.html'), array('_', ''), $file . '.php');

	    if (!self::$cache_enabled || !file_exists($cached_file) || filemtime($cached_file) < filemtime($file)) {
			$code = self::include($file);
			$code = self::compile($code);

	        file_put_contents($cached_file, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);
	    }

		return $cached_file;
	}

    private static function include(string $file): string
    {
		$code = file_get_contents(dirname(__DIR__, 6) . self::$views_path . "/{$file}.html");

        $pattern = '/{% ?(extends|include) ([^\.\'"]+?) ?%}/i';

		preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);

		foreach ($matches as $value) {
			$code = str_replace($value[0], self::include($value[2]), $code);
		}

		$code = preg_replace($pattern, '', $code);

		return $code;
	}

	private static function compile(string $code): string
    {
		$code = self::block($code);
		$code = self::yield($code);

		$code = preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
        $code = preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);
		$code = preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code);
        $code = preg_replace('~\{#\s*(.+?)\s*\#}~is', '', $code);

		return $code;
	}

    private static function block(string $code): string
    {
        preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            if (!array_key_exists($value[1], self::$blocks)) {
                self::$blocks[$value[1]] = '';
            }

            self::$blocks[$value[1]] = (strpos($value[2], '@parent') === false) ? $value[2] : str_replace('@parent', self::$blocks[$value[1]], $value[2]);

            $code = str_replace($value[0], '', $code);
        }

        return str_replace(array("\r", "\n"), '', $code);
    }

	private static function yield(string $code): string
    {
		foreach (self::$blocks as $block => $value) {
			$code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $code);
		}

		$code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);

		return $code;
	}

	public static function clear(): void
    {
		foreach (glob(self::$cache_path . '*') as $file) {
			unlink($file);
		}
	}
}
