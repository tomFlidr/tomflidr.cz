<?php

namespace App\Controllers;

class Projects extends \App\Controllers\Index {

    public function IndexAction (): void {
		$this->view->title = 'Open Source Projects';
		$this->assets->Projects();
    }
	
}
