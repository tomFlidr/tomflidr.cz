<?php

namespace App\Controllers\Fronts;

class Cv extends \App\Controllers\Front {

    public function IndexAction (): void {
		$this->view->title = 'CV';
		$this->assets->Cv();
    }
	
}
