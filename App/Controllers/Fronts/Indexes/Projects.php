<?php

namespace App\Controllers\Fronts\Indexes;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class Projects extends \App\Controllers\Fronts\Index {

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$this->assets->Projects();
		$this->renderAction();
	}
	
}
