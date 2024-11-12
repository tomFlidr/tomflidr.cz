<?php

namespace App\Models\Navigations\BreadCrumbs;

class Item {
	
	protected bool $isFirst = FALSE;
	protected bool $isLast = FALSE;

	public function __construct (
		protected string $text,
		protected ?string $url = NULL
	) {
	}

	public function SetText (string $text): static {
		$this->text = $text;
		return $this;
	}

	public function GetText (): string {
		return $this->text;
	}

	public function SetUrl (?string $url): static {
		$this->url = $url;
		return $this;
	}

	public function GetUrl (): ?string {
		return $this->url;
	}

	public function SetIsFirst (bool $isFirst): static {
		$this->isFirst = $isFirst;
		return $this;
	}

	public function GetIsFirst (): bool {
		return $this->isFirst;
	}

	public function SetIsLast (bool $isLast): static {
		$this->isLast = $isLast;
		return $this;
	}

	public function GetIsLast (): bool {
		return $this->isLast;
	}
}