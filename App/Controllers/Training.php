<?php

namespace App\Controllers;

class Training extends \App\Controllers\Index {

    public function IndexAction (): void {
		$this->view->title = 'ICT Training';
		$this->assets->Training();
    }
	
}
