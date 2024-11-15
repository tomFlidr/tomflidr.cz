<?php

namespace App\Models\Xml;

use \App\Models\Xml\Schema;

class Entity extends \App\Models\Xml\Base {
	
	protected ?\DOMDocument	$xml		= NULL;
	protected ?string		$xmlCode	= NULL;
	protected string		$path;

	public function GetXml (): \DOMDocument {
		if (!isset($this->xml)) {
			$this->xml = new \DOMDocument();
			$this->xml->loadXML($this->xmlCode ?? '');
		}
		return $this->xml;
	}
	public function SetXml (?\DOMDocument $xml): static {
		$this->xml = $xml;
		return $this;
	}

	public function GetPath (): string {
		return $this->path;
	}
	public function SetPath (string $path): static {
		$this->path = $path;
		return $this;
	}

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
			md5($relPath),
			$includingParentLevelDoc ? '1' : '0',
			serialize($sort)
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\Mvccore\Ext\ICache $cache, string $cacheKey) use ($relPath, $includingParentLevelDoc, $sort) {
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
	
	protected static function xmlLoadAndSetupModel (string $fileFullPath, string $path): ?Entity {
		$content = file_get_contents($fileFullPath);
		if ($content === FALSE) 
			return NULL;
		$schemas = Schema::GetByFullPathAndContent($fileFullPath, $content);
		$content = static::xmlSanitizeHtmlTypes($content, $schemas);
		$xml = static::xmlLoadDom($content, TRUE);
		if ($xml === NULL) 
			return NULL;
		Schema::ValidateXml($xml, $schemas);
		$result = new static();
		$result->xmlSetUp($content, $path, $xml, $schemas);
		return $result;
	}
	
	/** @param array<\App\Models\Xml\Schema> $schemas */
	protected static function & xmlSanitizeHtmlTypes (string $content, array $schemas): string {
		foreach ($schemas as $schema) {
			$namespaceName = $schema->GetName();
			foreach ($schema->GetTypes() as $nodeName => $nodeType) {
				if ($nodeType === 'code') {
					$replacePairs = [
						"<{$namespaceName}:{$nodeName}>"	=> "<{$namespaceName}:{$nodeName}><cdata><![CDATA[",
						"</{$namespaceName}:{$nodeName}>"	=> "]]></cdata></{$namespaceName}:{$nodeName}>"
					];
					$content = strtr($content, $replacePairs);
				}
			}
		}
		return $content;
	}

	/** @throws \RuntimeException  */
	protected static function & xmlLoadDom (string $fullPathOrContent, bool $fromString = FALSE) {
		if (PHP_VERSION_ID < 80000) libxml_disable_entity_loader(); // prevent XXE atacks
		libxml_use_internal_errors(true);
		libxml_clear_errors();
		$xml = new \DOMDocument();
		if ($fromString) {
			$content = $fullPathOrContent;
		} else {
			$content = file_get_contents($fullPathOrContent);
			if ($content === FALSE) 
				throw new \RuntimeException ("XML file not found in path `{$fullPathOrContent}`.");
		}
		$xml->loadXML($fullPathOrContent);
		// this contains only base XML syntax errors, not XSD errors:
		$errors = libxml_get_errors();
		libxml_clear_errors();
		if (count($errors) > 0) {
			$msgs = [];
			foreach ($errors as $e) {
				$msg = $e->message;
				$line = $e->line;
				$file = $fromString ? '[from string]' : $e->file;
				$column = $e->column;
				$msgs[] = "$msg (file: $file, line: $line, column: $column)";
			}
			x($fullPathOrContent);
			throw new \RuntimeException (implode(PHP_EOL, $msgs));
		}
		return $xml;
	}
	
	protected static function sanitizePath (string $path): string {
		$path = str_replace(['\\', '//'], '/', $path);
		return str_replace(['../', './'], '', $path);
	}
	
	/** @param array<\App\Models\Xml\Schema> $schemas */
	protected function xmlSetUp (string $content, string $path, \DOMDocument & $xml, array $schemas): static {
		$this->xmlCode = $content;
		$this->path = $path;
		$this->xml = $xml;
		foreach ($schemas as $schema) {
			$types = $schema->GetTypes();
			$elements = iterator_to_array($xml->getElementsByTagNameNS($schema->GetPath(), '*'), TRUE);
			foreach ($elements as $element) {
				$nodeName = $element->localName;
				if (!isset($types[$nodeName])) continue;
				$dataType = $types[$nodeName];
				$rawNodeValue = $element->nodeValue;
				$propertyName = lcfirst(\MvcCore\Tool::GetPascalCaseFromDashed($nodeName));
				static::setUpXmlValueByXsd($this, $rawNodeValue, $propertyName, $dataType);
			}
		}
		return $this;
	}
	
	protected static function setUpXmlValueByXsd (Entity & $context, string $rawNodeValue, string $propertyName, string $dataType) {
		if ($dataType === 'code') {
			$context->{$propertyName} = str_replace(
				['%basePath'],
				[self::GetRequest()->GetBasePath(),], 
				$rawNodeValue
			);
		} else if ($dataType === 'string') {
			if ($rawNodeValue === '') {
				$context->{$propertyName} = NULL;
			} else {
				$context->{$propertyName} = $rawNodeValue;
			}
		} else {
			if (settype($rawNodeValue, $dataType)) 
				$context->{$propertyName} = $rawNodeValue;
		}
	}
	
	public function __toString (): string {
		return $this->xml?->saveXML() ?? '';
	}
	
	public function __sleep () {
		if (!isset($this->xmlCode)) 
			$this->xmlCode = (string) $this->xml?->saveXML();
		$data = (array) $this;
		unset($data["\x00*\x00xml"]);
		return array_keys($data);
	}

}
