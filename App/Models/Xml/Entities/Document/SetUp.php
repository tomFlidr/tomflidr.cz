<?php

namespace App\Models\Xml\Entities\Document;

use \App\Models\Xml\Entity;
use \App\Models\Xml\Entities\Document;

/**
 * @mixin \App\Models\Xml\Entities\Document
 */
trait SetUp {
	
	protected function xmlSetUpMembers (): static {
		/** @var \App\Models\Xml\Entities\Document $this */
		parent::xmlSetUpMembers();
		
		// default values for navigation title and subtitle
		if (!$this->navigationTitle) 
			$this->navigationTitle = $this->title;
		if (!$this->navigationSubtitle) 
			$this->navigationSubtitle = $this->description;

		// default robots tags
		if (!$this->robots)
			$this->robots = static::META_ROBOTS_DEFAULT;

		// original xml path
		$this->path = ltrim($this->path, '~/');
		$originalPath = '/' . trim($this->path, '/');
		$this->originalPath = $originalPath;

		// lang and path for url
		$secondSlashPos = mb_strpos($originalPath, '/', 1);
		$secondSlashPos = $secondSlashPos !== FALSE ? $secondSlashPos : mb_strlen($originalPath);
		$langPathPart = mb_substr($originalPath, 1, $secondSlashPos - 1);
		$path = '/' . trim($this->path, '/') . '/';
		$secondSlashPos = mb_strpos($path, '/', 1);
		$this->lang = $langPathPart;
		$this->path = rtrim(mb_substr($path, $secondSlashPos + 1), '/');

		return $this;
	}

	protected static function setUpXmlMemberByXsd (Entity & $context, \DOMElement $element, string $propertyName, string $dataType): void {
		if ($element->nodeName !== 'doc:sitemap') {
			parent::setUpXmlMemberByXsd($context, $element, $propertyName, $dataType);
		} else {
			/** @var Document $context */
			/** @var array<string, \DOMAttr> $attrs */
			$attrs = iterator_to_array($element->attributes);
			if (isset($attrs['change-freq']))
				$context->sitemapChangeFreq = $attrs['change-freq']->value;
			if (isset($attrs['priority']))
				$context->sitemapPriority = floatval($attrs['priority']->value);
		}
	}

}
