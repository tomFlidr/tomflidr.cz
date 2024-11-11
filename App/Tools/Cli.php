<?php

namespace App\Tools;

class Cli {

	private static string $_cliDirFp = '/App/Cli';

	private static ?bool $_isWin = NULL;
	
	public static function GetIsWin (): bool {
		return self::$_isWin ?: (
			self::$_isWin = mb_substr(mb_strtolower(PHP_OS), 0, 3) === 'win'
		);
	}
	
	public static function GetCliDirFullPath (): string {
		if (self::$_cliDirFp !== NULL) 
			return self::$_cliDirFp;
		$app = \MvcCore\Application::GetInstance();
		return self::$_cliDirFp = implode('/', [
			$app->GetRequest()->GetAppRoot(),
			$app->GetAppDir(),
			$app->GetCliDir(),
		]);
	}

	public static function RunScript (string $scriptName, array $args = []): string {
		$cmd = $scriptName . (self::GetIsWin() ? '.cmd' : '.sh');
		if (count($args) > 0) {
			foreach ($args as $arg) {
				if (
					mb_strpos($arg, ' ') !== FALSE && (
						mb_substr($arg, 0, 1) !== '"' || 
						mb_substr($arg, 0, 1) !== "'"
					)
				) $arg = '"' . $arg . '"';
				$cmd .= ' ' . $arg;
			}
		}
		if (!self::GetIsWin()) 
			$cmd = 'sh -c "sh ' . $cmd . '" 2>&1';
		return self::System($cmd, self::GetCliDirFullPath());
	}

	public static function System (string $cmd, ?string $dirPath = NULL): bool | string {
		if (!function_exists('system')) return FALSE;
		$dirPathPresented = $dirPath !== NULL && mb_strlen($dirPath) > 0;
		$cwd = '';
		if ($dirPathPresented) {
			$cwd = getcwd();
			chdir($dirPath);
		}
		ob_start();
		system($cmd);
		$sysOut = ob_get_clean();
		if ($dirPathPresented) chdir($cwd);
		return trim($sysOut);
	}
}