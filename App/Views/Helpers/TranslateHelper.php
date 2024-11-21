<?php

namespace App\Views\Helpers {

	/**
	 * @method static \App\Views\Helpers\TranslateHelper GetInstance ()
	 */
	class TranslateHelper extends \MvcCore\Ext\Views\Helpers\AbstractHelper {
		
		protected static $instance = NULL;
		
		protected ?\MvcCore\Ext\ITranslator $translator = NULL;

		public function SetTranslator (\MvcCore\Ext\ITranslator $translator): static {
			$this->translator = $translator;
			return $this;
		}

		public function Translate (string $key, array $replacements = []): string {
			return $this->translator->Translate($key, $replacements);
		}
	}

}

namespace {
	function __ ($key, $replacements = []): string {
		return \App\Views\Helpers\TranslateHelper::GetInstance()->Translate($key, $replacements);
	};
}