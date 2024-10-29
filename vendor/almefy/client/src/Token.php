<?php
/*
 * Copyright (c) 2023 ALMEFY GmbH
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

namespace Almefy;

class Token
{

    private ?string $id;

    private ?string $createdAt;

    private ?string $name;

    private ?string $label;

    private ?string $model;

    /**
     * Device constructor.
     */
    public function __construct(?string $id, ?string $createdAt, ?string $name, ?string $label, ?string $model)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->label = $label;
        $this->model = $model;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public static function fromArray(array $array = []): Token
    {
        $id    = $array['id'] ??  null;
        $createdAt = $array['createdAt'] ?? null;
        $name  = $array['name'] ?? null;
        $label = $array['label'] ?? null;
        $model = $array['model'] ?? null;

        return new Token($id, $createdAt, $name, $label, $model);
    }

}