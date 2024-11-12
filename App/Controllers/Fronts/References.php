<?php

namespace App\Controllers\Fronts;

class References extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->view->title = 'References';
		$this->assets->References();
    }
	
}
