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

namespace GKralik\OOXML\Common;


use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use ZipArchive;

class XMLReader
{
	/** @var DOMDocument */
	private $dom = null;

	/** @var DOMXPath */
	private $xpath = null;

	/**
	 * @param string $zipFile
	 * @param string $xmlFile
	 *
	 * @return bool|DOMDocument
	 */
	public function loadFromZip($zipFile, $xmlFile)
	{
		if (!file_exists($zipFile))
		{
			throw new \RuntimeException('Could not find archive file ' . $zipFile);
		}

		$zip = new ZipArchive();
		$zip->open($zipFile);
		$contents = $zip->getFromName($xmlFile);
		$zip->close();

		if ($contents === false)
		{
			return false;
		}

		return $this->loadFromString($contents);
	}

	/**
	 * @param string $content
	 *
	 * @return DOMDocument
	 */
	public function loadFromString($content)
	{
		$this->dom = new DOMDocument();
		$this->dom->loadXML($content);

		return $this->dom;
	}

	/**
	 * @param string          $path XPath
	 * @param DOMElement|null $contextNode
	 *
	 * @return array|DOMNodeList|DOMElement[]
	 */
	public function getElements($path, DOMElement $contextNode = null)
	{
		if (!$this->dom)
		{
			return [];
		}

		if (!$this->xpath)
		{
			$this->xpath = new DOMXPath($this->dom);
		}

		return $this->xpath->query($path, $contextNode);
	}

	/**
	 * @param string          $path
	 * @param DOMElement|null $contextNode
	 *
	 * @return DOMElement|null
	 */
	public function getElement($path, DOMElement $contextNode = null)
	{
		$elements = $this->getElements($path, $contextNode);

		return $elements->length > 0 ? $elements->item(0) : null;
	}

	/**
	 * @param string          $path
	 * @param DOMElement|null $contextNode
	 *
	 * @return bool
	 */
	public function elementExists($path, DOMElement $contextNode = null)
	{
		return $this->getElements($path, $contextNode)->length > 0;
	}

	public function getAttribute($attribute, DOMElement $contextNode = null, $path = null)
	{
		$return = null;
		if ($path !== null) {
			$element = $this->getElement($path, $contextNode);
			if ($element !== null) {
				/** @var DOMElement $node */
				$return = $element->getAttribute($attribute);
			}
		} else {
			if ($contextNode !== null) {
				$return = $contextNode->getAttribute($attribute);
			}
		}

		return ($return == '') ? null : $return;
	}
}