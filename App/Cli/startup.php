<?php

	define('MVCCORE_APP_ROOT', str_replace('\\', '/', dirname(__DIR__, 2)));
	
	@include_once(MVCCORE_APP_ROOT . '/vendor/autoload.php');
	
	\App\Bootstrap::Init();