<?php

namespace App\Models\Xml;

/**
 * @method static array<Document> GetByDirPath(string $relPath, bool $includingParentLevelDoc = FALSE, array $sort = [])
 */
class Document extends \App\Models\Xml\Entity {
	
	public const ROUTE_NAME = 'document';
	public const META_ROBOTS_DEFAULT = 'index,follow,archive';

	protected static string	$dataDir = '~/Var/Documents';
	
	use \App\Models\Xml\Document\Props,
		\App\Models\Xml\Document\GettersSetters,
		\App\Models\Xml\Document\SetUp;
	
	public static function GetBestMatchByFilePath (string $lang, string $rawRequestedPath) {
		$cacheKey = implode('_', [
			'document_path_match', $lang, md5($rawRequestedPath)
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($lang, $rawRequestedPath) {
				$document = static::loadBestMatchByFilePath($lang, $rawRequestedPath);
				$cache->Save($cacheKey, $document, NULL, static::CACHE_TAGS);
				return $document;
			}
		);
	}

	protected static function loadBestMatchByFilePath (string $lang, string $rawRequestedPath) {
		$result = NULL;
		$rawRequestedPath = $lang . (mb_strlen($rawRequestedPath) > 0 
			? '/' . trim($rawRequestedPath, '/') : 
			'');
		$dataDirRelPathWithoutExt = rtrim(static::sanitizePath($rawRequestedPath), '/');
		$dataDirFullPath = static::GetDataDirFullPath();
		while (TRUE) {
			$fileFullPath = str_replace('\\', '/', $dataDirFullPath . '/' . $dataDirRelPathWithoutExt . '.xml');
			if (file_exists($fileFullPath)) {
				$result = static::xmlLoadAndSetupModel($fileFullPath, $dataDirRelPathWithoutExt);
				if ($result->Active) {
					break;
				} else {
					$result = NULL;
				}
			}
			if ($result === NULL) {
				$lastSlashPos = mb_strrpos($dataDirRelPathWithoutExt, '/');
				if ($lastSlashPos === FALSE) break;
				$dataDirRelPathWithoutExt = mb_substr($dataDirRelPathWithoutExt, 0, $lastSlashPos);
				$lastSlashPos = mb_strrpos($dataDirRelPathWithoutExt, '/');
				if ($lastSlashPos === FALSE) break;
			}
		}
		return $result;
	}

	public static function RouteFilterIn (& $urlParams, & $defaultParams, & $request) {
		$lang = $request->GetLang();
		$document = static::GetBestMatchByFilePath($lang, $urlParams['path'] ?? '');
		$defaultParams['doc'] = $document;
		return $urlParams;									  
	}

	public static function RouteFilterOut (& $urlParams, & $defaultParams, & $request) {
		$locParamName = \MvcCore\Ext\Routers\ILocalization::URL_PARAM_LOCALIZATION;
		if ($urlParams[$locParamName] !== $defaultParams[$locParamName]) {
			$targetLang = current(explode('-', $urlParams[$locParamName]));
			if (isset($defaultParams['doc'])) {
				$document = $defaultParams['doc'];
			} else {
				$currentLang = current(explode('-', $defaultParams[$locParamName]));
				$document = static::GetBestMatchByFilePath($currentLang, $urlParams['path'] ?? '');
			}
			$oppositeDoc = $document->GetLanguageOpposite($targetLang);
			$urlParams['path'] = $oppositeDoc->GetPath();
		}
		return $urlParams;		
	}

	public function GetUrl (array $urlParams = []) {
		return self::GetRouter()->Url(
			self::ROUTE_NAME, array_merge(['path' => $this->path], $urlParams)
		);
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
