<?php

namespace App\Models\Xml\Entities\Document;

/**
 * @mixin \App\Models\Xml\Entities\Document
 */
trait GettersSetters {

	public function GetLang (): string {
		return $this->lang;
	}
	public function SetLang (string $lang): static {
		$this->lang = $lang;
		return $this;
	}

	public function GetOriginalPath (): string {
		return $this->originalPath;
	}
	public function SetOriginalPath (string $originalPath): static {
		$this->originalPath = $originalPath;
		return $this;
	}

	// xml data:

	public function GetTitle (): string {
		return $this->title;
	}
	public function SetTitle (string $title): static {
		$this->title = $title;
		return $this;
	}

	public function GetSequence (): int {
		return $this->sequence;
	}
	public function SetSequence (int $sequence): static {
		$this->sequence = $sequence;
		return $this;
	}

	public function GetActive (): bool {
		return $this->active;
	}
	public function SetActive (bool $active): static {
		$this->active = $active;
		return $this;
	}

	public function GetController (): ?string {
		return $this->controller;
	}
	public function SetController (?string $controller): static {
		$this->controller = $controller;
		return $this;
	}

	public function GetAction (): ?string {
		return $this->action;
	}
	public function SetAction (?string $action): static {
		$this->action = $action;
		return $this;
	}

	public function GetDescription (): ?string {
		return $this->description;
	}
	public function SetDescription (?string $description): static {
		$this->description = $description;
		return $this;
	}

	public function GetKeywords (): ?string {
		return $this->keywords;
	}
	public function SetKeywords (?string $keywords): static {
		$this->keywords = $keywords;
		return $this;
	}

	public function GetRobots (): ?string {
		return $this->robots;
	}
	public function SetRobots (?string $robots): static {
		$this->robots = $robots;
		return $this;
	}

	public function GetOgImage (): ?string {
		return $this->ogImage;
	}
	public function SetOgImage (?string $ogImage): static {
		$this->ogImage = $ogImage;
		return $this;
	}

	public function GetOgTitle (): ?string {
		return $this->ogTitle;
	}
	public function SetOgTitle (?string $ogTitle): static {
		$this->ogTitle = $ogTitle;
		return $this;
	}

	public function GetOgDescription (): ?string {
		return $this->ogDescription;
	}
	public function SetOgDescription (?string $ogDescription): static {
		$this->ogDescription = $ogDescription;
		return $this;
	}

	public function GetItempropName (): ?string {
		return $this->itempropName;
	}
	public function SetItempropName (?string $itempropName): static {
		$this->itempropName = $itempropName;
		return $this;
	}

	public function GetItempropDescription (): ?string {
		return $this->itempropDescription;
	}
	public function SetItempropDescription (?string $itempropDescription): static {
		$this->itempropDescription = $itempropDescription;
		return $this;
	}

	public function GetNavigationTitle (): string {
		return $this->navigationTitle;
	}
	public function SetNavigationTitle (string $navigationTitle): static {
		$this->navigationTitle = $navigationTitle;
		return $this;
	}

	public function GetNavigationSubtitle (): ?string {
		return $this->navigationSubtitle;
	}
	public function SetNavigationSubtitle (?string $navigationSubtitle): static {
		$this->navigationSubtitle = $navigationSubtitle;
		return $this;
	}

	public function GetPerex (): ?string {
		return $this->perex;
	}
	public function SetPerex (?string $perex): static {
		$this->perex = $perex;
		return $this;
	}

	public function GetBody (): ?string {
		return $this->body;
	}
	public function SetBody (?string $body): static {
		$this->body = $body;
		return $this;
	}

}
