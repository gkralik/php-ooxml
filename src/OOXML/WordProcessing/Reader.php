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

namespace GKralik\OOXML\WordProcessing;

use DOMElement;
use GKralik\OOXML\Common\XMLReader;
use GKralik\OOXML\WordProcessing\Elements\Image;
use GKralik\OOXML\WordProcessing\Elements\Paragraph;
use GKralik\OOXML\WordProcessing\Elements\Run;
use GKralik\OOXML\WordProcessing\Elements\Text;
use ZipArchive;

class Reader
{
	/** @var string */
	private $docFile;

	/** @var array */
	private $relationships = [];

	/** @var XMLReader */
	private $documentReader = null;

	/** @var WordDocument */
	private $wordDocument;


	public function __construct($documentFile)
	{
		$this->docFile = $documentFile;
		if (!file_exists($documentFile))
		{
			throw new \RuntimeException("Document file {$documentFile} not found");
		}

		$this->wordDocument = new WordDocument();

		$this->readRelationships();
		$this->readDocument();
	}


	public function readDocument()
	{
		$this->documentReader = new XMLReader();
		$this->documentReader->loadFromZip($this->docFile, 'word/document.xml');

		$nodes = $this->documentReader->getElements('w:body/*');
		if ($nodes->length > 0)
		{
			$section = $this->wordDocument->addSection();

			foreach ($nodes as $node)
			{
				if ($node->nodeName == 'w:p')
				{
					$paragraph = $this->readParagraph($node);
					$section->addElement($paragraph);
				}
				else
				{
					// ignored
				}
			}
		}
	}

	protected function readParagraph(DOMElement $node)
	{
		$paragraph = new Paragraph();

		if ($this->documentReader->elementExists('w:pPr', $node))
		{
			$paragraph->setStyle($this->readParagraphStyle($node));
		}

		$runNodes = $this->documentReader->getElements('w:r', $node);
		foreach ($runNodes as $runNode)
		{
			$run = $this->readRun($runNode);
			$paragraph->addElement($run);
		}
		unset($runNode);

		return $paragraph;
	}

	protected function readRun(DOMElement $node)
	{
		$run = new Run();

		$elements = $this->documentReader->getElements('*', $node);
		foreach ($elements as $element)
		{
			if ($element->nodeName == 'w:t')
			{
				$textContent = $element->nodeValue;
				$run->addElement(new Text($textContent));
			}
			else if ($element->nodeName == 'w:drawing')
			{
				$drawing = $this->readDrawing($element);
				if ($drawing !== null)
				{
					$run->addElement($drawing);
				}
			}
			else
			{
				// ignored
			}
		}
		unset($element);

		return $run;
	}

	protected function readDrawing(DOMElement $node)
	{
		$blipNode = $this->documentReader->getElement('.//*[local-name() = "blip"]', $node);
		if ($blipNode === null)
		{
			return null;
		}

		// get r:embed attribute
		$rId = $blipNode->getAttribute('r:embed');

		// find image in rels
		$imageRel = isset($this->relationships['document'][$rId]) ? $this->relationships['document'][$rId] : null;

		$image = new Image($rId, "zip://{$this->docFile}#{$imageRel['target']}");

		return $image;
	}

	protected function readParagraphStyle(DOMElement $node)
	{
		if (!$this->documentReader->elementExists('w:pPr', $node))
		{
			return [];
		}

		$styleNode = $this->documentReader->getElement('w:pPr', $node);
		$styles    = [
			'styleName' => $this->documentReader->getAttribute('w:val', $styleNode, 'w:pStyle'),
		];

		return $styles;
	}

	public function readRelationships()
	{
		$this->relationships = [];

		// _rels/.rels
		$this->relationships['main'] = $this->readRels('_rels/.rels');

		// word/_rels/*.xml.rels
		$wordRelsPath = 'word/_rels/';
		$zip          = new ZipArchive();
		if ($zip->open($this->docFile) === true)
		{
			for ($i = 0; $i < $zip->numFiles; $i++)
			{
				$xmlFile = $zip->getNameIndex($i);
				if ((substr($xmlFile, 0, strlen($wordRelsPath))) == $wordRelsPath && (substr($xmlFile, -1)) != '/')
				{
					$docPart = str_replace('.xml.rels', '', str_replace($wordRelsPath, '', $xmlFile));

					$this->relationships[$docPart] = $this->readRels($xmlFile, 'word/');
				}
			}
			$zip->close();
		}
	}

	/**
	 * @param string $xmlFile
	 * @param string $targetPrefix
	 *
	 * @return array
	 */
	private function readRels($xmlFile, $targetPrefix = '')
	{
		$prefixesToRemove = [
			'http://schemas.openxmlformats.org/package/2006/relationships/metadata/',
			'http://schemas.openxmlformats.org/officeDocument/2006/relationships/',
		];

		$rels = array();

		$xmlReader = new XMLReader();
		$xmlReader->loadFromZip($this->docFile, $xmlFile);
		$nodes = $xmlReader->getElements('*');
		foreach ($nodes as $node)
		{
			$rId    = $node->getAttribute('Id');
			$type   = $node->getAttribute('Type');
			$target = $node->getAttribute('Target');
			$targetMode = $node->getAttribute('TargetMode');

			// remove the prefixes from type
			$type    = str_replace($prefixesToRemove, '', $type);
			$docPart = str_replace('.xml', '', $target);

			// prepend targetPrefix if rel is not external
			if ($targetMode !== 'External') {
				$target = $targetPrefix . $target;
			}

			$rels[$rId] = [
				'type'    => $type,
				'target'  => $target,
				'docPart' => $docPart
			];
		}
		ksort($rels);

		return $rels;
	}

	/**
	 * @return null|WordDocument
	 */
	public function getWordDocument()
	{
		return $this->wordDocument;
	}
}