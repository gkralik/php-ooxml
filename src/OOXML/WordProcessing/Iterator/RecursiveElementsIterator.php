<?php

/**
 * Copyright 2017 Gregor Kralik
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace GKralik\OOXML\WordProcessing\Iterator;

use GKralik\OOXML\WordProcessing\Elements\AbstractContainer;
use GKralik\OOXML\WordProcessing\Elements\AbstractElement;
use RecursiveIterator;

class RecursiveElementsIterator implements RecursiveIterator
{
	/** @var AbstractElement[] */
	private $elements;

	/**
	 * RecursiveElementsIterator constructor.
	 *
	 * @param AbstractElement[] $elements
	 */
	public function __construct(&$elements)
	{
		$this->elements = $elements;
	}

	/**
	 * @return AbstractElement
	 */
	public function current()
	{
		return current($this->elements);
	}

	public function next()
	{
		next($this->elements);

		return;
	}

	/**
	 * @return mixed
	 */
	public function key()
	{
		return key($this->elements);
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		return current($this->elements) !== false;
	}

	public function rewind()
	{
		reset($this->elements);

		return;
	}

	/**
	 * @return bool
	 */
	public function hasChildren()
	{
		$current = $this->current();

		if (!$current instanceof AbstractContainer)
		{
			return false;
		}

		$elements = $current->getElements();

		return is_array($elements) && count($elements) > 0;
	}

	/**
	 * @return static
	 */
	public function getChildren()
	{
		$elements = $this->current()->getElements();

		return new static($elements);
	}
}
