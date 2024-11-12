<?php

namespace App\Models\Bases;

trait Helpers {
	
	public static function Url ($controllerActionOrRouteName, array $params = []): string {
		if (self::$_router === NULL) self::GetRouter();
		return self::$_router->Url($controllerActionOrRouteName, $params);
	}
	
	public static function Translate (string $key, array $replacements = []): string {
		return self::GetTranslator()->Translate($key, $replacements);
	}

}
