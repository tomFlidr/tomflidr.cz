<?php

namespace App\Models\Xml\Entities\Document;

/**
 * @mixin \App\Models\Xml\Entities\Document
 */
trait RouterFiltering {

	public static function RouteFilterIn (& $urlParams, & $defaultParams, & $request) {
		$lang = $request->GetLang();
		$document = static::GetBestMatchByFilePath($lang, $urlParams['path'] ?? '');
		$defaultParams['doc'] = $document;
		return $urlParams;									  
	}

	public static function RouteFilterOut (& $urlParams, & $defaultParams, & $request) {
		$locParamName = \MvcCore\Ext\Routers\ILocalization::URL_PARAM_LOCALIZATION;
		if (
			isset($defaultParams[$locParamName]) && // request without route not contain any localization pram
			$urlParams[$locParamName] !== $defaultParams[$locParamName]
		) {
			$targetLang = current(explode('-', $urlParams[$locParamName]));
			if (isset($defaultParams['doc'])) {
				$document = $defaultParams['doc'];
			} else {
				$currentLang = current(explode('-', $defaultParams[$locParamName]));
				$document = static::GetBestMatchByFilePath($currentLang, $urlParams['path'] ?? '');
				if ($document === NULL)
					$document = static::GetBestMatchByFilePath($currentLang, ''); // homepage
			}
			$oppositeDoc = $document->GetLanguageOpposite($targetLang);
			$urlParams['path'] = $oppositeDoc->GetPath();
		}
		return $urlParams;		
	}

	public function & GetLanguageOpposite ($oppositeLang) {
		$result = parent::GetByFilePath('/' . $oppositeLang);
		// explode original path
		$origPathElms = explode('/', trim($this->GetOriginalPath(), '/'));
		// load documents trees and their sequence numbers
		$sequences = [];
		$currentPath = $origPathElms[0];
		$origPathElmsCntLessOne = count($origPathElms) - 1;
		$dataDirFullPath = static::GetDataDirFullPath();
		foreach ($origPathElms as $key => $origPathElm) {
			if ($key === 0) continue;
			if ($key === $origPathElmsCntLessOne) {
				$sequences[] = $this->GetSequence();
				break;
			}
			$currentPath .= '/' . $origPathElm;
			$fileFullPath = str_replace('\\', '/', $dataDirFullPath . '/' . $currentPath . '.xml');
			if (file_exists($fileFullPath)) {
				$subDoc = static::xmlLoadAndSetupModel($fileFullPath, $currentPath);
				$sequences[] = $subDoc->GetSequence();
			} else {
				return $result;
			}
		}
		// load opposite documents by sequence tree
		$baseOppositePath = '/' . $oppositeLang;
		$oppositePath = '';
		foreach ($sequences as $sequence) {
			$subDocs = parent::GetByDirPath($baseOppositePath . $oppositePath);
			$continue = FALSE;
			foreach ($subDocs as $subDoc) {
				if ($sequence === $subDoc->GetSequence()) {
					$result = & $subDoc;
					$oppositePath = $subDoc->GetPath();
					$continue = TRUE;
					break;
				}
			}
			if (!$continue) break;
		}
		return $result;
	}
	
}