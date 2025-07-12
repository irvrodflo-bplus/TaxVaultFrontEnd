<?php 

function buildEnv(string $path): void {
	if (!file_exists($path)) {
		throw new Exception(".env file not found at path: $path");
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	foreach ($lines as $line) {
		$line = trim($line);

		if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
			continue;
		}

		list($name, $value) = explode('=', $line, 2);

		$name = trim($name);
		$value = trim($value, "'\" ");

		$_ENV[$name] = $value;
	}
}

?>