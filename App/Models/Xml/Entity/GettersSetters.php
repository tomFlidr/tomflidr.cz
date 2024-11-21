<?php

namespace App\Models\Xml\Entity;

/**
 * @mixin \App\Models\Xml\Entity
 */
trait GettersSetters {

	public function GetModTime (): int {
		return $this->modTime;
	}
	public function SetModTime (string $modTime): int {
		$this->modTime = $modTime;
		return $this;
	}

	public function GetPath (): string {
		return $this->path;
	}
	public function SetPath (string $path): static {
		$this->path = $path;
		return $this;
	}

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

}
