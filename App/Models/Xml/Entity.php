<?php

namespace App\Models\Xml;

use \App\Models\Xml\Schema;

class Entity extends \App\Models\Xml\Base {
	
	use \App\Models\Xml\Entity\Props,
		\App\Models\Xml\Entity\GettersSetters,
		\App\Models\Xml\Entity\SetUp,
		\App\Models\Xml\Entity\StaticGetters;

	/** @return array<string mixed> */
	public function GetData (): array {
		$data = [];
		$data['modTime'] = $this->modTime;
		$data['path'] = $this->path;
		$data['originalPath'] = $this->originalPath;
		foreach ($this->schemas as $schema) {
			foreach ($schema->GetMembers() as $member) {
				$propName = $member->GetPropName();
				if (isset($this->{$propName}))
					$data[$propName] = $this->{$propName};
			}
		}
		return $data;
	}
	
}
