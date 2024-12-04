<?php

namespace App\Controllers\Fronts\Indexes;

use \MvcCore\Request\IConstants as ReqConsts;

class Contacts extends \App\Controllers\Fronts\Index {

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$this->view->contacts = \App\Models\Contact::GetData();
		$this->assets->Contacts();
		$this->renderAction('contacts');
	}

	public function PgpKeyAction (): void {
		$contacts = \App\Models\Contact::GetData();
		$download = $this->request->HasParam('download');
		$pgpFullPath = $this->application->GetPathVar(TRUE) . '/' . $contacts->pgpFileName;
		$this->response->SetHeaders([
			'Content-Length'			=> filesize($pgpFullPath),
			'Expires'					=> '0',
			'Cache-Control'				=> 'must-revalidate, post-check=0, pre-check=0'
		]);
		if ($download) {
			$this->response->SetHeaders([
				'Content-Description'		=> 'File Transfer',
				'Content-Transfer-Encoding'	=> 'binary',
				'Content-Disposition'		=> 'filename="' . $contacts->pgpFileName . '"',
			]);
			readfile($pgpFullPath);
			$this->Terminate();
		} else {
			$this->response->SetHeaders([
				'Content-Type'				=> 'text/plain',
				'Content-Encoding'			=> 'utf-8',
			]);
			$this->TextResponse(file_get_contents($pgpFullPath));
		}
	}

}
