<?php

namespace App\Models\Navigations;

use \App\Models\Navigations\Set;

class Item {
	
	protected string	$title;
	protected ?string	$description		= NULL;
	protected ?string	$url				= NULL;
	protected ?string	$routeName			= NULL;
	protected array		$subRouteNames		= [];
	protected bool		$selected			= FALSE;
	protected ?string	$cssClass			= NULL;
	protected ?Set		$items				= NULL;

	public function __construct (
		string			$title,
		?string			$description		= NULL,
		?string			$url				= NULL,
		?string			$routeName			= NULL,
		?array			$subRouteNames		= [],
		?bool			$selected			= FALSE,
		?string			$cssClass			= NULL,
		Set|array|NULL	$items				= []
	) {
		$this->title = $title;
		$this->url = $url;
		if ($description !== NULL)
			$this->description = $description;
		if ($routeName !== NULL)
			$this->routeName = $routeName;
		if ($subRouteNames !== NULL)
			$this->subRouteNames = $subRouteNames;
		if ($selected !== NULL)
			$this->selected = $selected;
		if ($cssClass !== NULL)
			$this->cssClass = $cssClass;
		if ($items !== NULL) 
			$this->items = is_array($items) ? new Set($items) : $items;
	}

	public function GetTitle (): string {
		return $this->title;
	}
	public function SetTitle (string $title): static {
		$this->title = $title;
		return $this;
	}
	
	public function GetDescription (): ?string {
		return $this->description;
	}
	public function SetDescription (?string $description): static {
		$this->description = $description;
		return $this;
	}

	public function GetUrl (): ?string {
		return $this->url;
	}
	public function SetUrl (?string $url): static {
		$this->url = $url;
		return $this;
	}
	
	public function GetRouteName (): ?string {
		return $this->routeName;
	}
	public function SetRouteName (?string $routeName): static {
		$this->routeName = $routeName;
		return $this;
	}

	public function GetSubRouteNames (): array {
		return $this->subRouteNames;
	}
	public function SetSubRouteNames (array $subRouteNames): static {
		$this->subRouteNames = $subRouteNames;
		return $this;
	}

	public function GetSelected (): bool {
		return $this->selected;
	}
	public function SetSelected (bool $selected): static {
		$this->selected = $selected;
		return $this;
	}

	public function GetCssClass (): ?string {
		return $this->cssClass;
	}
	public function SetCssClass (?string $cssClass): static {
		$this->cssClass = $cssClass;
		return $this;
	}

	public function GetItems (): ?Set {
		return $this->items;
	}
	public function SetItems (Set|array|NULL $items): static {
		$this->items = is_array($items) ? new Set($items) : $items;
		return $this;
	}
	
	public function RemoveItem (Item $item): static {
		if ($this->items === NULL) {
			return $this;
		} else {
			$offset = array_search($item, $this->items, TRUE);
			if ($this->items instanceof Set) {
				$this->items->splice($offset, 1);
			} else {
				array_splice($this->items, $offset, 1);
			}
			return $this;
		}
	}

}