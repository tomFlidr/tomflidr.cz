<?php

namespace App\Controllers;

use \MvcCore\Ext\Tools\Csp,
	\MvcCore\Ext\Tools\Csp\IConstants as CspConsts;

class Base extends \MvcCore\Controller {

	protected $layout = 'standard';
	
	protected ?\App\Controllers\Common\Assets $assets = NULL;

	protected ?\MvcCore\Ext\Translators\Csv $translator = NULL;
	
	public function GetTranslator (): ?\MvcCore\Ext\Translators\Csv {
		return $this->translator;
	}
	
	public function Init () {
		parent::Init();

		$localization = implode('_', $this->router->GetLocalization(FALSE));

		$this->translator = \MvcCore\Ext\Translators\Csv::GetInstance($localization);
		$this->translator->SetCache(\MvcCore\Ext\Cache::GetStore());
		
		// set language and country locale
		\MvcCore\Ext\Tools\Locale::SetLocale(LC_ALL, "{$localization}.UTF-8");

		// set date time zone
		$sysCfg = $this->GetConfigSystem();
		date_default_timezone_set($sysCfg->app->timezoneDefault);
	}
	
	public function PreDispatch (): void {
		if ($this->viewEnabled) {
			$this->view = $this->createView(TRUE);
			if ($this->assets === NULL)
				$this->AddChildController(
					$this->assets = new \App\Controllers\Common\Assets($this)
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
