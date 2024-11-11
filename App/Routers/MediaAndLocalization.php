<?php

namespace App\Routers;

use \App\Models\AppModule,
	\App\Models\Localizations\Localization,
	\MvcCore\Ext\Routers\IMedia;

class MediaAndLocalization extends \MvcCore\Ext\Routers\MediaAndLocalization {
	
	public const CACHE_TAGS = ['router'];

	protected static $adminRequestQueryParamName = 'admin';

	protected static $baseAuthClass = "\\MvcCore\\Ext\\Auths\\Basic";

	/** @var array */
	protected $allowedMediaVersionsAndUrlValues = [
		IMedia::MEDIA_VERSION_MOBILE	=> 'm',
		IMedia::MEDIA_VERSION_FULL		=> '',
	];
	
	/** @var array<string> */
	protected $defaultLocalization = ['en', 'GB'];
	
	/** @var array<string> */
	protected $allowedLocalizations = ['en-GB', 'de-DE', 'cs-CZ'];

	/** @var int */
	protected $sessionExpirationSeconds = 2592000;// 1 month

	/** @var array */
	protected $localizationEquivalents = [
		'en-GB'    => ['en-US', 'en-CA', 'en-AU'],
		'de-DE'    => ['de-AT'],
		'cs-CZ'    => ['sk-SK'],
	];

	/** @var bool */
	protected $autoCanonizeRequests = TRUE;
	
	/** @var bool */
	protected $stricModeBySession = FALSE;
	
	/** @var bool */
	protected $routeGetRequestsOnly = TRUE;
	
	/** @var bool */
	protected $allowNonLocalizedRoutes = TRUE;
	
	/**
	 * Init call from Bootstrap.php
	 */
	public static function Init (\MvcCore\IConfig $sysCfg): void {
		/** @var \App\Routers\MediaAndLocalization $router */
		$router = static::GetInstance();
		$router->setUpCachedRoutes();
	}

	protected function setUpCachedRoutes (): static {
		/** @var \MvcCore\Ext\ICache $cache */
		$cache = \MvcCore\Ext\Cache::GetStore(\MvcCore\Ext\Caches\Redis::class);
		$cacheKey = 'routes';
		$cacheEnabled = $cache->GetEnabled();
		/** @var $routesGroups ?array */
		$routesGroups = NULL;
		if ($cacheEnabled) 
			$routesGroups = $cache->Load('routes');
		if ($routesGroups !== NULL) {
			// set up routes from cache:
			$allRoutesByNames = [];
			foreach ($routesGroups as $routesGroup) {
				foreach ($routesGroup as $routeName => $routeInstance) {
					$allRoutesByNames[$routeName] = $routeInstance;
					$this->urlRoutes[$routeName] = $routeInstance;
					$controllerAction = $routeInstance->GetControllerAction();
					if ($controllerAction !== ':')
						$this->urlRoutes[$controllerAction] = $routeInstance;
				}
			}
			$this->routes = $allRoutesByNames;
			$this->routesGroups = $routesGroups;
			$this->anyRoutesConfigured = TRUE;
		} else {
			// set up all routes into router from code:
			$this->setUpRoutesConfiguration();
			if ($cacheEnabled) {
				foreach ($this->routes as $routeInstance)
					$routeInstance->InitAll();
				$cache->Save($cacheKey, $this->routesGroups, NULL, static::CACHE_TAGS);
			}
		}
		return $this;
	}

	protected function setUpRoutesConfiguration (): void {
		$this
			->setUpRoutesFront()
			->setUpRoutesSystem()
			->setUpRoutesVirtual();
	}
	
	protected function setUpRoutesFront (): static {
		$this->AddRoutes([
			'home'					=> [
				'match'					=> "#^/(index.php)?$#",
				'reverse'				=> '/',
				'controllerAction'		=> 'Index:Index',
			],
			'cv'					=> [
				'controllerAction'		=> 'Cv:Index',
				'pattern'				=> [
					'en'				=> '/curriculum-vitae',
					'de'				=> '/lebenslauf',
					'cs'				=> '/životopis',
				],
			],
			'training'				=> [
				'controllerAction'		=> 'Training:Index',
				'pattern'				=> [
					'en'				=> '/courses-for-professionals',
					'de'				=> '/kurse-für-fachkräfte',
					'cs'				=> '/kurzy-pro-profesionaly',
				],
			],
			'projects'				=> [
				'controllerAction'		=> 'Projects:Index',
				'pattern'				=> [
					'en'				=> '/projects',
					'de'				=> '/projekte',
					'cs'				=> '/projekty',
				],
			],
			'contact'				=> [
				'controllerAction'		=> 'Index:Contact',
				'pattern'				=> [
					'en'				=> '/contact',
					'de'				=> '/kontakt',
					'cs'				=> '/kontakt',
				],
			],
		]);
		return $this;
	}
	
	protected function setUpRoutesSystem (): static {
		$this->AddRoutes([
			new \MvcCore\Route([
				'name'							=> 'status',
				'controllerAction'				=> 'Index:Status',
				'pattern'						=> '/status',
			])
		]);
		return $this;
	}
	
	protected function setUpRoutesVirtual (): static {
		return $this;
	}
}
