<?php

namespace App\Controllers\Common;

use \MvcCore\Ext\Views\Helpers\CssHelper;

class Assets extends \MvcCore\Controller {

	public const PRINT_PREVIEW_PARAM = 'print-preview';
	public const THEME_SESSION_NAME = 'theme';
	
	protected string $mediaSiteVersion;
	protected array  $assignedLibraries = [];
	protected ?string $theme = NULL;
	protected string $printMedia = 'print';
	
	public function __construct (\App\Controllers\Base $controller) {
		$this->parentController = $controller;
		$req = $controller->GetRequest();
		$this->mediaSiteVersion = $req->GetMediaSiteVersion();
		// setup theme
		$session = $this->getSessionTheme();
		if (isset($session->theme)) {
			$this->theme = $session->theme;
		} else {
			$sysCfg = $this->parentController->GetConfigSystem();
			$this->theme = $sysCfg->app->themes->default;
			$session->theme = $this->theme;
		}
		if ($req->HasParam(self::PRINT_PREVIEW_PARAM))
			$this->printMedia = 'screen';
	}
	
	public function GetThemeCurrent (): string {
		return $this->theme;
	}

	public function GetThemeNext (): string {
		$themesCfg = $this->parentController->GetConfigSystem()->app->themes;
		$themeIndex = array_search($this->theme, $themesCfg->list, TRUE);
		$themeIndex++;
		if ($themeIndex === count($themesCfg->list)) 
			$themeIndex = 0;
		return $themesCfg->list[$themeIndex];
	}

	public function Move2NextTheme (): string {
		$session = $this->getSessionTheme();
		$session->theme = $this->GetThemeNext();
		return $session->theme;
	}

	protected function getSessionTheme (): \MvcCore\Session {
		$this->application = $this->parentController->GetApplication();
		$sysCfg = $this->parentController->GetConfigSystem();
		$session = self::GetSessionNamespace(self::THEME_SESSION_NAME);
		$session->SetExpirationSeconds($sysCfg->app->session->identity);
		return $session;
	}
	
	public function PreDispatch () {
		$this->viewEnabled = FALSE; // to not render this virtual controller
		$this->view = $this->parentController->GetView();
	}

