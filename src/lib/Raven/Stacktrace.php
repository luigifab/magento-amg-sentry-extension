<?php
/**
 * This file is part of Raven.
 *
 * (c) Sentry Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code (BSD-3-Clause).
 */

class Raven_Stacktrace {

	public static function get_stack_info($stack) {

		$result = [];
		foreach ($stack as $frame) {

			if (isset($frame['file'])) {
				$context  = self::read_source_file($frame['file'], $frame['line']);
				$abs_path = $frame['file'];
				$filename = basename($frame['file']);
			}
			else {
				if (isset($frame['args']))
					$args = is_string($frame['args']) ? $frame['args'] : @json_encode($frame['args']);
				else
					$args = 'n/a';

				if (isset($frame['class']))
					$context['line'] = sprintf('%s%s%s(%s)', $frame['class'], $frame['type'], $frame['function'], $args);
				else
					$context['line'] = sprintf('%s(%s)', $frame['function'], $args);

				$abs_path = '';
				$filename = '[Anonymous function]';
				$context['prefix'] = '';
				$context['suffix'] = '';
				$context['filename'] = $filename;
				$context['lineno'] = 0;
			}

			$module = $filename;
			if (isset($frame['class']))
				$module .= ':'.$frame['class'];

			$result[] = [
				'abs_path'     => $abs_path,
				'filename'     => $context['filename'],
				'lineno'       => $context['lineno'],
				'module'       => $module,
				'function'     => $frame['function'],
				'vars'         => [],
				'pre_context'  => $context['prefix'],
				'context_line' => $context['line'],
				'post_context' => $context['suffix'],
			];
		}

		return array_reverse($result);
	}

	private static function read_source_file($filename, $lineno) {

		$frame = [
			'prefix'   => [],
			'line'     => '',
			'suffix'   => [],
			'filename' => $filename,
			'lineno'   => $lineno,
		];

		if (($filename === null) || ($lineno === null))
			return $frame;

		// Code which is eval'ed have a modified filename.. Extract the
		// correct filename + linenumber from the string.
		$matched = preg_match("/^(.*?)\((\d+)\) : eval\(\)'d code$/", $filename, $matches);
		if ($matched) {
			[, $filename, $lineno] = $matches;
			$frame['filename'] = $filename;
			$frame['lineno']   = $lineno;
		}

		// Try to open the file. We wrap this in a try/catch block in case
		// someone has modified the error_trigger to throw exceptions.
		try {
			$fh = fopen($filename, 'rb');
			if ($fh === false)
				return $frame;
		}
		catch (Throwable $t) {
			return $frame;
		}

		$cur_lineno = 0;
		while (!feof($fh)) {

			$cur_lineno++;
			$line = fgets($fh);

			if ($cur_lineno == $lineno)
				$frame['line'] = $line;
			else if ($lineno - $cur_lineno > 0 && $lineno - $cur_lineno < 3)
				$frame['prefix'][] = $line;
			else if ($lineno - $cur_lineno > -3 && $lineno - $cur_lineno < 0)
				$frame['suffix'][] = $line;
		}
		fclose($fh);

		return $frame;
	}
}