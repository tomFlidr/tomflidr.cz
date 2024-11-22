<?php

namespace App\Controllers\Fronts;

use \MvcCore\Request\IConstants as ReqConsts;

class Index extends \App\Controllers\Front {

	public function IndexInit (): void {
		if ($this->request->HasParam('cron_job_org', ReqConsts::PARAM_TYPE_QUERY_STRING)) {
			$this->response
				->AddHeader('Content-Type', 'text/plain')
				->SetBody('ok');
			$this->Terminate();
		}
		if ($this->document === NULL && $this->actionName !== 'not-found') {
			$this->RenderNotFound($this->translate('Page not found.'));
		}
	}
	
	public function StatusAction (): void {
		if ($this->environment->IsProduction())
			throw new \Exception("No route for request", 404);
		$this->layout = NULL;
		$this->view->title = 'Status | ' . $this->request->GetHostName();
		$this->view->phpVersion = phpversion();
		$git = new \App\Tools\Git($this->application->GetPathAppRoot());
		$this->view->gitSummary = $git->GetHeadCommitSummary();
	}
	
	public function IndexAction (): void {
		$name = 'Tom Flídr';
		$desc = 'Freelance developer, trainer & consultant';
		$this->view->title = "{$name} | {$desc}";
		$this->view->heading = $name;
		$this->view->description = $desc;
		$this->assets->Index();
		$this->Render('index');
	}
	
	public function ContactsAction (): void {
		$this->setUpTitleAndBreadCrumbs();
		$this->view->contacts = (object) [
			'phone'	=> (object) [
				'link'	=> 'tel:+420274479802',
				'text'	=> '+420 274 479 802',
			],
			'email'	=> (object) [
				'link'	=> 'mailto:info@tomflidr.cz?' . http_build_query([
					'subject'	=> __('contact email subject'),
					'body'		=> __('contact email body'),
				], '', '&'),
				'text'	=> 'info@tomflidr.cz',
			],
			'pgpKey'	=> (object) [
				'fingerprint'	=> 'E909 20F0 CD62 5873 3E38 58FD 7470 2EA6 A301 417E',
				'view'			=> $this->Url(':PgpKey'),
				'download'		=> $this->Url(':PgpKey', ['download' => 1]),
			],
			'goverment'	=> (object) [
				'business'	=> (object) [
					'link'	=> 'https://www.mojedatovaschranka.cz/sds/detail?dbid=ujzk4jf',
					'text'	=> 'ujzk4jf',
				],
				'personal'	=> (object) [
					'link'	=> 'https://www.mojedatovaschranka.cz/sds/detail?dbid=85xcgtj',
					'text'	=> '85xcgtj',
				],
			],
			'social'	=> (object) [
				'github'		=> (object) [
					'text'		=> 'GitHub',
					'link'		=> 'https://github.com/tomflidr/',
				],
				'linkedin'		=> (object) [
					'text'		=> 'LinkedIn',
					'link'		=> 'https://www.linkedin.com/in/tomflidr/',
				],
				'stackoverflow'	=> (object) [
					'text'		=> 'Stack Overflow',
					'link'		=> 'https://stackoverflow.com/users/7032987/tom-fl%c3%addr',
				],
			],
			'invoicing'	=> (object) [
				'id'		=> (object) [
					'link'	=> 'https://rejstrik.penize.cz/ares/74813871-tomas-flidr',
					'text'	=> '74813871',
				],
				'bank'		=> (object) [
					'number'	=> '1961120014',
					'code'		=> '3030',
					'iban'		=> 'CZ10 3030 0000 0019 6112 0014',
					'bic'		=> 'AIRACZPP',
				]
			]
		];
		$this->assets->Contacts();
	}

	public function PgpKeyAction (): void {
		$download = $this->request->HasParam('download');
		$pgpFileName = 'info(at)tomflidr.cz.public';
		$pgpFullPath = $this->application->GetPathVar(TRUE) . '/' . $pgpFileName;
		$this->response->SetHeaders([
			'Content-Type'				=> 'text/plain',
			'Content-Length'			=> filesize($pgpFullPath),
			'Expires'					=> '0',
			'Cache-Control'				=> 'must-revalidate, post-check=0, pre-check=0'
		]);
		if ($download) {
			$this->response->SetHeaders([
				'Content-Description'		=> 'File Transfer',
				'Content-Transfer-Encoding'	=> 'binary',
				'Content-Disposition'		=> 'filename="' . $pgpFileName . '"',
			]);
			readfile($pgpFullPath);
			$this->Terminate();
		} else {
			$this->response->SetHeaders([
				'Content-Encoding'			=> 'utf-8',
			]);
			$this->TextResponse(file_get_contents($pgpFullPath));
		}
	}

	public function NotFoundAction (): void {
		$this->ErrorAction();
	}

	public function ErrorAction (): void {
		$code = $this->response->GetCode();
		if ($code === 200) $code = 500;
		$this->view->title = $this->translate("Error {0}", [$code]);
		$this->view->message = $this->request->GetParam('message', FALSE);
		$this->Render('error');
	}
}
