<?php

namespace App\Views\Helpers;

class Nl2BrHelper extends \MvcCore\Ext\Views\Helpers\AbstractHelper {
	
	protected static $instance = NULL;

	public function Nl2Br (string $text): string {
		return str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "<br />"], $text);
	}
}