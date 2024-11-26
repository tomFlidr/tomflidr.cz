<?php

namespace App\Views\Helpers;

/**
 * This helper could be used only in XML document directly inside <doc:body> element.
 */
class HrHelper extends \MvcCore\Ext\Views\Helpers\AbstractHelper {
	
	public function Hr (): string {
		if ($this->view->mediaSiteVersion === 'full') {
			return '</div></div><div class="glass"><div class="document">';
		} else {
			return '<hr />';
		}
	}
}