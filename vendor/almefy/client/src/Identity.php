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

class Identity
{

    private ?string $id;

    private ?string $createdAt;

    private bool $locked;

    private ?string $identifier;

    private ?string $nickname;

    private ?string $label;

    private string $role;

    /**
     * @var Token[]
     */
    private array $tokens;

    /**
     * @var Session[]
     */
    private array $sessions;

    /**
     * Identity constructor.
     */
    public function __construct(?string $id, ?string $createdAt, bool $locked, ?string $identifier, ?string $nickname, ?string $label, ?string $role, array $tokens = [], array $sessions = [])
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->locked = $locked;
        $this->identifier = $identifier;
        $this->nickname = $nickname;
        $this->label = $label;
        $this->role = $role;
        $this->tokens = $tokens;
        $this->sessions = $sessions;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    // For BC compatibility, getLabel() should be used
    public function getName(): ?string
    {
        return $this->label;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return Session[]
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }

    public static function fromArray(array $array = []): Identity
    {
        $id = $array['id'] ?? null;
        $createdAt = $array['createdAt'] ?? null;
        $locked = $array['locked'] ?? false;
        $identifier = $array['identifier'] ?? null;
        $nickname = $array['nickname'] ?? null;
        $label = $array['label'] ?? null;
        $role = $array['role'] ?? null;

        $tokens = [];
        foreach ($array['tokens'] as $item) {
            $tokens[] = Token::fromArray($item);
        }

        $sessions = [];
        foreach ($array['sessions'] as $item) {
            $sessions[] = Session::fromArray($item);
        }

        return new Identity($id, $createdAt, $locked, $identifier, $nickname, $label, $role, $tokens, $sessions);
    }

}