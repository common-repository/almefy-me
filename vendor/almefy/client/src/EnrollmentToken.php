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

class EnrollmentToken
{

    private ?string $id;

    private ?string $createdAt;

    private ?string $expiresAt;

    private ?string $base64ImageData;

    private ?Identity $identity;

    /**
     * ProvisioningToken constructor.
     */
    public function __construct(?string $id, ?string $createdAt, ?string $expiresAt, ?string $base64ImageData, ?Identity $identity)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->base64ImageData = $base64ImageData;
        $this->identity = $identity;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function getBase64ImageData(): ?string
    {
        return $this->base64ImageData;
    }

    public function getIdentity(): ?Identity
    {
        return $this->identity;
    }

    public static function fromArray(array $array = []): EnrollmentToken
    {
        $id = $array['id'] ?? null;
        $createdAt = $array['createdAt'] ?? null;
        $expiresAt = $array['expiresAt'] ?? null;
        $base64ImageData = $array['base64ImageData'] ?? null;
        $identity = Identity::fromArray($array['identity']);

        return new EnrollmentToken($id, $createdAt, $expiresAt, $base64ImageData, $identity);
    }
}