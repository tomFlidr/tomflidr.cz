<?php

namespace App\Controllers\Fronts;

class Projects extends \App\Controllers\Base {

    public function IndexAction (): void {
		$this->view->title = 'Open Source Projects';
		$this->assets->Projects();
    }
	
}
