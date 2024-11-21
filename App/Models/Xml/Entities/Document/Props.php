<?php

namespace App\Models\Xml\Entities\Document;

/**
 * @mixin \App\Models\Xml\Entities\Document
 */
trait Props {
	
	protected string	$lang;
	protected string	$originalPath;

	// xml data
	protected string	$title;
	protected int		$sequence;
	protected bool		$active					= FALSE;
	protected ?string	$controller				= NULL;
	protected ?string	$action					= NULL;
	protected ?string	$description			= NULL;
	protected ?string	$keywords				= NULL;
	protected ?string	$robots					= NULL;
	protected ?string	$ogImage				= NULL;
	protected ?string	$ogTitle				= NULL;
	protected ?string	$ogDescription			= NULL;
	protected ?string	$itempropName			= NULL;
	protected ?string	$itempropDescription	= NULL;
	protected ?string	$navigationTitle;
	protected ?string	$navigationSubtitle		= NULL;
	protected ?string	$perex					= NULL;
	protected ?string	$body					= NULL;

}
