<?php

namespace App\Controllers;

class Cv extends \App\Controllers\Index {

    public function IndexAction (): void {
		$this->view->title = 'CV';
		$this->assets->Cv();
    }
	
}
