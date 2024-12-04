<?php

namespace App\Models;

class SitemapUrl extends \App\Models\Base {
	
	protected string	$loc;
	protected \DateTime	$lastMod;
	protected float		$priority;
	protected string	$changeFreq;

	public function __construct (
		string $loc,
		int|\DateTime $lastMod,
		float $priority,
		string $changeFreq
	) {
		$this
			->SetLoc($loc)
			->SetLastMod($lastMod)
			->SetPriority($priority)
			->SetChangeFreq($changeFreq);
	}

	public function GetLoc (): string {
		return $this->loc;
	}
	public function SetLoc (string $loc): static {
		$this->loc = $loc;
		return $this;
	}

	public function GetLastMod (): string {
		return $this->lastMod->format('Y-m-d\TH:i:sP');
	}
	public function SetLastMod (int|\DateTime $lastMod): static {
		if (is_int($lastMod)) {
			$dateTime = new \DateTime();
			$dateTime->setTimestamp($lastMod);
			$this->lastMod = $dateTime;
		} else {
			$this->lastMod = $lastMod;
		}
		return $this;
	}

	public function GetPriority (): string {
		return number_format($this->priority, 1, '.', '');
	}
	public function SetPriority (float $priority): static {
		$this->priority = $priority;
		return $this;
	}

	public function GetChangeFreq (): string {
		return $this->changeFreq;
	}
	public function SetChangeFreq (string $changeFreq): static {
		$this->changeFreq = $changeFreq;
		return $this;
	}

}