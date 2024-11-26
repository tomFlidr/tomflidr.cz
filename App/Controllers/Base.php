<?php

namespace App\Controllers;

use \MvcCore\Ext\Tools\Csp,
	\MvcCore\Ext\Tools\Csp\IConstants as CspConsts,
	\MvcCore\Ext\Translators\Csv as CsvTranslator;

use \App\Controllers\Common\Assets,
	\App\Models\Xml\Entities\Document;

/**
 * @property \App\Routers\MediaAndLocalization $router
 */
class Base extends \MvcCore\Controller {

	protected $layout = 'standard';
	
	protected ?Assets $assets = NULL;

	protected ?CsvTranslator $translator = NULL;
	
	protected ?Document $document = NULL;

	public function GetTranslator (): ?CsvTranslator {
		return $this->translator;
	}

	public static function PostRouteHandler () {
		// complete necessary global objects
		$app = \MvcCore\Application::GetInstance();
		$router = $app->GetRouter();
		$req = $app->GetRequest();

		$defaultParams = & $router->GetDefaultParams();
		if (isset($defaultParams['doc'])) {
			$document = & $defaultParams['doc'];
		} else {
			// try to complete document instance
			$rawDocumentPath = $req->HasParam('path')
				? $req->GetParam('path', FALSE)
				: $req->GetPath();
			$document = $rawDocumentPath !== NULL
				? Document::GetBestMatchByFilePath($req->GetLang(), $rawDocumentPath)
				: NULL;
		}
		$documentControllerPc = NULL;
		$documentActionPc = NULL;
		// check if document has defined any controller or action
		if ($document) {
			if ($document->GetController() !== NULL) 
				$documentControllerPc = $document->GetController();
			if ($document->GetAction() !== NULL) 
				$documentActionPc = $document->GetAction();
		}
		
		// if there is no document and no definition for controller and action, do nothing more
		if (!$document && !$documentControllerPc && !$documentActionPc) 
			return TRUE;
		
		// if there is $document and doesn't matter if $documentControllerPc or $documentActionPc,
		// try to complete controller class full name, create ctrl instance and set up document
		$controllerNamePc = NULL;
		if ($documentControllerPc) {
			// get controller name from document
			$controllerNamePc = $documentControllerPc;
		} else {
			// get controller name from routed route if any
			$currentRoute = $router->GetCurrentRoute();
			if ($currentRoute === NULL) return $app->DispatchException('No route for request', 404);
			$controllerNamePc = $currentRoute->GetController();
		}
		
		// complete full controller name
		$ctrlClassFullName = $app->CompleteControllerName($controllerNamePc);
		if (!class_exists($ctrlClassFullName)) 
			return $app->DispatchException("Controller class `$documentControllerPc` not found.", 404);
		
		// try to create controller
		$controller = NULL;
		try {
			$controller = $ctrlClassFullName::CreateInstance();
		} catch (\Exception $e) {
			return $app->DispatchException($e->getMessage(), 404);
		}

		// set up controller into application instance
		$app->SetController($controller);

		// if controller is completed successfully to set up document into it 
		// and if there is necessary to redefine routed target, redefine routed 
		// target
		if ($documentControllerPc || $documentActionPc) 
			$router->RedefineRoutedTarget($documentControllerPc, $documentActionPc, FALSE);
		
		// set up document into controller
		$controller->document = $document;

		// return TRUE to continue dispatching
		return TRUE;
	}
	
	public function Init () {
		parent::Init();

		$localization = implode('_', $this->router->GetLocalization(FALSE));

		$this->translator = CsvTranslator::GetInstance($localization);
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
					$this->assets = new Assets($this)
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
		list($langCode, $countryCode) = $this->router->GetLocalization(FALSE);
		$this->view->appName = $sysCfgApp->name;
		$this->view->appDesc = $sysCfgApp->description;
		$this->view->basePath = $this->request->GetBasePath();
		$this->view->langCode = $langCode;
		$this->view->countryCode = $countryCode;
		$this->view->localization = implode('-', [$langCode, $countryCode]);
		$this->view->mediaSiteVersion = $mediaSiteVersion;
		$this->view->coreConfig = (object) [
			'Environment'		=> $this->environment->GetName(),
			'Layout'			=> $this->layout,
			'MediaSiteVersion'	=> $mediaSiteVersion,
			'Controller'		=> $this->controllerName,
			'Action'			=> $this->actionName,
		];
		$this->view->isDevelopment = $this->environment->IsDevelopment();
		$this->view->isProduction = $this->environment->IsProduction();
		$this->view->document = $this->document;
		$themeCurrent = $this->assets->GetThemeCurrent();
		$themeCurrentParts = explode('/', $themeCurrent);
		$this->view->theme = (object) [
			'current'			=> $themeCurrent,
			'currentDcShort'	=> $themeCurrentParts[0],
			'currentDcFull'		=> implode('-', $themeCurrentParts),
			'next'				=> $this->assets->GetThemeNext(),
		];
		$this->view->google = $sysCfgApp->google;
		$this->view->footer = $sysCfgApp->footer;
	}

	private function _preDispatchSetUpCsp (): void {
		$csp = Csp::GetInstance()
			->AllowGoogleAnalytics()
			->AllowGoogleMapsEmbedApi()
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
			// Tracy debugger, AgGrid (standard virtual row rendering)
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
