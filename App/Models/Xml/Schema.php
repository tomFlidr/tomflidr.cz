<?php

namespace App\Models\Xml;

use \App\Models\Xml\Entity\Member;

class Schema extends \App\Models\Xml\Base {

	public const ANY_TYPE = 'code';
	
	protected string	$name;
	protected string	$path;
	protected string	$fullPath;
	/** @var array<string,Member> */
	protected array		$members	= [];

	public function GetName (): string {
		return $this->name;
	}
	public function SetName (string $name): static {
		$this->name = $name;
		return $this;
	}

	public function GetPath (): string {
		return $this->path;
	}
	public function SetPath (string $path): static {
		$this->path = $path;
		return $this;
	}
	
	public function GetFullPath (): string {
		return $this->fullPath;
	}
	public function SetFullPath (string $fullPath): static {
		$this->fullPath = $fullPath;
		return $this;
	}
	
	/** @return array<string,Member> */
	public function GetMembers (): array {
		return $this->members;
	}
	/** @param array<string,Member> $members */
	public function SetMembers (array $members): static {
		$this->members = $members;
		return $this;
	}

	public function __construct (
		string $name,
		string $path,
		string $fullPath,
		array $members = []
	) {
		$this->name = $name;
		$this->path = $path;
		$this->fullPath = $fullPath;
		$this->members = $members;
	}

	/** @param array<\App\Models\Xml\Schema> $schemas */
	public static function ValidateXml (\DOMDocument $xml, array $schemas): bool {
		$result = TRUE;
		libxml_use_internal_errors(false);
		if (PHP_VERSION_ID < 80000) libxml_disable_entity_loader(); // prevent XXE atacks
		libxml_set_external_entity_loader(function ($public, $system, $context) {
			if (mb_strpos($system, static::GetDataDirFullPath()) === 0)
				return fopen($system, "r+");
			return NULL;
		});
		foreach ($schemas as $schema) {
			if (!$xml->schemaValidate($schema->GetFullPath())) {
				$result = FALSE;
				$errors = libxml_get_errors();
				static::xmlThrownLibErrors($errors);
				libxml_clear_errors();
			}
		}
		return $result;
	}

	/** @return array<\App\Models\Xml\Schema> */
	public static function GetByFullPathAndContent (string $fileFullPath, string $content): array {
		$xmlRootNode = static::getXmlContentRootNode($content);
		$cacheKey = implode('_', [
			'xml_schemas', 
			md5(serialize([
				$fileFullPath,
				$xmlRootNode
			]))
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\MvcCore\Ext\ICache $cache, string $cacheKey) use ($fileFullPath, $xmlRootNode) {
				$schemas = static::xmlLoadSchemas($fileFullPath, $xmlRootNode);
				$cache->Save($cacheKey, $schemas, NULL, static::CACHE_TAGS);
				return $schemas;
			}
		);
	}
	
	/** @return array<\App\Models\Xml\Schema> */
	protected static function xmlLoadSchemas (string $fileFullPath, ?string $xmlRootNode): array {
		$result = [];

		if ($xmlRootNode === NULL)
			return $result;

		preg_match_all("# xsi\:schemaLocation\=\"([^\"]*)\"#", $xmlRootNode, $schemaLocationsMatches);
		preg_match_all("# xmlns\:([a-z0-9]*)=\"([^\"]*)\"#", $xmlRootNode, $schemaNameSpacesMatches);

		if (
			count($schemaLocationsMatches) === 2 && count($schemaLocationsMatches[1]) > 0 &&
			count($schemaNameSpacesMatches) === 3 && count($schemaLocationsMatches[1]) > 0
		) {
			$nameSpaceUrlsAndRelPaths = [];
			foreach ($schemaLocationsMatches[1] as $nsAndPath) {
				list($nameSpaceUrl, $xsdRelPath) = explode(' ', $nsAndPath);
				$nameSpaceUrlsAndRelPaths[$nameSpaceUrl] = $xsdRelPath;
			}

			$schemaNameSpacesAndRelPaths = [];
			foreach ($schemaNameSpacesMatches[1] as $index => $nameSpaceName) {
				$nameSpaceUrl = $schemaNameSpacesMatches[2][$index];
				if (isset($nameSpaceUrlsAndRelPaths[$nameSpaceUrl])) {
					$schemaNameSpacesAndRelPaths[$nameSpaceName] = $nameSpaceUrlsAndRelPaths[$nameSpaceUrl];
				}
			}

			$lastSlashPos = mb_strrpos($fileFullPath, '/');
			if ($lastSlashPos === FALSE) 
				$lastSlashPos = mb_strlen($lastSlashPos);
			$xmlFileDirPath = rtrim(str_replace('\\', '/', mb_substr($fileFullPath, 0, $lastSlashPos)), '/');

			foreach ($schemaNameSpacesAndRelPaths as $schemaNameSpace => $schemaRelPath) {
				$schemaFullPath = $xmlFileDirPath . '/' . $schemaRelPath;
				$schemaFullPath = \MvcCore\Tool::RealPathVirtual($schemaFullPath);
				$result[] = static::getSchema($schemaNameSpace, $schemaFullPath);
			}

		}
		return $result;
	}

