<?php

namespace App\Models\Xml\Entity;

/**
 * @mixin \App\Models\Xml\Entity
 */
trait Props {
	
	protected int			$modTime;
	protected string		$path;
	protected ?\DOMDocument	$xml		= NULL;
	protected ?string		$xmlCode	= NULL;
	/** @var array<\App\Models\Xml\Schema> */
	protected array			$schemas	= [];

}
