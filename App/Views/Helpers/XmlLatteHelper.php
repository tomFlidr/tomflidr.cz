<?php

namespace App\Views\Helpers;

use \App\Models\Xml\Entity;

class XmlLatteHelper extends \MvcCore\Ext\Views\Helpers\AbstractHelper {
	
	protected static $instance = NULL;

	public function XmlLatte (Entity $model, array $variables = [], string $codeProp = 'body'): string {
		$viewVars = $this->view->GetData();
		$modelVars = $model->GetData();
		$vars = array_merge($viewVars, $modelVars, $variables);

		if (!isset($vars['path']) || !isset($vars[$codeProp])) 
			throw new \Exception("Entity model has not defined `path` or `{$codeProp}` in data output.");

		$modTime = $vars['modTime'] ?? 0;
		$path = $vars['path'];
		$code = $vars[$codeProp];
		unset($vars[$codeProp]);

		$tmpFullPath = $this->controller->GetApplication()->GetPathTmp(TRUE);
		$templateName = 'latte_' . md5($path) . '.latte';
		$tplFullPath = $tmpFullPath . '/' . $templateName;

		clearstatcache(TRUE, $tplFullPath);
		if (
			!file_exists($tplFullPath) || 
			$modTime > filemtime($tplFullPath)
		) {
			file_put_contents($tplFullPath, $code, LOCK_EX);
		}

		$latte = new \Latte\Engine;
		$latte->setTempDirectory($tmpFullPath);

		return $latte->renderToString($tplFullPath, $vars);
	}
}