	protected static function getXmlContentRootNode (string $content): ?string {
		$index = 0;
		$invalid = FALSE;
		// skip first xml comments `<!-- -->` and possible tags like `< ?xml-stylesheet ... ? >`:
		while (TRUE) {
			$nextLtChar = mb_strpos($content, '<', $index);
			if ($nextLtChar === FALSE) {
				$invalid = TRUE;
				break;
			} else {
				$index = $nextLtChar;
			}
			$first2Chars = mb_substr($content, $index, 2);
			$first4Chars = mb_substr($content, $index, 4);
			if ($first2Chars === '<?') {
				list($tagStartLen, $tagEnd) = [2, '?>'];
			} else if ($first4Chars === '<!--') {
				list($tagStartLen, $tagEnd) = [4, '-->'];
			} else {
				break;
			}
			$tagEndPos = mb_strpos($content, $tagEnd, $index + $tagStartLen);
			if ($tagEndPos === FALSE) {
				$invalid = TRUE;
				break;
			} else {
				$index = $tagEndPos + mb_strlen($tagEnd);
			}
		}
		if ($invalid)
			return NULL;
		// parse root node
		$firstNodeEndPos = mb_strpos($content, '>', $index);
		$firstNodeEndPos = $firstNodeEndPos !== FALSE ? $firstNodeEndPos + 1 : mb_strlen($content);
		$rootNode = trim(mb_substr($content, $index, $firstNodeEndPos - $index));
		return $rootNode;
	}

	/** @throws \RuntimeException */
	protected static function getSchema (string $schemaNameSpace, string $schemaFullPath): \App\Models\Xml\Schema {
		$cacheKey = implode('_', [
			'xml_schema', $schemaNameSpace
		]);
		return self::GetCache()->Load(
			$cacheKey, 
			function (\MvcCore\Ext\ICache $cache, string $cacheKey) use ($schemaNameSpace, $schemaFullPath) {
				$schema = static::loadAndParseSchema($schemaNameSpace, $schemaFullPath);
				$cache->Save($cacheKey, $schema, NULL, static::CACHE_TAGS);
				return $schema;
			}
		);
	}
	
	/** @throws \RuntimeException */
	protected static function & loadAndParseSchema (string $schemaNameSpace, string $schemaFullPath): \App\Models\Xml\Schema {
		$schemaXml = static::loadSchemaAsSimpleXml($schemaFullPath);
		if (!($schemaXml instanceof \SimpleXMLElement)) {
			throw new \RuntimeException(implode("\n", [
				"[".get_called_class()."] No XML schema found in path: `{$schemaFullPath}`. ",
				"Define namespace and scheme file in root node correctly: ",
				"`<schemeName:rootNodeName xsi:schemaLocation=\"/schemeName ./Path/To/SchemaName.xsd\" xmlns:schemeName=\"/schemeName\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">`"
			]));
		}
		$rootNodeAttrs = $schemaXml->attributes();
		$schemaPath = (string) $rootNodeAttrs['targetNamespace'];
		$schema = new \App\Models\Xml\Schema(
			$schemaNameSpace, $schemaPath, $schemaFullPath
		);
		$rootNodeDescriptorBase = $schemaXml->children('xsd', TRUE);
		$rootNodeDescriptorBase->registerXPathNamespace('xsd', 'http://www.w3.org/2001/XMLSchema');
		$rootNodeDescriptorBaseAttrs = $rootNodeDescriptorBase->attributes();
		$rootNodeDescriptorBaseName = (string) $rootNodeDescriptorBaseAttrs['name'];
		$schemaMembers = [];
		foreach ($rootNodeDescriptorBase->xpath('//xsd:element') as $dataNode) {
			$attrs = $dataNode->attributes();
			$nodeName = trim((string) $attrs['name']);
			if ($nodeName === $rootNodeDescriptorBaseName)
				continue; // do not process root node
			if (!isset($attrs['type'])) {
				$dataNode->xpath('//xs:element');
				$dataType = static::ANY_TYPE;
			} else {
				$dataType = substr(trim((string)$attrs['type']), 4);
			}
			$propName = lcfirst(\MvcCore\Tool::GetPascalCaseFromDashed($nodeName));
			$schemaMembers[$nodeName] = new Member(
				$nodeName, $dataType, $propName
			);
		}
		$schema->SetMembers($schemaMembers);
		return $schema;
	}

	/** @throws \RuntimeException  */
	protected static function loadSchemaAsSimpleXml (string $fullPathOrContent, bool $fromString = FALSE): ?\SimpleXMLElement {
		if (PHP_VERSION_ID < 80000) libxml_disable_entity_loader(); // prevent XXE atacks
		libxml_use_internal_errors(true);
		libxml_clear_errors();
		if ($fromString) {
			$content = $fullPathOrContent;
		} else {
			$content = file_get_contents($fullPathOrContent);
			if ($content === FALSE) return NULL;
		}
		$xml = simplexml_load_string($content);
		$errors = libxml_get_errors();
		libxml_clear_errors();
		static::xmlThrownLibErrors($errors);
		if ($xml === FALSE) 
			return NULL;
		return $xml;
	}
	
}
