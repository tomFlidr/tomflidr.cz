<?php

namespace App\Models\Navigations;

use \App\Models\Navigations\Item;

class Set extends \MvcCore\Ext\Tools\Collections\Set {

	public function current (): Item {
		return parent::current();
	}
	/**
	 * @param  int   $offset 
	 * @param  Item $value 
	 */
	public function offsetSet ($offset, $value): void {
		parent::offsetSet($offset, $value);
	}
	/** @param int $offset */
	public function offsetGet ($offset): Item {
		return parent::offsetGet($offset);
	}
	public function shift (): Item {
		return parent::shift();
	}
	/**
	 * @param Item $values,...
	 */
	public function unshift (): int {
		return parent::unshift();
	}
	public function splice (int $offset, int $length, array $replacement = []): array {
		$replacementCnt = count($replacement);
		if (
			count($replacement) === 0 && 
			$this->position >= $offset && 
			$this->position <= $offset + $length &&
			$this->position >= $this->count - $length
		) {
			$this->position -= 1;
			if ($this->position < 0)
				$this->position = 0;
		}
		$this->count = $this->count - $length + $replacementCnt;
		$keys = array_splice($this->keys, $offset, $length, array_keys($replacement));
		$values = array_splice($this->array, $offset, $length, array_values($replacement));
		return array_combine($keys, $values);
	}
}