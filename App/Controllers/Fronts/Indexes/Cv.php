<?php

namespace App\Controllers\Fronts\Indexes;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;
use \App\Models\Xml\Entities\Document;
use \App\Models\Contact;
use \App\Models\Training;

class Cv extends \App\Controllers\Fronts\Index {

	protected static array $techStarts = [
		'js'		=> '2007-08',
		'php'		=> '2009-11',
		'mySql'		=> '2010-01',
		'csharp'	=> '2015-03',
		'msSql'		=> '2016-04',
		'vb'		=> '2017-05',
		'shell'		=> '2018-01',
		'ts'		=> '2020-01',
		'htmlCss'	=> '2006-08',
		'ciCd'		=> '2010-12',
		'xml'		=> '2012-10',
		'adobe'		=> '2007-09',
		'corel'		=> '2013-02',
		'autodesk'	=> '2013-02',
	];

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$this->indexRender();
	}
	
	public function CompleteAction (): void {
		$parentDocPath = dirname($this->document->GetOriginalPath());
		$parentDoc = Document::GetByFilePath($parentDocPath);
		$this->navigationBreadCrumbs->AddItem(new BreadCrumbItem(
			text: $parentDoc->GetNavigationTitle(),
			url: $parentDoc->GetUrl()
		));
		$this->view->title = $this->document->GetTitle();
		$this->navigationBreadCrumbs->AddItem(new BreadCrumbItem(
			text: $this->document->GetNavigationTitle(),
		));
		$this->indexRender();
	}

	protected function indexRender (): void {
		$this->view->training = new Training;
		$this->view->contacts = Contact::GetData();
		$this->view->techYears = $this->completeTechYears();

		$this->assets->Cv();
		$this->renderAction();
	}

	protected function completeTechYears (): \stdClass {
		$currentDateTime = new \DateTime('now');
		$techYears = new \stdClass;
		foreach (self::$techStarts as $techKey => $techStart) {
			$startTimestamp = strtotime($techStart . '-01');
			$startDate = new \DateTime();
			$startDate->setTimestamp($startTimestamp);
			$dateDiff = $currentDateTime->diff($startDate, TRUE);
			$techYears->{$techKey} = intval($dateDiff->format('%y'));
		}
		return $techYears;
	}
}