	public function Base (): void {
		/** @var $this \App\Controllers\Base */
		$static = $this->application->GetPathStatic();
		
		$this->view->Css('headAll')
			->Append($static . "/css/all/resets.css")
			->Append($static . "/css/all/old-browsers-warning.css")
			->Append($static . "/css/all/fonts.css")
			->Append($static . "/css/all/icons.css")
			->Append($static . "/css/all/common-rules.css")
			->Append($static . "/css/all/links.css")
			->Append($static . "/css/all/layout.{$this->mediaSiteVersion}.css")
			->Append($static . "/css/all/document.css")
			->Append($static . "/css/all/document.{$this->mediaSiteVersion}.css");
		
		$themeParts = explode('/', $this->theme);
		$themePartBase = $themeParts[0];
		$themeFullSuffix = implode('.', $themeParts);
		$this->view->Css('headMediaTheme')
			->Append($static . "/css/all/themes/{$themePartBase}/common-rules.{$themePartBase}.css")
			->Append($static . "/css/all/themes/{$themePartBase}/links.{$themePartBase}.css")
			->Append($static . "/css/all/themes/{$themePartBase}/links.{$themePartBase}.css")
			->Append($static . "/css/all/themes/{$themePartBase}/document.{$themePartBase}.css")
			->Append($static . "/css/all/themes/{$themePartBase}/layout.{$this->mediaSiteVersion}.{$themePartBase}.css")
			->Append($static . "/css/all/themes/{$this->theme}/layout.{$this->mediaSiteVersion}.{$themeFullSuffix}.css");
		
		$this->view->Css('headAllPrint')
			->Append(media: $this->printMedia, path: $static . '/css/print/fonts.css')
			->Append(media: $this->printMedia, path: $static . '/css/print/common-rules.css')
			->Append(media: $this->printMedia, path: $static . '/css/print/links.css')
			->Append(media: $this->printMedia, path: $static . '/css/print/layout.css')
			->Append(media: $this->printMedia, path: $static . '/css/print/document.css');
		
		if ($this->request->HasParam(self::PRINT_PREVIEW_PARAM)) {
			$headAllBundle = $this->view->Css('headAllPrint');
			$printItems = & $headAllBundle->GetItems();
			foreach ($printItems as & $printItem)
				$printItem->media = CssHelper::MEDIA_ALL;
			$headAllBundle->Prepend(media: 'screen', path: $static . '/css/print/preview.css');
		}
		
		$this->view->Js('headAll')
			->Append($static . "/js/libs/prototype-extensions.js")
			//->Append($static . "/js/libs/intl-messageformat.iife.js") // translations
			->Append($static . "/js/build/Core/Layouts/Name.js")
			->Append($static . "/js/build/Core/Layout.js")
			->Append($static . "/js/build/Core/MediaSiteVersions/Name.js")
			->Append($static . "/js/build/Core/MediaSiteVersion.js")
			->Append($static . "/js/build/Core/Environments/Name.js")
			->Append($static . "/js/build/Core/Environment.js")
			// translations:
			//->Append($static . "/js/build/Core/Translators/EnumTransform.js")
			//->Append($static . "/js/build/Core/Translators/Record.js")
			//->Append($static . "/js/build/Core/Translators/Replacements.js")
			//->Append($static . "/js/build/Core/Translators/Translations.js")
			//->Append($static . "/js/build/Core/Translator.js")
			->Append($static . "/js/build/Core/Pages/Selectors.js")
			->Append($static . "/js/build/Core/Page.js")
			
			->Append($static . '/js/build/Front/Navigations/Mobiles/Events/Base.js')
			->Append($static . '/js/build/Front/Navigations/Mobiles/Events/ClickEvent.js')
			->Append($static . '/js/build/Front/Navigations/Mobiles/Events/SelectChangeEvent.js')
			->Append($static . '/js/build/Front/Navigations/Mobiles/EventHandler.js')
			->Append($static . '/js/build/Front/Navigations/Mobiles/Elements.js')
			->Append($static . '/js/build/Front/Navigations/Mobiles/Handlers.js')
			->Append($static . '/js/build/Front/Navigations/Mobiles/TouchHandlers.js')
			->Append($static . '/js/build/Front/Navigations/Mobile.js')
			
			->Append($static . "/js/build/Front/Pages/Selectors.js")
			->Append($static . "/js/build/Front/Page.js");
	}
	
	public function Index (): void {
		/** @var $this \App\Controllers\Front */
		$static = $this->application->GetPathStatic();
		$this->view->Css('headPage')
			->Append($static . '/css/pages/index.css');
	}
	
	public function Cv (): void {
		/** @var $this \App\Controllers\Front */
		$static = $this->application->GetPathStatic();
		
		$this->view->Css('headPage')
			->Append($static . '/css/pages/cv.css')
			->Append($static . '/css/pages/cv.print.css', media: $this->printMedia);
		if ($this->request->HasParam(self::PRINT_PREVIEW_PARAM)) {
			$headPageBundle = $this->view->Css('headPage');
			$printItems = & $headPageBundle->GetItems();
			foreach ($printItems as & $printItem)
				$printItem->media = CssHelper::MEDIA_ALL;
		}
	}
	
	public function Contacts (): void {
		/** @var $this \App\Controllers\Front */
		$static = $this->application->GetPathStatic();
		$this->view->Css('headPage')
			->Append($static . '/css/pages/contacts.css');
	}
	
	public function Projects (): void {
		/** @var $this \App\Controllers\Front */
		//$static = $this->application->GetPathStatic();
		
	}
	
	public function Training (): void {
		/** @var $this \App\Controllers\Front */
		//$static = $this->application->GetPathStatic();
		
	}
	
	public function Services (): void {
		/** @var $this \App\Controllers\Front */
		//$static = $this->application->GetPathStatic();
		
	}
	
	public function References (): void {
		/** @var $this \App\Controllers\Front */
		$static = $this->application->GetPathStatic();
		$this->view->Css('headPage')
			->Append($static . '/css/pages/references.css');
		
	}
	
}