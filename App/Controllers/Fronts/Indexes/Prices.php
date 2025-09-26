<?php

namespace App\Controllers\Fronts\Indexes;

class Prices extends \App\Controllers\Fronts\Index {

	public function IndexAction (): void {
		$this->setUpTitleAndBreadCrumbsText();
		$price = new \App\Models\Price;
		$langCode = $this->request->GetLang();
		$this->view->priceRates = $price->GetRates($langCode)->GetData();
		$this->assets->Prices();
		$this->renderAction();
	}

}
