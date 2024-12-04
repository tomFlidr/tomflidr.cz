<?php

namespace App\Models\Xml\Entity;

use \App\Models\Xml\Entity,
	\App\Models\Xml\Schema;

/**
 * @mixin \App\Models\Xml\Entity
 */
trait SetUp {

	protected static function xmlLoadAndSetupModel (string $fileFullPath, string $path): ?Entity {
		clearstatcache(TRUE, $fileFullPath);
		$modTime = filemtime($fileFullPath);
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
		$result->xmlSetUp($modTime, $path, $content, $xml, $schemas);
		return $result;
	}
	
	/** @param array<\App\Models\Xml\Schema> $schemas */
	protected static function & xmlSanitizeHtmlTypes (string $content, array $schemas): string {
		foreach ($schemas as $schema) {
			$namespaceName = $schema->GetName();
			foreach ($schema->GetMembers() as $member) {
				if ($member->GetDataType() === 'code') {
					$nodeName = $member->GetNodeName();
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
	
	/** @param array<\App\Models\Xml\Schema> $schemas */
	protected function xmlSetUp (int $modTime, string $path, string $xmlCode, \DOMDocument $xml, array $schemas): static {
		$this->modTime = $modTime;
		$this->path = $path;
		$this->xmlCode = $xmlCode;
		$this->xml = $xml;
		$this->schemas = $schemas;
		$this->xmlSetUpMembers();
		return $this;
	}
	
	protected function xmlSetUpMembers (): static {
		foreach ($this->schemas as $schema) {
			$members = $schema->GetMembers();
			$elements = iterator_to_array($this->xml->getElementsByTagNameNS($schema->GetPath(), '*'), TRUE);
			foreach ($elements as $element) {
				$nodeName = $element->localName;
				if (!isset($members[$nodeName])) continue;
				$member = $members[$nodeName];
				$dataType = $member->GetDataType();
				$propName = $member->GetPropName();
				static::setUpXmlMemberByXsd($this, $element, $propName, $dataType);
			}
		}
		return $this;
	}
	
	protected static function setUpXmlMemberByXsd (Entity & $context, \DOMElement $element, string $propertyName, string $dataType): void {
		$rawNodeValue = $element->nodeValue;
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