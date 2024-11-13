<?php

namespace App\Controllers\Fronts\Navigations;

use \App\Models\Navigations\BreadCrumbs as ModelBreadCrumbs;

class BreadCrumbs extends \MvcCore\Controller {
	
	public static int $minItems2Render = 2;

	/** @var \App\Controllers\Front */
	protected $parentController;

	protected array $cssClasses = ['nav-breadcrumbs', 'glass', 'document'];

	protected ModelBreadCrumbs\Set $items;

	public function __construct (\App\Controllers\Front $parentController) {
		$this->parentController = $parentController;
		$this->items = new ModelBreadCrumbs\Set([]);
	}

	/**
	 * @param \string[] $cssClasses,...
	 */
	public function AddCssClass (...$cssClasses): static {
		$this->cssClasses = array_merge([], $this->cssClasses, $cssClasses);
		return $this;
	}

	public function AddItem (ModelBreadCrumbs\Item $item): static {
		$this->items[] = $item;
		return $this;
	}

	public function IsRendered (): bool {
		$itemsCount = $this->GetCount();
		if ($itemsCount < static::$minItems2Render) return FALSE;
		return TRUE;
	}

	public function RemoveLastItem (): static {
		return $this->RemoveItem(count($this->items) - 1);
	}

	public function RemoveItem (int $index): static {
		$arr = $this->items->getArray();
		array_splice($arr, $index, 1);
		$this->items = new ModelBreadCrumbs\Set($arr);
		return $this;
	}

	public function SetItems (ModelBreadCrumbs\Set $items): static {
		$this->items = $items;
		if ($this->view !== NULL)
			$this->view->items = $this->items;
		return $this;
	}
	
	public function GetItem (int $index): ModelBreadCrumbs\Item {
		return $this->items[$index];
	}
	
	public function GetLastItem (): ModelBreadCrumbs\Item {
		return $this->items[count($this->items) - 1];
	}

	public function GetItems (): ModelBreadCrumbs\Set {
		return $this->items;
	}

	public function GetCount (): int {
		return count($this->items);
	}

	/**
	 * @param string $controllerOrActionNameDashed 
	 * @param string $actionNameDashed 
	 */
	public function Render ($controllerOrActionNameDashed = NULL, $actionNameDashed = NULL): string {
		if (!$this->IsRendered()) return '';
		$this->items[0]->SetIsFirst(TRUE);
		$this->items[$this->GetCount() - 1]->SetIsLast(TRUE);
		$this->view->items = $this->items;
		$this->dispatchState = \MvcCore\Controller\IConstants::DISPATCH_STATE_RENDERED;
		return $this->view->RenderLayout('/header/navigation-bread-crumbs');
	}
}