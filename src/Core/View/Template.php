<?php

namespace Nano\Core\View;

use Nano\Core\Env;
use Exception;

class Template
{
	private static array $blocks = [];

    private static bool $cache;

	private static string $cachePath = 'cache/';

	public static function render(string $file, ?array $data = []): void
    {
        self::$cache = filter_var(Env::fetch('TEMPLATE_ENGINE_CACHE'), FILTER_VALIDATE_BOOLEAN);

		$cachedFile = self::cache($file);

	    extract($data, EXTR_SKIP);

	   	require $cachedFile;
	}

	private static function cache(string $file): string
    {
		if (!file_exists(self::$cachePath)) {
		  	mkdir(self::$cachePath, 0744);
		}

        $templatePath = self::getTemplatePath($file);

        if (!file_exists($templatePath)) {
            throw new Exception("Template file '{$file}.html' not found at expected path");
        }

	    $cachedFile = self::$cachePath . str_replace(['/', '.html'], ['_', ''], $file . '.php');

	    if (!self::$cache || !file_exists($cachedFile) || filemtime($cachedFile) < filemtime($templatePath)) {
			$code = self::include($file);
			$code = self::compile($code);

	        file_put_contents($cachedFile, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);
	    }

		return $cachedFile;
	}

    private static function include(string $file): string
    {
        $templatePath = self::getTemplatePath($file);

		$code = file_get_contents($templatePath);

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

    private static function getTemplatePath(string $file): string
    {
        return dirname(__DIR__, 6) . "/views/{$file}.html";
    }
}
