<?php

namespace App\Models;

class Contact extends \App\Models\Base {
	
	public const CACHE_TAGS = ['contacts'];

	protected static array $contacts = [
		'pgpFileName'	=> 'info(at)tomflidr.cz.public',
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
		'chats'			=> [
			'whatsapp'		=> 'https://wa.me/00420724479802',
			'skype'			=> 'skype:tomflidr?chat',
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
		],
		'fullMap'		=> [
			'en'			=> 'https://www.google.com/maps/place/Tom+Fl%C3%ADdr/@49.6831259,13.0066673,7.52z/data=!4m15!1m8!3m7!1s0x471297e05abf9bb5:0xc87ae5907ea6e228!2sTom+Fl%C3%ADdr!8m2!3d49.0758643!4d16.493!10e5!16s%2Fg%2F11fj86363s!3m5!1s0x471297e05abf9bb5:0xc87ae5907ea6e228!8m2!3d49.0758643!4d16.493!16s%2Fg%2F11fj86363s?entry=ttu&g_ep=EgoyMDI0MTEyNC4xIKXMDSoASAFQAw%3D%3D',
			'de'			=> 'https://www.google.com/maps/place/Tom+Fl%C3%ADdr/@49.6831259,13.0066673,7z/data=!4m15!1m8!3m7!1s0x471297e05abf9bb5:0xc87ae5907ea6e228!2sTom+Fl%C3%ADdr!8m2!3d49.0758643!4d16.493!10e5!16s%2Fg%2F11fj86363s!3m5!1s0x471297e05abf9bb5:0xc87ae5907ea6e228!8m2!3d49.0758643!4d16.493!16s%2Fg%2F11fj86363s?hl=de&entry=ttu&g_ep=EgoyMDI0MTEyNC4xIKXMDSoASAFQAw%3D%3D',
			'cs'			=> 'https://mapy.cz/zakladni?q=Tom%20Fl%C3%ADdr%2C%20M%C4%9Bl%C4%8Dany%2065%2C%2066464&source=firm&id=13602951&ds=1&x=15.4218285&y=49.6696015&z=8',
		],
	];
	
	public static function GetData (): \stdClass {
		$cacheKey = implode('_', [
			'contacts',
			self::GetRouter()->GetLocalization(TRUE)
		]);
		return self::GetCache()->Load($cacheKey, function (\MvcCore\Ext\ICache $cache, string $cacheKey) {
			$contacts = self::prepareContacts();
			$cache->Save($cacheKey, $contacts, NULL, self::CACHE_TAGS);
			return $contacts;
		});
	}

	protected static function prepareContacts (): \stdClass {
		$contacts = self::array2object(self::$contacts);
		$contacts->email->link = str_replace([
			'<subject>',
		], [
			rawurlencode(self::Translate('contact email subject')),
		], $contacts->email->link);
		$router = self::GetRouter();
		$contacts->pgpKey->view = call_user_func_array([$router, 'Url'], $contacts->pgpKey->view);
		$contacts->pgpKey->download = call_user_func_array([$router, 'Url'], $contacts->pgpKey->download);
		[$lang] = self::GetRouter()->GetLocalization(FALSE);
		$contacts->fullMap = $contacts->fullMap->{$lang};
		return $contacts;
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

}