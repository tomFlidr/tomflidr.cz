<?php

namespace App\Controllers\Fronts\Navigations;

use \App\Models\Navigations\Set,
	\App\Models\Navigations\Item,
	\App\Models\Navigations\Main as MainNavigationModel;

/**
 * @property \App\Routers\MediaAndLocalization $router
 */
class Main extends \MvcCore\Controller {

	/** @var \App\Controllers\Front */
	protected $parentController;
	
	protected string	$requestPath;
	protected string	$requestLocalizationPath;
	protected bool		$requestLocalizationDefault;
	protected bool		$requestIsHome;
	protected string	$mediaSiteVersion;
	
	protected array $cssClasses = ['nav-main'];

	public function __construct (\App\Controllers\Front $parentController) {
		$this->parentController = $parentController;
	}
	
	/**
	 * @param \string[] $cssClasses,...
	 */
	public function AddCssClass (...$cssClasses): static {
		$this->cssClasses = array_merge([], $this->cssClasses, $cssClasses);
		return $this;
	}

	public function PreDispatch (): void {
		parent::PreDispatch();
		
		$this->requestPath = $this->router->EncodeUrl($this->request->GetPath());
		//x($this->request->GetPath(), "req path only");
		$this->mediaSiteVersion = $this->request->GetMediaSiteVersion();
		$localization = $this->router->GetLocalization(TRUE);
		$defaultLocalization = $this->router->GetDefaultLocalization(TRUE);
		$reqPath = $this->request->GetPath();
		$this->requestLocalizationPath = '/' . $localization;
		$this->requestIsHome = $reqPath === '/';
		$this->requestLocalizationDefault = $localization === $defaultLocalization;
		if ($this->requestIsHome && $this->requestLocalizationDefault) {
			$this->requestLocalizationPath = '';
		}
		
		// Complete cached data with urls:
		$mainItems = MainNavigationModel::GetData(
			$this->mediaSiteVersion, $this->router->GetLocalization(FALSE)
		);

		$this->view->items = $this->completeItemsSelected($mainItems);
		$this->view->nonce = \MvcCore\Ext\Tools\Csp::GetInstance()->GetNonce();
	}

	protected function completeItemsSelected (Set $mainItems): Set {
		foreach ($mainItems as $groupItem) {
			/** @var \App\Models\Navigations\Item $groupItem */
			$subItems = $groupItem->GetItems();
			if ($subItems === NULL || count($subItems) > 0) {
				foreach ($subItems as $subItem) {
					if ($this->completeItemSelected($subItem)) {
						$groupItem->SetSelected(TRUE);
						break 2;
					}
				}
				if (!$groupItem->GetSelected()) {
					if ($this->completeItemSelected($groupItem)) break;
				}
			} else {
				//if ($this->completeItemSelected($groupItem)) break;
				$this->completeItemSelected($groupItem);
			}
		}
		return $mainItems;
	}

	protected function completeItemSelected (Item $groupItem): bool {
		$itemUrl = $groupItem->GetUrl();
		$itemUrlIsHome = (
			($this->requestLocalizationDefault && $itemUrl === '/') || 
			(!$this->requestLocalizationDefault && $itemUrl === $this->requestLocalizationPath . '/')
		);
		$itemPath = $itemUrlIsHome
			? $itemUrl
			: mb_substr($itemUrl, strlen($this->requestLocalizationPath));
		$isHomeSelected = $this->requestIsHome && $itemUrlIsHome;
		$isOtherSelected = !$this->requestIsHome && !$itemUrlIsHome;
		if (
			$isHomeSelected ||
			($isOtherSelected && mb_strpos($this->requestPath, $itemPath) === 0)
		) {
			$groupItem->SetSelected(TRUE);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param string $controllerOrActionNameDashed 
	 * @param string $actionNameDashed 
	 */
	public function Render ($controllerOrActionNameDashed = NULL, $actionNameDashed = NULL): string {
		$this->dispatchState = \MvcCore\Controller\IConstants::DISPATCH_STATE_RENDERED;
		return $this->view->RenderLayout('/header/navigation-main.' . $this->mediaSiteVersion);
	}
}