<?php

namespace App\Models\Navigations;

use \App\Routers\MediaAndLocalization,
	\App\Models\Xml\Entities\Document,
	\App\Models\Navigations\Set,
	\App\Models\Navigations\Item;


class Main extends \App\Models\Base {
	
	public const CACHE_TAGS = ['nav-main'];

	public static function GetData (string $mediaSiteVersion, array $localization): Set {
		$cacheKey = 'navigation_main_' . $mediaSiteVersion . '_' . implode('-', $localization);
		return self::GetCache()->Load(
			$cacheKey, 
			function (
				\MvcCore\Ext\ICache $cache, $cacheKey
			) use (
				$mediaSiteVersion, $localization
			) {
				$data = self::loadData(
					$mediaSiteVersion, $localization
				);
				$cache->Save($cacheKey, $data, NULL, static::CACHE_TAGS);
				return $data;
			}
		);
	}

	protected static function loadData (
		string $mediaSiteVersion, array $localization
	): Set {
		$data = [];
		$router = self::GetRouter();
		$localizationWeb = implode('-', $localization);
		$mediaKey = $router::URL_PARAM_MEDIA_VERSION;
		$localKey = $router::URL_PARAM_LOCALIZATION;
		$urlParams = [
			$mediaKey	=> $mediaSiteVersion,
			$localKey	=> $localizationWeb,
		];
		[$lang] = $localization;
		$firstLevelDocsPath = '/' . $lang;
		$firstLevelDocsInclHome = Document::GetByDirPath(
			$firstLevelDocsPath, TRUE, ['sequence' => 'asc', 'title' => 'asc'], TRUE
		);
		$docRoute = $router->GetRoute(Document::ROUTE_NAME);
		foreach ($firstLevelDocsInclHome as $doc) {
			$docCtrl = $doc->GetController() ?? $docRoute->GetController();
			$docAction = $doc->GetAction() ?? $docRoute->GetAction();
			$routeName = "{$docCtrl}:{$docAction}";
			$data[] = new Item(
				title		: $doc->GetNavigationTitle(),
				description	: $doc->GetNavigationSubtitle(),
				url			: $doc->GetUrl($urlParams),
				routeName	: $routeName
			);
		}
		return new Set($data);
	}

}