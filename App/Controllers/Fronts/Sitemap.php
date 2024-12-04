<?php

namespace App\Controllers\Fronts;

use \MvcCore\Router\IConstants as RouterConsts;
use \MvcCore\Ext\Routers\IMedia;
use \MvcCore\Ext\Routers\ILocalization;

use \App\Models\Xml\Entities\Document;

class Sitemap extends \App\Controllers\Front {
	
	public function IndexAction (): void {
		$this->layout = NULL;
		$this->view->urls = Document::GetSitemapUrls();
		$this->response->SetHeader(
			'Content-Type', 'application/xml'
		);
	}

}
