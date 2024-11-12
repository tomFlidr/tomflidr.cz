<?php

namespace App\Controllers\Fronts;

class Services extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->view->title = 'Services';
		$this->assets->Training();
    }
	
}
