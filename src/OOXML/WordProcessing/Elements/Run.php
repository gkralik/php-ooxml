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


class Run extends AbstractContainer
{
    /** @var array */
    private $properties;

    /**
     * @param string $propertyName
     *
     * @return mixed|null
     */
    public function getProperty($propertyName)
    {
        if (!isset($this->properties[$propertyName])) {
            return null;
        }

        return $this->properties[$propertyName];
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return Run
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }


}