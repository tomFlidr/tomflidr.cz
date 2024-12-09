<?php

namespace App\Views\Layouts;

use \App\Controllers\Fronts\Navigations,
	\App\Models\Xml\Entities\Document;

abstract class standard extends \MvcCore\View {
	
	var string $appName;

	var string $appDesc;

	var string $basePath;

	/**
	 * @var ?object{
	 *		"analyticsId":string,
	 *		"mapsApiKey":string
	 * }
	 */
	var ?\stdClass $google = NULL;

	/**
	 * @var ?object{
	 *		"sourceLink":string
	 * }
	 */
	var ?\stdClass $footer = NULL;

	var bool $isDevelopment;

	var bool $isProduction;

	var ?Document $document;
	
	/**
	 * @var object{
	 *		"urlCanonical":?string,
	 *		"urlAlternate":?string
	 * }
	 */
	var ?\stdClass $canonicalization = NULL;
	
	/**
	 * @var ?object{
	 *		"current":string,
	 *		"next":string,
	 *		"currentDcShort":string,
	 *		"currentDcFull":string
	 * }
	 */
	var ?\stdClass $theme = NULL;
	
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

	var string $langCode;

	var string $countryCode;

	var string $localization;

	var string $mediaSiteVersion;
	
	var string $nonce;

	var string $title;

	var Navigations\BreadCrumbs $navigationBreadCrumbs;

	var Navigations\Main $navigationMain;

	public abstract function Translate (string $key, array $replacements = []): string;

	public abstract function Nl2Br (string $text): string;

	public abstract function XmlLatte (?Entity $model, array $variables = [], string $codeProp = 'body'): string;

}