<?php

namespace App\Controllers\Fronts;

use \MvcCore\Ext\Routers\IMedia;

use \App\Models\Xml\Entities\Document;

class Sitemap extends \App\Controllers\Front {
	
	public function Init (): void {
		parent::Init();
		$this->router->SetMediaSiteVersion(IMedia::MEDIA_VERSION_FULL);
	}

	public function IndexAction (): void {
		$this->layout = NULL;
		$this->view->urls = Document::GetSitemapUrls();
		$this->response->SetHeader(
			'Content-Type', 'application/xml'
		);
	}

}
