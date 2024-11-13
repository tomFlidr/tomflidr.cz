<?php

namespace App\Controllers\Common;

class Assets extends \MvcCore\Controller {
	
	protected string $mediaSiteVersion;
	protected array  $assignedLibraries = [];

	public function __construct (\App\Controllers\Base $controller) {
		$this->mediaSiteVersion = $controller->GetRequest()->GetMediaSiteVersion();
		x($this->mediaSiteVersion);
	}

	public function PreDispatch () {
		$this->viewEnabled = FALSE; // to not render this virtual controller
		$this->view = $this->parentController->GetView();
	}

	public function Base (): void {
		/** @var $this \App\Controllers\Base */
		$static = self::$staticPath;
		$this->view->Css('headAll')
			->Append($static . '/css/all/resets.css')
			->Append($static . '/css/all/old-browsers-warning.css')
			->Append($static . '/css/all/fonts.css')
			->Append($static . '/css/all/icons.css')
			->Append($static . '/css/all/common-rules.css')
			->Append($static . '/css/all/links.css')
			->Append($static . "/css/all/layout.{$this->mediaSiteVersion}.css")
			->Append($static . '/css/all/document.css');
		$this->view->Css('headAllPrint')
			->Append(media: 'print', path: $static . '/css/print/common-rules.css')
			->Append(media: 'print', path: $static . '/css/print/links.css')
			->Append(media: 'print', path: $static . '/css/print/layout.css')
			->Append(media: 'print', path: $static . '/css/print/document.css');
		$this->view->Js('headAll')
			->Append($static . "/js/libs/prototype-extensions.js")
			//->Append($static . "/js/libs/intl-messageformat.iife.js")
			->Append($static . "/js/build/Core/Layouts/Name.js")
			->Append($static . "/js/build/Core/Layout.js")
			->Append($static . "/js/build/Core/MediaSiteVersions/Name.js")
			->Append($static . "/js/build/Core/MediaSiteVersion.js")
			->Append($static . "/js/build/Core/Environments/Name.js")
			->Append($static . "/js/build/Core/Environment.js")
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
		$static = self::$staticPath;
		$this->view->Css('headPage')
			->Append($static . '/css/pages/index.css');
	}
	
	public function Cv (): void {
		/** @var $this \App\Controllers\Front */
		$static = self::$staticPath;
		$this->view->Css('headPage')
			->Append($static . '/css/pages/cv.css')
			->Append($static . '/css/pages/cv.print.css', media: 'print');
	}
	
	public function Contact (): void {
		/** @var $this \App\Controllers\Front */
		$static = self::$staticPath;
		
	}
	
	public function Projects (): void {
		/** @var $this \App\Controllers\Front */
		$static = self::$staticPath;
		
	}
	
	public function Training (): void {
		/** @var $this \App\Controllers\Front */
		$static = self::$staticPath;
		
	}
	
	public function Services (): void {
		/** @var $this \App\Controllers\Front */
		$static = self::$staticPath;
		
	}
	
	public function References (): void {
		/** @var $this \App\Controllers\Front */
		$static = self::$staticPath;
		
	}
	
}