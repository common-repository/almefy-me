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

class Configuration
{

    private ?string $websiteUrl;

    private ?string $authenticationUrl;

    private ?string $registrationUrl;

    private bool $sessionSupport;

    public function __construct(?string $websiteUrl, ?string $authenticationUrl, ?string $registrationUrl, bool $sessionSupport)
    {
        $this->websiteUrl = $websiteUrl;
        $this->authenticationUrl = $authenticationUrl;
        $this->registrationUrl = $registrationUrl;
        $this->sessionSupport = $sessionSupport;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function getAuthenticationUrl(): ?string
    {
        return $this->authenticationUrl;
    }

    public function getRegistrationUrl(): ?string
    {
        return $this->registrationUrl;
    }

    public function hasSessionSupport(): bool
    {
        return $this->sessionSupport;
    }

    public static function fromArray(array $array = []): Configuration
    {
        $websiteUrl        = $array['websiteUrl'] ?? null;
        $authenticationUrl = $array['authenticationUrl'] ?? null;
        $registrationUrl   = $array['registrationUrl'] ?? null;
        $sessionSupport    = $array['supportSessions'] === true;

        return new Configuration($websiteUrl, $authenticationUrl, $registrationUrl, $sessionSupport);
    }

}