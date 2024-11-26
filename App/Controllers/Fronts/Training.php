<?php

namespace App\Controllers\Fronts;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;

class Training extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$this->assets->Training();
    }
	
}
