<?php

namespace App\Models\Bases;

/**
 * @mixin \App\Models\Base
 * @property \App\Routers\MediaAndLocalization $_router
 */
trait Helpers {
	
	public static function Url ($controllerActionOrRouteName, array $params = []): string {
		if (self::$_router === NULL) self::GetRouter();
		return self::$_router->Url($controllerActionOrRouteName, $params);
	}
	
	public static function Translate (string $key, array $replacements = []): string {
		return self::GetTranslator()->Translate($key, $replacements);
	}

}
