<?php

namespace App\Controllers;

use \MvcCore\Request\IConstants as ReqConsts;

class Index extends Base {

	public function IndexInit (): void {
		if ($this->request->HasParam('cron_job_org', ReqConsts::PARAM_TYPE_QUERY_STRING)) {
			$this->response
				->AddHeader('Content-Type', 'text/plain')
				->SetBody('ok');
			$this->Terminate();
		}
	}

    public function IndexAction (): void {
		$name = 'Tom Flídr';
		$desc = 'Freelance developer, trainer & consultant';
		$this->view->title = "{$name} | {$desc}";
		$this->view->heading = $name;
		$this->view->description = $desc;
		$this->assets->Index();
    }
	
	public function StatusAction (): void {
		if ($this->environment->IsProduction())
			throw new \Exception("No route for request", 404);
		$this->layout = NULL;
		$this->view->title = 'Status | ' . $this->request->GetHostName();
		$this->view->phpVersion = phpversion();
		$git = new \App\Tools\Git($this->request->GetAppRoot());
		$this->view->gitSummary = $git->GetHeadCommitSummary();
	}

	public function NotFoundAction (): void {
		$this->ErrorAction();
	}

	public function ErrorAction (): void {
		$code = $this->response->GetCode();
		if ($code === 200) $code = 500;
		$this->view->title = "Error {$code}";
		$this->view->message = $this->request->GetParam('message', FALSE);
		$this->Render('error');
	}
}
