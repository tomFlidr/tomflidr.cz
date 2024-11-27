<?php

namespace App\Controllers\Fronts\Indexes;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;
use DateTime;

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
	];

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$currentDateTime = new DateTime('now');
		$techYears = new \stdClass;
		foreach (self::$techStarts as $techKey => $techStart) {
			$startTimestamp = strtotime($techStart . '-01');
			$startDate = new \DateTime();
			$startDate->setTimestamp($startTimestamp);
			$dateDiff = $currentDateTime->diff($startDate, TRUE);
			$techYears->{$techKey} = intval($dateDiff->format('%y'));
		}
		$this->view->techYears = $techYears;
		x($techYears);
		$this->assets->Cv();
		$this->renderAction();
	}

}
