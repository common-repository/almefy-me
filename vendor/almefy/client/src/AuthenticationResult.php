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

class AuthenticationResult
{
    private ?string $identifier;

    private ?string $role;

    private ?Session $session;

    /**
     * AuthenticationToken constructor.
     */
    public function __construct(?string $identifier, ?string $challenge, ?Session $session)
    {
        $this->identifier = $identifier;
        $this->role = $challenge;
        $this->session = $session;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public static function fromArray(array $array = []): AuthenticationResult
    {
        $identifier = $array['identifier'] ?? null;
        $role = $array['role'] ?? null;
        $session = Session::fromArray($array['session']) ?? null;

        return new AuthenticationResult($identifier, $role, $session);
    }

}