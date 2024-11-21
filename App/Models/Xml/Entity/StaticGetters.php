<?php

namespace App\Models\Xml\Entity;

use \App\Models\Xml\Entity;

/**
 * @mixin \App\Models\Xml\Entity
 */
trait StaticGetters {

	/**
	 * Get model instance by single xml file relative path from defined data path
	 * without .xml file extension.
	 */
	public static function GetByFilePath (string $dataDirRelPathWithoutExt): ?Entity {
		$cacheKey = implode('_', [
			'xmlEntity_byFilePath', md5($dataDirRelPathWithoutExt)
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($dataDirRelPathWithoutExt) {
				/** @var App\Models\Xml\Entity $this */
				$entity = static::loadByFilePath($dataDirRelPathWithoutExt);
				$cache->Save($cacheKey, $entity, NULL, static::CACHE_TAGS);
				return $entity;
			}
		);
	}

	/**
	 * Load model instance by single xml file relative path from defined data path
	 * without .xml file extension.
	 */
	protected static function loadByFilePath (string $dataDirRelPathWithoutExt): ?Entity {
		$dataDirFullPath = static::GetDataDirFullPath();
		$dataDirRelPathWithoutExt = static::sanitizePath($dataDirRelPathWithoutExt);
		$fileFullPath = $dataDirFullPath . $dataDirRelPathWithoutExt . '.xml';
		if (file_exists($fileFullPath))
			return static::xmlLoadAndSetupModel($fileFullPath, $dataDirRelPathWithoutExt);
		return NULL;
	}

	/**
	 * Get array with all model instances by given directory relative path from 
	 * defined data path without subdirectories.
	 * @return  array<Entity>
	 */
	public static function GetByDirPath (string $relPath, bool $includingParentLevelDoc = FALSE, array $sort = []): array {
		$cacheKey = implode('_', [
			'xmlEntity_byDirPath', 
			md5(serialize([
				$relPath,
				$includingParentLevelDoc,
				$sort
			]))
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($relPath, $includingParentLevelDoc, $sort) {
				/** @var App\Models\Xml\Entity $this */
				$entities = static::loadByDirPath($relPath, $includingParentLevelDoc, $sort);
				$cache->Save($cacheKey, $entities, NULL, static::CACHE_TAGS);
				return $entities;
			}
		);
	}

	/**
	 * Get array with all model instances by given directory relative path from 
	 * defined data path without subdirectories.
	 * @return  array<Entity>
	 */
	public static function loadByDirPath (string $relPath, bool $includingParentLevelDoc = FALSE, array $sort = []): array {
		$result = [];
		$dataDirFullPath = self::GetDataDirFullPath();
		$relPath = static::sanitizePath($relPath);
		$fullPath = $dataDirFullPath . $relPath;
		// get parent level document with the same name as target directory if necessary
		$parentLevelDoc = NULL;
		if ($includingParentLevelDoc) {
			$parentLevelDocFullPath = $fullPath . '.xml';
			if (is_file($parentLevelDocFullPath))
				$parentLevelDoc = static::xmlLoadAndSetupModel(
					$parentLevelDocFullPath, $relPath
				);
		}
		// complete items from target directory
		if (!is_dir($fullPath)) {
			if ($parentLevelDoc !== NULL)
				$result[] = $parentLevelDoc;
			return $result;
		}
		$di = new \DirectoryIterator($fullPath);
		$resultItems = [];
		foreach ($di as $item) {
			if ($item->isDir()) continue;
			if (strtolower($item->getExtension()) != 'xml') continue;
			$fileName = $item->getFilename();
			$fileNameWithoutExt = mb_substr($fileName, 0, mb_strlen($fileName) - 4);
			$resultItems[] = static::xmlLoadAndSetupModel(
				$fullPath . '/' . $fileName, 
				$relPath . '/' . $fileNameWithoutExt
			);
		}
		// sorting by sort property:
		if (count($sort) > 0) {
			usort($resultItems, function ($a, $b) use ($sort) {
				return static::compareByProperies($a, $b, $sort, 0);
			});
		}
		// completing result
		$result = array_merge($parentLevelDoc ? [$parentLevelDoc] : [], $resultItems);
		return $result;
	}

	protected static function sanitizePath (string $path): string {
		$path = str_replace(['\\', '//'], '/', $path);
		return str_replace(['../', './'], '', $path);
	}
	
	/**
	 * Compare asscendently booleans, integers, floats and strings.
	 * `TRUE` is before `FALSE`, `NULL` is after anything.
	 * @param Entity $a 
	 * @param Entity $b 
	 * @param string $sortProperty 
	 * @return int
	 */
	protected static function compareByProperies (Entity $a, Entity $b, $sort, $sortIndex) {
		$sortProperties = array_keys($sort);
		$sortProperty = $sortProperties[$sortIndex];
		$sortDir = $sort[$sortProperty];
		if (strtolower($sortDir) == 'asc') {
			$plus = 1;
			$minus = -1;
		} else {
			$plus = -1;
			$minus = 1;
		}
		$aVal = $a->{$sortProperty};
		$bVal = $b->{$sortProperty};
		$aPropType = gettype($aVal);
		$bPropType = gettype($bVal);
		$aPropType = $aPropType === 'NULL' || $aPropType === 'unknown type' ? '' : $aPropType;
		$bPropType = $bPropType === 'NULL' || $bPropType === 'unknown type' ? '' : $bPropType;
		if (!$aPropType && $bPropType) {
			return $plus;
		} else if ($aPropType && !$bPropType) {
			return $minus;
		} else if ($aPropType !== $bPropType) {
			return 0;
		}
		$propType = $aPropType;
		$r = 0;
		if ($propType === 'string') {
			$r = strcasecmp($aVal, $bVal);
		} else if ($propType == 'boolean') {
			$r = ($aVal === $bVal ? 0 : ($aVal ? -1 : 1));
		} else if ($propType == 'integer') {
			$r = $aVal > $bVal ? 1 : ($aVal < $bVal ? -1 : 0);
		} else if ($propType == 'double') {
			$aValStr = (string) $aVal;
			$bValStr = (string) $bVal;
			$aDotPos = mb_strrpos($aValStr, '.');
			$bDotPos = mb_strrpos($bValStr, '.');
			$aScale = $aDotPos !== FALSE ? mb_strlen($aValStr) - $aDotPos - 1 : 0;
			$bScale = $bDotPos !== FALSE ? mb_strlen($bValStr) - $bDotPos - 1 : 0;
			$scale = max($aScale, $bScale);
			$r = bccomp($aVal, $bVal, $scale);
		}
		if ($r === 0) {
			if ($sortIndex + 1 < count($sort)) 
				return static::compareByProperies($a, $b, $sort, $sortIndex + 1);
		}
		return $r === 1 ? $plus : ($r === -1 ? $minus : 0);
	}
}