<?php

namespace App\Controllers\Fronts\Indexes;

use \MvcCore\Request\IConstants as ReqConsts;

class Contacts extends \App\Controllers\Fronts\Index {

	protected static string $pgpFileName = 'info(at)tomflidr.cz.public';

	protected static array $contacts = [
		'phone'			=> [
			'link'			=> 'tel:+420724479802',
			'text'			=> '+420 724 479 802',
		],
		'email'			=> [
			'link'			=> 'mailto:info@tomflidr.cz?subject=<subject>',
			'text'			=> 'info@tomflidr.cz',
		],
		'pgpKey'		=> [
			'fingerprint'	=> 'E909 20F0 CD62 5873 3E38 58FD 7470 2EA6 A301 417E',
			'view'			=> [':PgpKey'],
			'download'		=> [':PgpKey', ['download' => 1]],
		],
		'goverment'		=> [
			'business'		=> [
				'link'			=> 'https://www.mojedatovaschranka.cz/sds/detail?dbid=ujzk4jf',
				'text'			=> 'ujzk4jf',
			],
			'personal'		=> [
				'link'			=> 'https://www.mojedatovaschranka.cz/sds/detail?dbid=85xcgtj',
				'text'			=> '85xcgtj',
			],
		],
		'social'		=> [
			'github'		=> [
				'text'			=> 'GitHub',
				'link'			=> 'https://github.com/tomflidr/',
			],
			'linkedin'		=> [
				'text'			=> 'LinkedIn',
				'link'			=> 'https://www.linkedin.com/in/tomflidr/',
			],
			'stackoverflow'	=> [
				'text'			=> 'Stack Overflow',
				'link'			=> 'https://stackoverflow.com/users/7032987/tom-fl%c3%addr',
			],
		],
		'invoicing'		=> [
			'id'			=> [
				'link'			=> 'https://rejstrik.penize.cz/ares/74813871-tomas-flidr',
				'text'			=> '74813871',
			],
			'bank'		=> [
				'number'	=> '1961120014',
				'code'		=> '3030',
				'iban'		=> 'CZ10 3030 0000 0019 6112 0014',
				'bic'		=> 'AIRACZPP',
			]
		]
	];

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$contacts = self::array2object(self::$contacts);
		$contacts->email->link = str_replace([
			'<subject>',
		], [
			rawurlencode($this->translate('contact email subject')),
		], $contacts->email->link);
		$contacts->pgpKey->view = call_user_func_array([$this, 'Url'], $contacts->pgpKey->view);
		$contacts->pgpKey->download = call_user_func_array([$this, 'Url'], $contacts->pgpKey->download);
		$this->view->contacts = $contacts;
		$this->assets->Contacts();
		$this->renderAction('contacts');
	}

	protected static function array2object (array $array): \stdClass {
		$obj = new \stdClass;
		foreach ($array as $k => $v) {
			if (is_array($v) && !self::arrayIsList($v)) {
				$obj->{$k} = self::array2object($v);
			} else {
				$obj->{$k} = $v;
			}
		}
		return $obj;
	}

	protected static function arrayIsList (array $array): bool {
		$i = 0;
		foreach ($array as $k => $v) {
			if ($k !== $i++)
				return FALSE;
		}
		return TRUE;
	}

	public function PgpKeyAction (): void {
		$download = $this->request->HasParam('download');
		$pgpFullPath = $this->application->GetPathVar(TRUE) . '/' . self::$pgpFileName;
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
				'Content-Disposition'		=> 'filename="' . self::$pgpFileName . '"',
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

}
