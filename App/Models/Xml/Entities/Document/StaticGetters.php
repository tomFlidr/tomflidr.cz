<?php

namespace App\Models\Xml\Entities\Document;

use \App\Models\Xml\Entities\Document;

/**
 * @mixin Document
 */
trait StaticGetters {

	/** @inheritDoc */
	public static function GetByFilePath (string $dataDirRelPathWithoutExt, bool $activeOnly = TRUE): ?Document {
		$cacheKey = implode('_', [
			'document_byFilePath', md5(serialize([$dataDirRelPathWithoutExt, $activeOnly]))
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($dataDirRelPathWithoutExt, $activeOnly) {
				/** @var ?Document $this */
				$entity = static::loadByFilePath($dataDirRelPathWithoutExt);
				if ($activeOnly && !$entity->GetActive())
					$entity = NULL;
				$cache->Save($cacheKey, $entity, NULL, static::CACHE_TAGS);
				return $entity;
			}
		);
	}

	/**
	 * @inheritDoc
	 * @return  array<Document>
	 */
	public static function GetByDirPath (string $path, bool $includingParentLevelDoc = FALSE, array $sort = [], bool $activeOnly = TRUE): array {
		$cacheKey = implode('_', [
			'document_byDirPath', 
			md5(serialize([
				$path,
				$includingParentLevelDoc,
				$sort,
				$activeOnly
			]))
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($path, $includingParentLevelDoc, $sort, $activeOnly) {
				/** @var Document $this */
				$entities = static::loadByDirPath($path, $includingParentLevelDoc, $sort);
				if ($activeOnly) {
					$entitiesActive = [];
					foreach ($entities as $entity)
						if ($entity->GetActive())
							$entitiesActive[] = $entity;
					$entities = $entitiesActive;
				}
				$cache->Save($cacheKey, $entities, NULL, static::CACHE_TAGS);
				return $entities;
			}
		);
	}

	public static function GetBestMatchByFilePath (string $lang, string $rawRequestedPath, bool $activeOnly = TRUE) {
		$cacheKey = implode('_', [
			'document_bestMatchByFilePath', md5(serialize([
				$lang, $activeOnly, $rawRequestedPath
			]))
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($lang, $rawRequestedPath, $activeOnly) {
				/** @var \App\Models\Xml\Entities\Document $this */
				$document = static::loadBestMatchByFilePath($lang, $rawRequestedPath, $activeOnly);
				$cache->Save($cacheKey, $document, NULL, static::CACHE_TAGS);
				return $document;
			}
		);
	}

	protected static function loadBestMatchByFilePath (string $lang, string $rawRequestedPath, bool $activeOnly = TRUE) {
		$result = NULL;
		$rawRequestedPath = $lang . (mb_strlen($rawRequestedPath) > 0 
			? '/' . trim($rawRequestedPath, '/') : 
			'');
		$dataDirRelPathWithoutExt = rtrim(static::sanitizePath($rawRequestedPath), '/');
		$dataDirFullPath = static::GetDataDirFullPath();
		while (TRUE) {
			$fileFullPath = str_replace('\\', '/', $dataDirFullPath . '/' . $dataDirRelPathWithoutExt . '.xml');
			if (file_exists($fileFullPath)) {
				/** @var ?Document $result */
				$result = static::xmlLoadAndSetupModel($fileFullPath, $dataDirRelPathWithoutExt);
				if (!$activeOnly || ($activeOnly && $result->GetActive())) {
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