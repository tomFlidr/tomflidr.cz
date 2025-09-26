<?php

namespace App\Models;

use \App\Models\Prices\Rates;

class Price extends \App\Models\Base {

	protected static array $langCode2CurrencyCode = [
		'en'	=> 'EUR',
		'de'	=> 'EUR',
		'cs'	=> 'CZK',
	];
	
	/** @var array<string,Rates> */
	protected array $rates = []; 
	
	public function __construct () {
		$this->rates = [
			'EUR'	=> new Rates(
				consultation	: 48.0,
				databases		: 49.0,
				development		: 38.0,
				administration	: 32.0,
				additionalWork	: 27.0,
			),
			'CZK'	=> new Rates(
				consultation	: 1140.0,
				databases		: 1180.0,
				development		:  920.0,
				administration	:  780.0,
				additionalWork	:  650.0,
			),
		];
	}

	public function GetRates (string $langCode): Rates {
		$currencyCode = static::$langCode2CurrencyCode[$langCode];
		return $this->rates[$currencyCode];
	}

}