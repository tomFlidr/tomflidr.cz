<?php

namespace App\Controllers\Fronts;

class Training extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->view->title = 'ICT Training';
		$this->assets->Training();
    }
	
}
