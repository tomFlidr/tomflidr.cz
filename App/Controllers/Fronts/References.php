<?php

namespace App\Controllers\Fronts;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class References extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbs();
		$this->assets->References();
    }
	
}
