<?php

namespace App\Views\Layouts;

abstract class standard extends \MvcCore\View {
	
	var string $appName;

	var string $basePath;

	var ?string $gaTrackingId = NULL;

	var bool $isDevelopment;

	var bool $isProduction;
	
	var string $localization;

	var string $mediaSiteVersion;
	
	var string $nonce;

	var string $title;

	public abstract function Translate (string $key, array $replacements = []): string;

	public abstract function Nl2Br (string $text): string;
}