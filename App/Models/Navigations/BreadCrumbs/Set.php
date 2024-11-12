<?php

namespace App\Models\Navigations\BreadCrumbs;

class Set extends \MvcCore\Ext\Tools\Collections\Set {
	
	public function current (): \App\Models\Navigations\BreadCrumbs\Item {
		return parent::current();
	}
	
	/**
	 * @param  int                                      $key 
	 * @param  \App\Models\Navigations\BreadCrumbs\Item $value 
	 */
	public function offsetSet ($key, $value): void {
		parent::offsetSet($key, $value);
	}
	
	/**
	 * @param  int $key 
	 */
	public function offsetGet (mixed $key): mixed {
		return parent::offsetGet($key);
	}
	
	public function shift (): \App\Models\Navigations\BreadCrumbs\Item {
		return parent::shift();
	}

}
