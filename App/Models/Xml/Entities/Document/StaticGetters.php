<?php

namespace App\Models\Xml\Entities\Document;

/**
 * @mixin \App\Models\Xml\Entities\Document
 */
trait StaticGetters {

	public static function GetBestMatchByFilePath (string $lang, string $rawRequestedPath) {
		$cacheKey = implode('_', [
			'document_bestMatchByFilePath', md5(serialize([
				$lang, $rawRequestedPath
			]))
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($lang, $rawRequestedPath) {
				/** @var \App\Models\Xml\Entities\Document $this */
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

}