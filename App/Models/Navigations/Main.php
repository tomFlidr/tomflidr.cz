<?php

namespace App\Models\Navigations;

use \App\Routers\MediaAndLocalization,
	\App\Models\Navigations\Set,
	\App\Models\Navigations\Item;


class Main extends \App\Models\Base {
	
	public const CACHE_TAGS = ['nav-main'];

	protected static array $items = [
		'home'			=> [
			'title'			=> 'Home',
			'description'	=> 'from beginning',
		],
		'cv'			=> [
			'title'			=> 'CV',
			'description'	=> 'all about me'
		],
		'services'		=> [
			'title'			=> 'Services',
			'description'	=> 'how useful I am'
		],
		'references'	=> [
			'title'			=> 'References',
			'description'	=> 'who chose me'
		],
		'training'		=> [
			'title'			=> 'Training',
			'description'	=> 'for professionals'
		],
		'projects'		=> [
			'title'			=> 'Projects',
			'description'	=> 'open source'
		],
		'contact'		=> [
			'title'			=> 'Contact',
			'description'	=> 'feel free to call',
		],
	];

	public static function GetData (string $mediaSiteVersion, array $localization): Set {
		$cacheKey = 'navigation_main_' . $mediaSiteVersion . '_' . implode('-', $localization);
		return self::GetCache()->Load(
			$cacheKey, 
			function (
				\MvcCore\Ext\ICache $cache, $cacheKey
			) use (
				$mediaSiteVersion, $localization
			) {
				$data = self::completeDataWithUrls(
					$mediaSiteVersion, $localization
				);
				$cache->Save($cacheKey, $data, NULL, static::CACHE_TAGS);
				return $data;
			}
		);
	}

	protected static function completeDataWithUrls (
		string $mediaSiteVersion, array $localization
	): Set {
		$data = [];
		$router = self::GetRouter();
		$localizationWeb = implode('-', $localization);
		$translator = self::GetTranslator(implode('_', $localization));
		$mediaKey = $router::URL_PARAM_MEDIA_VERSION;
		$localKey = $router::URL_PARAM_LOCALIZATION;
		$urlParams = [
			$mediaKey	=> $mediaSiteVersion,
			$localKey	=> $localizationWeb,
		];
		foreach (self::$items as $routeName => $itemData) {
			$data[] = new Item(
				title		: $translator->Translate($itemData['title']),
				description	: isset($itemData['description'])
					? $translator->Translate($itemData['description'])
					: NULL,
				url			: self::Url($routeName, $urlParams),
				routeName	: $routeName,
				cssClass	: $itemData['class'] ?? NULL
			);
		}
		return new Set($data);
	}

}