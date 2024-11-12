<?php

namespace App\Controllers\Fronts\Navigations;

use App\Models\Navigations\Set;

class Main extends \MvcCore\Controller {

	/** @var \App\Controllers\Front */
	protected $parentController;

	public function __construct (\App\Controllers\Front $parentController) {
		$this->parentController = $parentController;
	}

	public function PreDispatch (): void {
		parent::PreDispatch();

		$mediaSiteVersion = $this->request->GetMediaSiteVersion();
		$localizationArr = $this->router->GetLocalization(FALSE);
		
		// Complete cached data with urls:
		$mainItems = \App\Models\Navigations\Main::GetData(
			$mediaSiteVersion, $localizationArr
		);

		$this->view->items = $this->completeItemsSelected($mainItems);
		$this->view->nonce = \MvcCore\Ext\Tools\Csp::GetInstance()->GetNonce();
		$this->view->mediaSiteVersion = $mediaSiteVersion;
		$this->view->localization = implode('-', $localizationArr);
	}

	protected function completeItemsSelected (Set $mainItems): Set {
		$currentRouteName = $this->router->GetCurrentRoute()?->GetName();
		foreach ($mainItems as $groupItem) {
			/** @var \App\Models\Navigations\Item $groupItem */
			$subItems = $groupItem->GetItems();
			if ($subItems !== NULL) {
				foreach ($subItems as $subItem) {
					if (
						$currentRouteName === $subItem->GetRouteName() ||
						in_array($currentRouteName, $subItem->GetSubRouteNames(), TRUE)		
					) {
						$groupItem->SetSelected(TRUE);
						$subItem->SetSelected(TRUE);
						break 2;
					}
				}
				if (!$groupItem->GetSelected()) {
					if (
						$currentRouteName === $groupItem->GetRouteName() ||
						in_array($currentRouteName, $groupItem->GetSubRouteNames(), TRUE)	
					) {
						$groupItem->SetSelected(TRUE);
						break;
					}
				}
			} else {
				if (
					$currentRouteName === $groupItem->GetRouteName() ||
					in_array($currentRouteName, $groupItem->GetSubRouteNames(), TRUE)	
				) {
					$groupItem->SetSelected(TRUE);
					break;
				}
			}
		}
		return $mainItems;
	}

	/**
	 * @param string $controllerOrActionNameDashed 
	 * @param string $actionNameDashed 
	 */
	public function Render ($controllerOrActionNameDashed = NULL, $actionNameDashed = NULL): string {
		$this->dispatchState = \MvcCore\Controller\IConstants::DISPATCH_STATE_RENDERED;
		return $this->view->RenderLayout('/header/navigation-main');
	}
}