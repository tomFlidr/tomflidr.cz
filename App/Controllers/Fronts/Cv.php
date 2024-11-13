<?php

namespace App\Controllers\Fronts;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class Cv extends \App\Controllers\Front {

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbs('CV');
		$this->assets->Cv();
	}
	
}
