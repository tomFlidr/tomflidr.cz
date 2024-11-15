<?php

namespace App\Controllers\Fronts\Navigations;

use \App\Models\Navigations\Set,
	\App\Models\Navigations\Main as MainNavigationModel,
	\App\Models\Xml\Document;

class Main extends \MvcCore\Controller {

	/** @var \App\Controllers\Front */
	protected $parentController;
	
	protected string $requestPath;
	protected string $mediaSiteVersion;
	
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
		
		$this->requestPath = $this->request->GetOriginalPath();
		$this->mediaSiteVersion = $this->request->GetMediaSiteVersion();
		$localizationArr = $this->router->GetLocalization(FALSE);
		
		// Complete cached data with urls:
		$mainItems = MainNavigationModel::GetData(
			$this->mediaSiteVersion, $localizationArr
		);

		$this->view->items = $this->completeItemsSelected($mainItems);
		$this->view->nonce = \MvcCore\Ext\Tools\Csp::GetInstance()->GetNonce();
	}

	protected function completeItemsSelected (Set $mainItems): Set {
		$currentRoute = $this->router->GetCurrentRoute();
		[$crCtrl, $crAction] = [$currentRoute->GetController(), $currentRoute->GetAction()];
		$currentRouteName = "{$crCtrl}:{$crAction}";
		foreach ($mainItems as $groupItem) {
			/** @var \App\Models\Navigations\Item $groupItem */
			$subItems = $groupItem->GetItems();
			if ($subItems !== NULL) {
				foreach ($subItems as $subItem) {
					if (
						mb_strpos($subItem->GetUrl(), $this->requestPath) === 0 && (
							$currentRouteName === $subItem->GetRouteName() ||
							in_array($currentRouteName, $subItem->GetSubRouteNames(), TRUE)
						)
					) {
						$groupItem->SetSelected(TRUE);
						$subItem->SetSelected(TRUE);
						break 2;
					}
				}
				if (!$groupItem->GetSelected()) {
					if (
						mb_strpos($groupItem->GetUrl(), $this->requestPath) === 0 && (
							$currentRouteName === $groupItem->GetRouteName() ||
							in_array($currentRouteName, $groupItem->GetSubRouteNames(), TRUE)
						)
					) {
						$groupItem->SetSelected(TRUE);
						break;
					}
				}
			} else {
				if (
					mb_strpos($groupItem->GetUrl(), $this->requestPath) === 0 && (
						$currentRouteName === $groupItem->GetRouteName() ||
						in_array($currentRouteName, $groupItem->GetSubRouteNames(), TRUE)
					)
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
		return $this->view->RenderLayout('/header/navigation-main.' . $this->mediaSiteVersion);
	}
}