<?php

namespace App\Views\Helpers;

use \App\Models\Xml\Entity;

class XmlLatteHelper extends \MvcCore\Ext\Views\Helpers\AbstractHelper {
	
	protected static $instance = NULL;

	public function XmlLatte (?Entity $model, array $variables = [], string $codeProp = 'body'): string {
		if ($model === NULL) return '';

		$viewVars = $this->view->GetData();
		$modelVars = $model->GetData();
		$vars = array_merge($viewVars, $modelVars, $variables, [
			'controller'	=> $this->controller,
			'request'		=> $this->request,
			'response'		=> $this->response,
		]);
		
		if ((!isset($vars['originalPath']) && !isset($vars['path'])) || !isset($vars[$codeProp])) 
			throw new \Exception("Entity model has not defined `originalPath` or `path` or `{$codeProp}` in XML data.");

		$modTime = $vars['modTime'] ?? 0;
		$path = $vars['originalPath'] ?? $vars['path'];
		$code = $vars[$codeProp];
		$xmlDirFp = str_replace('\\', '/', dirname($model::GetDataDirFullPath() . $path));
		
		unset($vars[$codeProp]);
		
		return $this->renderLatte(
			$path, $code, $modTime, $vars, $xmlDirFp
		);
	}
	
	protected function renderLatte (string $path, string $code, int $modTime, array $vars, string $xmlDirFp): string {
		$tmpFullPath = $this->controller->GetApplication()->GetPathTmp(TRUE);
		$templateName = 'latte_' . md5(serialize([$xmlDirFp, $path])) . '.latte';
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
		$localization = \MvcCore\Ext\Tools\Locale::GetLocale(LC_ALL);
		$latte->setLocale("{$localization->lang}_{$localization->locale}");
		
		$latte->addFunction('hr', fn () => new \Latte\Runtime\Html($this->view->Hr()));
		$latte->addFunction('incl', function ($path) use ($vars, $xmlDirFp) {
			$firstChars = mb_substr($path, 0, 2);
			if ($firstChars === './') {
				$fullPath = $xmlDirFp . mb_substr($path, 1) . '.latte';
			} else {
				$fullPath = $xmlDirFp . '/' . $path . '.latte';
			}
			$code = file_get_contents($fullPath);
			$modTime = filemtime($fullPath);
			$result = $this->renderLatte(
				$path, $code, $modTime, $vars, $xmlDirFp
			);
			return new \Latte\Runtime\Html($result);
		});
		$latte->addFunction('url', fn ($route, $params = [])  => $this->controller->Url($route, $params));
		$latte->addExtension(new \Latte\Essential\RawPhpExtension);
		return $latte->renderToString($tplFullPath, $vars);
	}

}