<?php

namespace App\Views\Layouts;

use \App\Controllers\Fronts\Navigations,
	\App\Models\Xml\Document;

abstract class standard extends \MvcCore\View {
	
	var string $appName;

	var string $basePath;

	var ?string $gaTrackingId = NULL;

	var bool $isDevelopment;

	var bool $isProduction;

	var ?Document $document;

	var string $themeCurrent;

	var string $themeNext;
	
	/** 
	 * @var object{
	 *		"Environment":string,
	 *		"Layout":string,
	 *		"Theme":string,
	 *		"MediaSiteVersion":string,
	 *		"Controller":string,
	 *		"Action":string
	 * } */
	var \stdClass $coreConfig;

	var string $localization;

	var string $mediaSiteVersion;
	
	var string $nonce;

	var string $title;

	var Navigations\BreadCrumbs $navigationBreadCrumbs;

	var Navigations\Main $navigationMain;

	public abstract function Translate (string $key, array $replacements = []): string;

	public abstract function Nl2Br (string $text): string;
}