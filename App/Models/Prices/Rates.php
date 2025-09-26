<?php

namespace App\Models\Prices;

class Rates extends \App\Models\Base {

	public function __construct(
		protected float $consultation,
		protected float $databases,
		protected float $development,
		protected float $administration,
		protected float $additionalWork
	) {
	}
	
	/**
	 * @return array{
	 *		additionalWork:	float,
	 *		administration:	float,
	 *		consultation:	float,
	 *		databases:		float,
	 *		development:	float
	 * }
	 */
	public function GetData () {
		return [
			'consultation'		=> $this->consultation,
			'databases'			=> $this->databases,
			'development'		=> $this->development,
			'administration'	=> $this->administration,
			'additionalWork'	=> $this->additionalWork,
		];
	}

	public function GetConsultation (): float {
		return $this->consultation;
	}
	public function SetConsultation (float $consultation): static {
		$this->consultation = $consultation;
		return $this;
	}

	public function GetDatabases (): float {
		return $this->databases;
	}
	public function SetDatabases (float $databases): static {
		$this->databases = $databases;
		return $this;
	}

	public function GetDevelopment (): float {
		return $this->development;
	}
	public function SetDevelopment (float $development): static {
		$this->development = $development;
		return $this;
	}

	public function GetAdministration (): float {
		return $this->administration;
	}
	public function SetAdministration (float $administration): static {
		$this->administration = $administration;
		return $this;
	}

	public function GetAdditionalWork (): float {
		return $this->additionalWork;
	}
	public function SetAdditionalWork (float $additionalWork): static {
		$this->additionalWork = $additionalWork;
		return $this;
	}

}