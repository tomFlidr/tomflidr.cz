<?php

namespace App\Controllers;

use \MvcCore\Ext\Tools\Csp,
	\MvcCore\Ext\Tools\Csp\IConstants as CspConsts;

class Base extends \MvcCore\Controller {

	protected $layout = 'standard';
	
	protected ?\App\Controllers\Assets $assets = NULL;

	protected ?\MvcCore\Ext\Translators\Csv $translator = NULL;
	
	public function GetAssets (): ?\App\Controllers\Assets {
		return $this->assets;
	}
	
	public function GetTranslator (): ?\MvcCore\Ext\Translators\Csv {
		return $this->translator;
	}
	
	public function Init () {
		parent::Init();
		$this->translator = \MvcCore\Ext\Translators\Csv::GetInstance(
			$this->router->GetLocalization(TRUE)
		);
		
		// set language and country locale
		$sysCfg = $this->GetConfigSystem();
		[$reqLang, $reqLocale] = [$this->request->GetLang(), $this->request->GetLocale()];
		\MvcCore\Ext\Tools\Locale::SetLocale(LC_ALL, "{$reqLang}_{$reqLocale}.UTF-8");

		// set date time zone
		date_default_timezone_set($sysCfg->app->timezoneDefault);
	}
	
	public function PreDispatch (): void {
		if ($this->viewEnabled) {
			$this->view = $this->createView(TRUE);
			if ($this->assets === NULL)
				$this->AddChildController(
					$this->assets = new \App\Controllers\Assets($this)
				);
			$this->_preDispatchSetUpAssetsHelper();
		}
		parent::PreDispatch();
		if (!$this->viewEnabled) return;
		$this->preDispatchSetUpViewHelpers();
		$this->_preDispatchSetUpBundles();
		$this->preDispatchSetUpCommonProps();
		$this->_preDispatchSetUpCsp();
	}
	
	private function _preDispatchSetUpAssetsHelper (): void {
		if ($this->layout === NULL) return;
		$cfgAssets = \MvcCore\Config::GetConfigSystem()->assets;
		\MvcCore\Ext\Views\Helpers\Assets::SetGlobalOptions((array) $cfgAssets);
	}

	protected function preDispatchSetUpViewHelpers (): void {
		\App\Views\Helpers\TranslateHelper::GetInstance()
			->SetTranslator($this->translator);
	}
	
	private function _preDispatchSetUpBundles (): void {
		if ($this->layout === NULL) return;
		$this->assets->Base();
	}

	protected function preDispatchSetUpCommonProps (): void {
		$sysCfg = $this->GetConfigSystem();
		$sysCfgApp = $sysCfg->app;
		$mediaSiteVersion = $this->request->GetMediaSiteVersion();
		$this->view->appName = $sysCfgApp->name;
		$this->view->basePath = $this->request->GetBasePath();
		$this->view->localization = $this->router->GetLocalization(TRUE);
		$this->view->mediaSiteVersion = $mediaSiteVersion;
		$this->view->isDevelopment = $this->environment->IsDevelopment();
		$this->view->isProduction = $this->environment->IsProduction();
		$this->view->gaTrackingId = $sysCfgApp->ga?->trackingId ?? '';
	}

	private function _preDispatchSetUpCsp (): void {
		$csp = Csp::GetInstance()
			->AllowGoogleAnalytics()
			->AllowSelf(
				CspConsts::FETCH_SCRIPT_SRC |
				CspConsts::FETCH_CONNECT_SRC |
				CspConsts::FETCH_STYLE_SRC |
				CspConsts::NAVIGATION_FORM_ACTION |
				CspConsts::FETCH_IMG_SRC |
				CspConsts::FETCH_FONT_SRC |
				CspConsts::FETCH_FRAME_SRC
			)
			->AllowNonce(
				CspConsts::FETCH_SCRIPT_SRC |
				CspConsts::FETCH_IMG_SRC
			)
			->AllowSheme(
				CspConsts::FETCH_IMG_SRC |
				CspConsts::FETCH_FONT_SRC,
				'data:'
			)
			// Tracy debugger, ag grid - standard virtual row rendering:
			->AllowUnsafeInline(
				CspConsts::FETCH_STYLE_SRC
			);
		$this->view->nonce = $csp->GetNonce();
		$this->application->AddPreSentHeadersHandler(
			function (\MvcCore\IRequest $req, \MvcCore\IResponse $res) use ($csp) {
				$res->SetHeader($csp->GetHeaderName(), $csp->GetHeaderValue());
			}, 
			999
		);
	}

	protected function translate ($key, $replacements = []) {
		return $this->translator->Translate($key, $replacements);
	}
}
