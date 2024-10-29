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

class AuthenticationChallenge
{

    private ?string $challenge;

    private ?string $identifier;

    private ?string $otp;

    /**
     * AuthenticationToken constructor.
     */
    public function __construct(?string $challenge, ?string $identifier, ?string $otp)
    {
        $this->challenge = $challenge;
        $this->identifier = $identifier;
        $this->otp = $otp;
    }

    public function getChallenge(): ?string
    {
        return $this->challenge;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getOtp(): ?string
    {
        return $this->otp;
    }

    public static function fromArray(array $array = []): AuthenticationChallenge
    {
        $challenge = $array['challenge'] ?? null;
        $identifier = $array['identifier'] ?? null;
        $otp = $array['otp'] ?? null;

        return new AuthenticationChallenge($challenge, $identifier, $otp);
    }

}