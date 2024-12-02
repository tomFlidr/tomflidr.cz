<?php

namespace App\Controllers\Fronts\Indexes;

use \App\Models\Navigations\BreadCrumbs\Item as BreadCrumbItem;
use \App\Models\Training;

class References extends \App\Controllers\Fronts\Index {

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();

		$maxPixels = 80 * 60;
		$logosFullPath = implode('/', [
			$this->application->GetPathStatic(TRUE),
			'img/content/logotypes',
		]);
		$svgs = \App\Models\Xml\Entities\Svg::GetByDirPath($logosFullPath);
		$logotypes = new \stdClass;
		foreach ($svgs as $svg) {
			$name = $svg->GetName();
			[$w, $h] = $this->resizeByPixelsCount($maxPixels, $svg->GetWidth(), $svg->GetHeight());
			$logotypes->{$name} = (object) [
				'code'		=> $svg->GetHtmlCode(),
				'width'		=> $w,
				'height'	=> $h,
				'styleAttr'	=> " style=\"width:{$w}px;height:{$h}px;\""
			];
		}
		$this->view->logotypes = $logotypes;

		$this->view->training = new Training;

		$this->assets->References();
		$this->view->logoHeight = 100;
		$this->renderAction();
	}

	/** @return array<int> */
	protected function resizeByPixelsCount (int $pixelsCount, float $w, float $h): array {
		$targetSqrt = sqrt($pixelsCount);
		$sourceSqrt = sqrt($w * $h);
		$sqrtRatio = $targetSqrt / $sourceSqrt;
		$newWidth = intval(round($w * $sqrtRatio));
		$newHeight = intval(round($h * $sqrtRatio));
		return [max(1, $newWidth), max(1, $newHeight)];
	}
	
}
