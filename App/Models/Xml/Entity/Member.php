<?php

namespace App\Models\Xml\Entity;

readonly class Member {

	protected string $nodeName;
	protected string $dataType;
	protected string $propName;

	public function __construct(string $nodeName, string $dataType, string $propName) {
		$this->nodeName = $nodeName;
		$this->dataType = $dataType;
		$this->propName = $propName;
	}
	
	public function GetNodeName (): string {
		return $this->nodeName;
	}
	public function GetDataType (): string {
		return $this->dataType;
	}
	public function GetPropName (): string {
		return $this->propName;
	}
	
}