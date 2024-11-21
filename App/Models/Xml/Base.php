<?php

namespace App\Models\Xml;

class Base extends \App\Models\Base {
	
	public const CACHE_TAGS = ['xml'];
	
	protected static string	$dataDir = '~/Var';
	
	public static function GetDataDirFullPath () {
		if (mb_substr(static::$dataDir, 0, 2) === '~/') {
			$appRoot = self::GetApp()->GetPathAppRoot() ;
			static::$dataDir = $appRoot . mb_substr(static::$dataDir, 1);
		}
		return static::$dataDir;
	}
	
	/** @throws \RuntimeException */
	protected static function xmlThrownLibErrors (array $errors): void {
		if (count($errors) > 0) {
			$errorItems = [];
			foreach ($errors as $error) {
				$errorItem = [];
				$errorDetails = [];
				switch ($error->level) {
					case LIBXML_ERR_WARNING:
						$errorItem[] = "Warning {$error->code}: ";
						break;
					case LIBXML_ERR_ERROR:
						$errorItem[] = "Error {$error->code}: ";
						break;
					case LIBXML_ERR_FATAL:
						$errorItem[] = "Fatal Error {$error->code}: ";
						break;
				}
				$errorItem[] = trim($error->message);
				if ($error->file)
					$errorDetails[] = "file: `{$error->file}`";
				if ($error->line)
					$errorDetails[] = "line: {$error->line}";
				if ($error->column)
					$errorDetails[] = "column: {$error->column}";
				if (count($errorDetails) > 0)
					$errorItem[] = " (" . implode(", ", $errorDetails) . ")";
				$errorItems[] = implode('', $errorItem);
			}
			throw new \RuntimeException(implode(".\n", $errorItems));
		}
	}
	
}