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

namespace GKralik\OOXML\WordProcessing\Elements;


class Image extends AbstractElement
{
	/** @var string */
	private $id;

	/** @var string */
	private $target;

	/** @var string */
	private $name;

	/** @var string */
	private $imageType;

	/** @var int */
	private $width;

	/** @var int */
	private $height;

	public function __construct($id, $target, $parentElement = null)
	{
		$this->id     = $id;
		$this->target = $target;

		parent::__construct($parentElement);

		$this->analyzeImage();
	}

	public function getId()
	{
		return $this->id;
	}

	public function getTarget()
	{
		return $this->target;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getImage()
	{
		return file_get_contents($this->target);
	}

    /**
     * @return string
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

	public function __toString()
	{
		return "<image:{$this->id}:{$this->target}>";
	}

	private function analyzeImage()
	{
		$this->name = basename($this->target);

		$imageData = getimagesize($this->target);
		if ($imageData === false) {
			// bail out
			return;
		}

		list($this->width, $this->height, $imageType) = $imageData;

		// TODO check image type and react if there are weird types like EMF or WMF
		$this->imageType = image_type_to_mime_type($imageType);
	}
}