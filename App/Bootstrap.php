<?php

namespace App;

use \MvcCore\Environment\IConstants as EnvConsts;

class Bootstrap {

	/**
	 * @return \MvcCore\Application
	 */
	public static function Init (): ?\MvcCore\Application {

		$app = \MvcCore\Application::GetInstance();
		
		static::patchCoreClasses($app);
		
		// PHP 8+ and Attributes anotation:
		$app->SetAttributesAnotations(TRUE);

		// for new browsers supporting cookie with SameSite=Strict:
		$app->SetSecurityProtection(\MvcCore\Application\IConstants::SECURITY_PROTECTION_COOKIE);

		$cache = static::getCache($app);
		
		static::getEnvironment($app);

		// Uncomment those lines to develop application startup (eg: Bootstrap, Router):
		//$env = static::getEnvironment($app);
		//if ($env->IsDevelopment()) \MvcCore\Ext\Debugs\Tracy::Init();
		
		$sysCfg = static::getSysConfig($app, $cache);
		
		if (!static::verifyHttpHost($app, $sysCfg)) return NULL;
		
		\App\Routers\MediaAndLocalization::Init($sysCfg);
		$app->AddPostRouteHandler(fn () => \App\Controllers\Base::PostRouteHandler());

		return $app;
	}
	
	protected static function patchCoreClasses (\MvcCore\Application $app): void {
		$app
			->SetDebugClass(\MvcCore\Ext\Debugs\Tracy::class)
			->SetConfigClass(\MvcCore\Ext\Configs\Cached::class)
			->SetRouterClass(\App\Routers\MediaAndLocalization::class)
			->SetDefaultControllerName('//'.\App\Controllers\Fronts\Index::class);
	}
	
	protected static function getCache (\MvcCore\Application $app): \MvcCore\Ext\ICache {
		$cacheDbName = 'tomflidr.cz';
		$cache = \MvcCore\Ext\Caches\Redis::GetInstance([
			\MvcCore\Ext\ICache::CONNECTION_PERSISTENCE	=> $cacheDbName,
			\MvcCore\Ext\ICache::CONNECTION_DATABASE	=> $cacheDbName,
			/*\MvcCore\Ext\ICache::PROVIDER_CONFIG		=> [
				'\Redis::OPT_SERIALIZER'				=> '\Redis::SERIALIZER_PHP'
			]*/
		]);
		\MvcCore\Ext\Cache::RegisterStore(
			\MvcCore\Ext\Caches\Redis::class, $cache, TRUE
		);
		$cache->Connect();
		return $cache;
	}
	
	protected static function getEnvironment (\MvcCore\Application $app): \MvcCore\Environment {
		\MvcCore\Config::SetConfigEnvironmentPath('~/App/env.ini');
		$env = $app->GetEnvironment();
		$req = $app->GetRequest();
		if ($req->IsCli()) {
			$envName = $req->GetParam('env_name', 'a-z');
			if (in_array($envName, \MvcCore\Environment::GetAllNames(), TRUE)) {
				$env->SetName($envName);
			} else {
				$env->GetName();	
			}
		} else {
			$env->GetName();
		}
		return $env;
	}
	
	protected static function getSysConfig (\MvcCore\Application $app, \MvcCore\Ext\ICache $cache): ?\MvcCore\Config {
		$env = $app->GetEnvironment();
		$isDev = $env->IsDevelopment();
		if ($isDev)
			\MvcCore\Config::SetConfigSystemPath('~/App/config.dev.ini');
		\MvcCore\Ext\Configs\Cached::SetEnvironmentGroups([
			EnvConsts::PRODUCTION 	=> [EnvConsts::GAMMA],
			EnvConsts::GAMMA 		=> [EnvConsts::PRODUCTION],
			EnvConsts::DEVELOPMENT 	=> [
				EnvConsts::PRODUCTION,
				EnvConsts::ALPHA,
				EnvConsts::BETA,
				EnvConsts::GAMMA
			],
		]);
		$sysCfg = \MvcCore\Config::GetConfigSystem();
		// if cache is disabled in config, disable cache amd load env and config from hdd
		if (isset($sysCfg->app->cache) && !$sysCfg->app->cache && $cache->GetEnabled()) {
			// disable cache
			$cache->SetEnabled(FALSE);
			// reinitialize environment from hdd
			$env->SetName(NULL);
			self::getEnvironment($app);
			// load sysconfig from hdd
			$sysCfg = \MvcCore\Config::GetConfigSystem();
		}
		if ($isDev && isset($sysCfg->debug->editorLink))
			\Tracy\Debugger::$editor = $sysCfg->debug->editorLink;
		return $sysCfg;
	}
	
	protected static function verifyHttpHost (\MvcCore\Application $app, \MvcCore\Config $sysCfg): bool {
		$req = $app->GetRequest();
		$env = $app->GetEnvironment();
		$serverGlobals = & $req->GetGlobalCollection('server');
		if (!$env->IsDevelopment() && isset($serverGlobals['HTTP_HOST'])) {
			$rawHost = $serverGlobals['HTTP_HOST'];
			/** @var \stdClass $hostVerify */
			$hostVerify = $sysCfg->app->hostVerify;
			$req->SetPort(''); // app is always using default port not necessary to define
			if (preg_match($hostVerify->pattern, $rawHost)) {
				$req->SetHostName($rawHost);
			} else {
				$url = $app
					->GetRouter()
						->SetRequest($req->SetHostName($hostVerify->baseHost))
						->Url($app->GetDefaultControllerName(), ['absolute' => TRUE]);
				$app->GetResponse()
					->SetCode(\MvcCore\Response\IConstants::SEE_OTHER)
					->SetHeader('Location', $url);
				$app->Terminate();
				return FALSE;
			}
		}
		return TRUE;
	}
	
}
