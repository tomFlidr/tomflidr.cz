<?php

namespace App\Models\Xml\Entities;

/**
 * @method static array<Document> GetByDirPath(string $path, bool $includingParentLevelDoc = FALSE, array $sort = [])
 */
class Document extends \App\Models\Xml\Entity {
	
	public const ROUTE_NAME = 'document';
	public const META_ROBOTS_DEFAULT = 'index,follow,archive';

	protected static string	$dataDir = '~/Var/Documents';
	
	use \App\Models\Xml\Entities\Document\Props,
		\App\Models\Xml\Entities\Document\GettersSetters,
		\App\Models\Xml\Entities\Document\SetUp,
		\App\Models\Xml\Entities\Document\RouterFiltering,
		\App\Models\Xml\Entities\Document\StaticGetters;
	
	public function GetUrl (array $urlParams = []) {
		return self::GetRouter()->Url(
			self::ROUTE_NAME, array_merge(['path' => $this->path], $urlParams)
		);
	}

	/** @return array<string, mixed> */
	public function GetData (): array {
		$data = parent::GetData();
		$data['lang'] = $this->lang;
		$data['originalPath'] = $this->originalPath;
		return $data;
	}

}
