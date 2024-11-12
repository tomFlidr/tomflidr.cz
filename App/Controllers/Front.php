<?php

namespace App\Controllers;

use \App\Controllers\Fronts\Navigations as CtrlNavigations,
	\App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class Front extends Base {

	protected ?CtrlNavigations\Main $navigationMain = NULL;
	protected ?CtrlNavigations\BreadCrumbs $navigationBreadCrumbs = NULL;

	public function Init (): void {
		parent::Init();
		if (!$this->viewEnabled) return;

		$this->navigationMain = new CtrlNavigations\Main($this);
		$this->navigationBreadCrumbs = new CtrlNavigations\BreadCrumbs($this);
		$this
			->AddChildController($this->navigationMain, 'navigationMain')
			->AddChildController($this->navigationBreadCrumbs, 'navigationBreadCrumbs');
	}

	public function PreDispatch (): void {
		parent::PreDispatch();
		if (!$this->viewEnabled) return;

		$this->navigationBreadCrumbs->AddItem(new BreadCrumbItem(
			text: $this->translate('Home'),
			url: $this->Url('home'),
		));
		$this->view->navigationMain = $this->navigationMain;
		$this->view->navigationBreadCrumbs = $this->navigationBreadCrumbs;
	}

	protected function setUpTitleAndBreadCrumbs (string $title): string {
		$translatedTitle = $this->translate('CV');
		$this->view->title = $translatedTitle;
		$this->assets->Cv();
		$this->navigationBreadCrumbs->AddItem(new BreadCrumbItem(
			text: $translatedTitle,
		));
		return $translatedTitle;
	}

}
