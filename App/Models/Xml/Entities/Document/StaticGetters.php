<?php

namespace App\Models\Xml\Entities\Document;

use \MvcCore\Router\IConstants as RouterConsts;
use \MvcCore\Ext\Routers\IMedia;
use \MvcCore\Ext\Routers\ILocalization;

use \App\Models\Xml\Entities\Document;
use \App\Models\SitemapUrl;

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

	/** @return array<SitemapUrl> */
	public static function GetSitemapUrls (): array {
		return self::GetCache()->Load(
			'documents_sitemap', 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) {
				/** @var ?Document $this */
				$urls = static::completeSitemapUrls();
				$cache->Save($cacheKey, $urls, NULL, static::CACHE_TAGS);
				return $urls;
			}
		);
	}
	
	/** @return array<SitemapUrl> */
	protected static function completeSitemapUrls (): array {
		/** @var array<string, Document> $docs */
		$docs = [];
		static::loadDocsRecursive($docs, '~/');
		/** @var array<SitemapUrl> $urls */
		$urls = [];
		static::completeUrls($urls, $docs, IMedia::MEDIA_VERSION_FULL);
		return $urls;
	}

	/** @param  array<string, Document> $docs  */
	protected static function loadDocsRecursive (array & $docs, string $path): void {
		$levelDocs = Document::GetByDirPath($path, FALSE, ['sequence' => 'asc'], TRUE);
		$newPaths = [];
		foreach ($levelDocs as $levelDoc) {
			$originalPath = $levelDoc->GetOriginalPath();
			if (!isset($docs[$originalPath])) {
				$docs[$originalPath] = $levelDoc;
				$newPaths[] = '~' . $originalPath;
			}
		}
		foreach ($newPaths as $newPath)
			static::loadDocsRecursive($docs, $newPath);
	}

	/**
	 * @param array<SitemapUrl> $urls 
	 * @param array<string, Document> $docs 
	 */
	protected static function completeUrls (array & $urls, array $docs, string $mediaSiteVersion): void {
		$router = self::GetRouter();
		$localizationEquivalents = $router->GetLocalizationEquivalents();
		foreach ($docs as $doc) {
			$dateTime = new \DateTime();
			$dateTime->setTimestamp($doc->GetModTime());
			$localization = $localizationEquivalents[$doc->GetLang()];
			$urls[] = new SitemapUrl(
				$router->Url(Document::ROUTE_NO_FITERS_NAME, [
					'path'									=> $doc->GetPath(),
					ILocalization::URL_PARAM_LOCALIZATION	=> $localization,
					IMedia::URL_PARAM_MEDIA_VERSION			=> $mediaSiteVersion,
					RouterConsts::URL_PARAM_ABSOLUTE		=> TRUE,
				]),
				$doc->GetModTime(),
				$doc->GetSitemapPriority() ?? static::SITEMAP_PRIORITY_DEFAULT,
				$doc->GetSitemapChangeFreq() ?? static::SITEMAP_CHANGE_FREQ_DEFAULT
			);
		}
	}

}