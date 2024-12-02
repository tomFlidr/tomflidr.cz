<?php

namespace App\Models;

class Training extends \App\Models\Base {
	
	protected int	$count			= 150;
	protected int	$yearStart		= 2008;
	protected float	$avgPercentage	= 0.27; // 1.35 / 5
	
	public function GetCount (): int {
		return $this->count;
	}
	public function GetYears (): int {
		return intval(date('Y')) - $this->yearStart;
	}
	public function GetRating (int $fromTotal = 5): float {
		return floatval($this->avgPercentage * $fromTotal);
	}

}