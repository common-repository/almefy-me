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

class Identity
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
     * @var bool
     */
    private $locked;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $nickname;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Token[]
     */
    private $tokens;

    /**
     * Identity constructor.
     *
     * @param string $id
     * @param string $createdAt
     * @param bool $locked
     * @param string $identifier
     * @param string $nickname
     * @param string $name
     * @param Token[] $tokens
     */
    public function __construct($id, $createdAt, $locked, $identifier, $nickname, $name, array $tokens)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->locked = $locked;
        $this->identifier = $identifier;
        $this->nickname = $nickname;
        $this->name = $name;
        $this->tokens = $tokens;
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
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Token[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param $array
     *
     * @return Identity
     */
    public static function fromArray($array)
    {
        $id = $array['id'] ?: null;
        $createdAt = $array['createdAt'] ?: null;
        $locked = $array['locked'] ?: false;
        $identifier = $array['identifier'] ?: null;
        $nickname = $array['nickname'] ?: null;
        $name = $array['name'] ?: null;
        $tokens = [];

        foreach ($array['tokens'] as $item) {
            $tokens[] = Token::fromArray($item);
        }

        return new Identity($id, $createdAt, $locked, $identifier, $nickname, $name, $tokens);
    }

}