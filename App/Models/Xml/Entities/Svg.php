<?php

namespace App\Models\Xml\Entities;

/**
 * @method static array<Svg> GetByDirPath(string $path, bool $includingParentLevelDoc = FALSE, array $sort = [])
 */
class Svg extends \App\Models\Xml\Entity {
	
	public const EXTENSIONS = ['svg'];
	
	protected string $name;
	protected float $width;
	protected float $height;
	
	public function GetName (): string {
		return $this->name;
	}
	public function GetWidth (): float {
		return $this->width;
	}
	public function GetHeight (): float {
		return $this->height;
	}

	public function GetHtmlCode (): string {
		$svgCode = $this->xmlCode;
		$xmlHeadPosBegin = mb_strpos($this->xmlCode, '<?xml ');
		if ($xmlHeadPosBegin !== FALSE) {
			$xmlHeadPosEnd = mb_strpos($this->xmlCode, '?>', $xmlHeadPosBegin + 6);
			if ($xmlHeadPosEnd !== FALSE)
				$svgCode = mb_substr($this->xmlCode, $xmlHeadPosEnd + 2);
		}
		return $svgCode;
	}

	protected function xmlSetUpMembers (): static {
		/** @var \App\Models\Xml\Entities\Svg $this */
		parent::xmlSetUpMembers();
		
		$svgElms = iterator_to_array($this->xml->getElementsByTagName('svg'));
		$svgElm = $svgElms[0];
		$viewBoxAttr = $svgElm->getAttribute('viewBox');
		$sizes = explode(' ', trim($viewBoxAttr));
		[$x, $y, $width, $height] = array_map('floatval', $sizes);
		$width -= $x;
		$height -= $y;
		$this->width = $width;
		$this->height = $height;

		$this->name = basename($this->path);

		return $this;
	}
}
