<?php
/*
 * Copyright (c) 2022 ALMEFY GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Almefy;

class Token
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $model;

    /**
     * Device constructor.
     *
     * @param string $id
     * @param string $createdAt
     * @param string $name
     * @param string $label
     * @param string $model
     */
    public function __construct($id, $createdAt, $name, $label, $model)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->label = $label;
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $array
     *
     * @return Token
     */
    public static function fromArray($array)
    {
        $id    = $array['id'] ?:  null;
        $createdAt = $array['createdAt'] ?: null;
        $name  = $array['name'] ?: null;
        $label = $array['label'] ?: null;
        $model = $array['model'] ?: null;

        return new Token($id, $createdAt, $name, $label, $model);
    }

}