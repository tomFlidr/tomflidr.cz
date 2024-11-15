<?php

namespace App\Models\Xml\Document;

/**
 * @mixin \App\Models\Xml\Document
 */
trait SetUp {
	
	protected function xmlSetUp (string $content, string $path, \DOMDocument & $xml, array $schemes): static {
		parent::xmlSetUp($content, $path, $xml, $schemes);
		
		// default values for navigation title and subtitle
		if (!$this->navigationTitle) 
			$this->navigationTitle = $this->title;
		if (!$this->navigationSubtitle) 
			$this->navigationSubtitle = $this->description;

		// default robots tags
		if (!$this->robots)
			$this->robots = static::META_ROBOTS_DEFAULT;

		// original xml path
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

}
