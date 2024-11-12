<?php

namespace App\Controllers\Fronts;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class Services extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbs('Services');
		$this->assets->Services();
    }
	
}
