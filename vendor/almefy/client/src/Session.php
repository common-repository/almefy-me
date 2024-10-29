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

class Session
{
    public const DEFAULT_TTL = 350;

    private ?string $id;

    private ?string $createdAt;

    private ?string $identifier;

    private ?string $expiresAt;

    private ?string $updatedAt;

    private ?string $deviceLabel;

    /**
     * Session constructor.
     */
    public function __construct(?string $id, ?string $createdAt, ?string $identifier, ?string $expires, ?string $updatedAt, ?string $deviceLabel)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->identifier = $identifier;
        $this->expiresAt = $expires;
        $this->updatedAt = $updatedAt;
        $this->deviceLabel = $deviceLabel;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getDeviceLabel(): ?string
    {
        return $this->deviceLabel;
    }

    public function withUpdatedExpiration(string $updatedAt, string $expiresAt): Session
    {
        $this->updatedAt = $updatedAt;
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function withUpdateAt(?string $updatedAt = null): Session
    {
        $this->updatedAt = $updatedAt ?? date(DATE_ATOM);

        return $this;
    }

    public static function fromArray($array): Session
    {
        $id = $array['id'] ?? null;
        $createdAt = $array['createdAt'] ?? date(DATE_ATOM);
        $identifier = $array['identifier'] ?? null;
        $expires = $array['expiresAt'] ?? null;
        if (is_int($expires)) {
            $expires = date(DATE_ATOM, $expires);
        }
        $updatedAt = $array['updatedAt'] ?? date(DATE_ATOM);

        $deviceLabel = $array['deviceLabel'] ?? null;

        return new Session($id, $createdAt, $identifier, $expires, $updatedAt, $deviceLabel);
    }

    /**
     * @return Session[]
     */
    public static function fromSessionArray($array): array
    {
        $sessions = [];
        foreach ($array as $key => $item) {
            $session = Session::fromArray($item);
            $sessions[$session->getId()] = $session;
        }

        return $sessions;
    }
}
