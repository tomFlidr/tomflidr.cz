<?php

namespace App\Models\Xml\Entities;

/**
 * @method static array<Document> GetByDirPath(string $relPath, bool $includingParentLevelDoc = FALSE, array $sort = [])
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

	public function ProcessBody (\MvcCore\View $view): string {
		$data = [];

		$data['lang'] = $this->lang;
		$data['path'] = $this->path;
		$data['originalPath'] = $this->originalPath;

		foreach ($this->schemas as $schema) {
			foreach ($schema->GetMembers() as $member) {
				$propName = $member->GetPropName();
				if (isset($this->{$propName}))
					$data[$propName] = $this->{$propName};
			}
		}

		x($this);
		x($data);
		//return $this->body;

		$tmpFullPath = self::GetApp()->GetPathTmp(TRUE);
		$templateName = implode('_', ['doc', $this->lang, md5($this->path)]) . '.latte';
		$tplFullPath = $tmpFullPath . '/' . $templateName;

		clearstatcache(TRUE, $tplFullPath);
		if (
			!file_exists($tplFullPath) ||
			$this->modTime > filemtime($tplFullPath)
		) {
			file_put_contents($tplFullPath, $this->body, LOCK_EX);
		}

		$latte = new \Latte\Engine;
		$latte->setTempDirectory($tmpFullPath);

		$output = $latte->renderToString($tplFullPath, $data);

		return $output;
	}
}
