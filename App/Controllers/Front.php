<?php

namespace App\Controllers;

use \MvcCore\Request\IConstants as ReqConsts;

use \App\Controllers\Fronts\Navigations as CtrlNavigations,
	\App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class Front extends Base {

	protected bool $renderNavigations;
	protected ?CtrlNavigations\Main $navigationMain = NULL;
	protected ?CtrlNavigations\BreadCrumbs $navigationBreadCrumbs = NULL;

	public function Init (): void {
		parent::Init();
		if (!$this->viewEnabled) return;

		// there is no POST with template rendering in whole website:
		$method = $this->request->GetMethod();
		$this->renderNavigations = (
			$method === ReqConsts::METHOD_GET || 
			$method === ReqConsts::METHOD_HEAD
		);

		if (!$this->renderNavigations) return;

		$this->navigationMain = new CtrlNavigations\Main($this);
		$this->navigationBreadCrumbs = new CtrlNavigations\BreadCrumbs($this);
		$this
			->AddChildController($this->navigationMain, 'navigationMain')
			->AddChildController($this->navigationBreadCrumbs, 'navigationBreadCrumbs');
	}

	public function PreDispatch (): void {
		parent::PreDispatch();
		if (
			!$this->viewEnabled ||
			!$this->renderNavigations
		) return;

		$this->navigationBreadCrumbs->AddItem(new BreadCrumbItem(
			text: $this->translate('Home'),
			url: $this->Url('home'),
		));
		$this->view->navigationMain = $this->navigationMain;
		$this->view->navigationBreadCrumbs = $this->navigationBreadCrumbs;
	}
	
	public function ThemeAction (): void {
		$this->assets->Move2NextTheme();
		$sourceUrl = $this->GetParam('source', FALSE);
		$redirectUrl = $this->Url('home');
		if ($sourceUrl !== NULL) {
			$sourceUrl = rawurldecode($sourceUrl);
			$sourceHost = \MvcCore\Tool::ParseUrl($sourceUrl, PHP_URL_HOST);
			if ($sourceHost === $this->request->GetHostName()) {
				$redirectUrl = str_replace(["<", ">", "\n", "\r"], ["&lt;", "&gt;", "", ""], $sourceUrl);
			}
		}
		self::Redirect($redirectUrl, \MvcCore\Response::SEE_OTHER);
	}

	protected function setUpTitleAndBreadCrumbsText (?string $viewTitle = NULL, ?string $bcNavText = NULL): string {
		if ($viewTitle !== NULL) {
			$translatedTitle = $this->translate($viewTitle);
		} else {
			$translatedTitle = $this->document->GetTitle();	
		}
		if ($bcNavText !== NULL) {
			$translatedBcNavText = $this->translate($bcNavText);
		} else {
			$translatedBcNavText = $translatedTitle;	
		}
		$this->view->title = $translatedTitle;
		$this->navigationBreadCrumbs->AddItem(new BreadCrumbItem(
			text: $translatedBcNavText,
		));
		return $translatedTitle;
	}

}
