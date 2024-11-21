<?php

namespace App\Models\Bases;

/**
 * @mixin \App\Models\Base
 */
trait AppObjects {
	
	private static ?\MvcCore\Application $_app = NULL;
	private static ?\MvcCore\Request $_req = NULL;
	private static ?\MvcCore\Ext\ICache $_cache = NULL;
	private static ?\App\Routers\MediaAndLocalization $_router = NULL;
	/** @var array<string,\MvcCore\Ext\ITranslator> */
	private static array $_translators = [];
	
	public static function GetApp (): \MvcCore\Application {
		if (self::$_app === NULL)
			self::$_app = \MvcCore\Application::GetInstance();
		return self::$_app;
	}

	public static function GetCache (): \MvcCore\Ext\ICache {
		if (self::$_cache === NULL)
			self::$_cache = \MvcCore\Ext\Cache::GetStore();
		return self::$_cache;
	}
	
	public static function GetRequest (): \MvcCore\Request {
		if (self::$_req === NULL)
			self::$_req = self::GetApp()->GetRequest();
		return self::$_req;
	}

	public static function GetRouter (): \App\Routers\MediaAndLocalization {
		if (self::$_router === NULL)
			self::$_router = self::GetApp()->GetRouter();
		return self::$_router;
	}

	public static function GetTranslator (?string $localization = NULL): \MvcCore\Ext\Translators\Csv {
		$localization = $localization ?: implode('_', self::GetRouter()->GetLocalization(FALSE));
		if (isset(self::$_translators[$localization]))
			return self::$_translators[$localization];
		$translator = \MvcCore\Ext\Translators\Csv::GetInstance($localization)
			->SetCache(self::GetCache());
		return self::$_translators[$localization] = $translator;
	}

}
