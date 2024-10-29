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

use Exception;
use Almefy\Exception\NetworkException;
use Almefy\Exception\ServerException;
use Almefy\Exception\TransportException;
use InvalidArgumentException;
use RuntimeException;

class Client
{
    const VERSION = '0.9.7';

    const GET_REQUEST = 'GET';
    const POST_REQUEST = 'POST';
    const PUT_REQUEST = 'PUT';
    const PATCH_REQUEST = 'PATCH';
    const DELETE_REQUEST = 'DELETE';

    const REQUEST_TIMESTAMP_LEEWAY = 10;

    const JSON_DEFAULT_DEPTH    = 512;
    const BASE64_PADDING_LENGTH = 4;

    const ONE_STEP_ENROLLMENT = 'ONE_STEP_ENROLLMENT';
    const TWO_STEP_ENROLLMENT = 'TWO_STEP_ENROLLMENT';

    private string $api;

    private string $key;

    private string $secret;

    private $handle = null;

    /**
     * Client constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $key, string $secret, $api = 'https://api.almefy.com')
    {
        if (empty($key) || empty($secret)) {
            throw new InvalidArgumentException('Invalid "key" or "secret" while initiating Almefy client');
        }

        $this->key = $key;
        $this->secret = $secret;
        $this->api = $api;
    }

    public function getApi(): string
    {
        return $this->api;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function check(): void
    {
        $this->doRequest(self::POST_REQUEST, sprintf('%s/v1/entity/check', $this->api), [
            'message' => 'ping'
        ]);
    }

    public function getConfiguration(): Configuration
    {
        $result = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/configuration', $this->api));

        return Configuration::fromArray($result ?? []);
    }

    public function setConfiguration(array $settings): Configuration
    {
        $result = $this->doRequest(self::PATCH_REQUEST, sprintf('%s/v1/entity/configuration', $this->api), $settings);

        return Configuration::fromArray($result ?? []);
    }

    /**
     * @return Identity[]
     */
    public function getIdentities(): array
    {
        $response = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/identities', $this->api));

        $identities = [];
        foreach ($response as $item) {
            $identities[] = Identity::fromArray($item);
        }

        return $identities;
    }

    public function getIdentity(string $identifier): Identity
    {
        $response = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/identities/%s', $this->api, urlencode($identifier)));

        return Identity::fromArray($response ?? []);
    }

    public function getSession(string $sessionsId): Session
    {
        $response = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/sessions/%s', $this->api, $sessionsId));

        return Session::fromArray($response ?? []);
    }

    public function getSessions(array $sessionsToUpdate = []): array
    {
        if (count($sessionsToUpdate) > 0) {
            return $this->updateSessions($sessionsToUpdate ?? []);
        }

        $response = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/sessions', $this->api));

        return Session::fromSessionArray($response['items'] ?? []);
    }

    public function updateSessions(array $sessions = []): array
    {
        $data = array_map(static fn (Session $session) => [
            'id' => $session->getId(),
            'clientSessionUpdatedAt' => $session->getUpdatedAt(),
            'clientSessionExpiresAt' => $session->getExpiresAt(),
        ], $sessions);

        $response = $this->doRequest(self::PATCH_REQUEST, sprintf('%s/v1/entity/sessions', $this->api), $data);

        return Session::fromSessionArray($response['items'] ?? []);
    }

    public function logoutSession(string $sessionId): void
    {
        $this->doRequest(self::DELETE_REQUEST, sprintf('%s/v1/entity/sessions/%s', $this->api, $sessionId));
    }

    public function enrollIdentity(string $identifier, array $options = []): EnrollmentToken
    {
        $defaults = [
            'enrollmentType'  => Client::ONE_STEP_ENROLLMENT,
            'nickname'        => null,
            'sendEmail'       => false,
            'sendEmailTo'     => '',
            'sendEmailLocale' => 'en_US',
            'role'            => 'ROLE_USER',
            'timeout'         => 3600
        ];

        $data = array_intersect_key($options, $defaults) + $defaults;
        $data['identifier'] = $identifier; // This cannot be changed by options

        $response = $this->doRequest(self::POST_REQUEST, sprintf('%s/v1/entity/identities/enroll', $this->api), $data);

        return EnrollmentToken::fromArray($response);
    }

    /**
     * @deprecated Use enrollIdentity() instead
     */
    public function provisionIdentity(string $identifier, array $options = []): EnrollmentToken
    {
        return $this->enrollIdentity($identifier, $options);
    }

    public function renameIdentity(string $oldIdentifier, string $newIdentifier): void
    {
        $this->doRequest(self::PATCH_REQUEST, sprintf('%s/v1/entity/identities/%s/rename', $this->api, urlencode($oldIdentifier)), [
            'identifier' => $newIdentifier
        ]);
    }

    public function deleteIdentity(string $identifier): void
    {
        $this->doRequest(self::DELETE_REQUEST, sprintf('%s/v1/entity/identities/%s', $this->api, urlencode($identifier)));
    }

    public function deleteToken(string $id): void
    {
        $this->doRequest(self::DELETE_REQUEST, sprintf('%s/v1/entity/tokens/%s', $this->api, $id));
    }

    /**
     * @return bool|AuthenticationResult
     */
    public function authenticate(AuthenticationChallenge $token)
    {
        try {
            $response = $this->doRequest(self::POST_REQUEST, sprintf('%s/v1/entity/identities/%s/authenticate', $this->api, urlencode($token->getIdentifier())), [
                'challenge' => $token->getChallenge(),
                'otp' => $token->getOtp()
            ]);

            if (!empty($response)) {
                return AuthenticationResult::fromArray($response);
            }

            return true;

        } catch (TransportException $e) {
        }

        return false;
    }

    /**
     * @throws NetworkException|ServerException
     */
    protected function doRequest(string $method, string $url, array $data = null)
    {
        if (is_resource($this->handle)) {
            curl_reset($this->handle);
        } else {
            $this->handle = curl_init();
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->handle, CURLOPT_HEADER, false);
        curl_setopt($this->handle, CURLOPT_VERBOSE, false);
        curl_setopt($this->handle, CURLOPT_FAILONERROR, false);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);

        $body = empty($data) ? '' : $this->jsonEncode($data);

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer '.$this->createApiToken($method, $url, $body),
            'User-Agent: Almefy PHP Client '.self::VERSION.' (PHP '.phpversion().')',
            'X-Client-Version: '.self::VERSION
        ];

        if (in_array($method, [self::POST_REQUEST, self::PUT_REQUEST, self::PATCH_REQUEST])) {

            $headers[] = 'Content-Type: application/json; charset=utf-8';
            $headers[] = 'Content-Length: '.mb_strlen($body);

            if ($method === self::POST_REQUEST) {
                curl_setopt($this->handle, CURLOPT_POST, true);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $body);
            } else {
                curl_setopt($this->handle, CURLOPT_POST, false);
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $body);
            }

        } else if ($method === self::DELETE_REQUEST) {
            curl_setopt($this->handle, CURLOPT_POST, false);
            curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, self::DELETE_REQUEST);
        } else {
            curl_setopt($this->handle, CURLOPT_POST, false);
            curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, self::GET_REQUEST);
        }

        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        $content = curl_exec($this->handle);

        switch (curl_errno($this->handle)) {
            case CURLE_OK:
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_CONNECT:
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_SSL_CONNECT_ERROR:
                throw new NetworkException(curl_error($this->handle), curl_errno($this->handle));
        }

        $statusCode = curl_getinfo($this->handle, CURLINFO_RESPONSE_CODE);
        $content = $this->jsonDecode($content);

        if ($statusCode >= 400) {
            throw new ServerException($content, $statusCode);
        }

        if ($statusCode !== 204 && !is_array($content)) {
            throw new ServerException($content, $statusCode);
        }

        curl_close($this->handle);

        return $content;
    }

    private function createApiToken(string $method, string $url, string $body = ''): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $claims = [
            'iss' => $this->key,
            'aud' => $this->api,
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 10,
            'method' => $method,
            'url' => $url,
            'bodyHash' => hash('sha256', $body)
        ];

        $payload = [
            $this->base64UrlEncode($this->jsonEncode($header)),
            $this->base64UrlEncode($this->jsonEncode($claims)),
        ];

        $payload[] = $this->base64UrlEncode(hash_hmac('sha256', implode('.', $payload), $this->base64UrlDecode($this->secret), true));

        return implode('.', $payload);
    }

    public function createJwt($claims = []): void
    {
    }

    public function decodeJwt(string $jwt): AuthenticationChallenge
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new RuntimeException('JWT has wrong number of segments.');
        }

        list($head64, $body64, $signature64) = $tks;

        if (null === ($header = $this->jsonDecode($this->base64UrlDecode($head64)))) {
            throw new RuntimeException('JWT has invalid header encoding.');
        }
        if (null === ($body = $this->jsonDecode($this->base64UrlDecode($body64)))) {
            throw new RuntimeException('JWT has invalid body encoding.');
        }
        if (null === ($signature = $this->base64UrlDecode($signature64))) {
            throw new RuntimeException('JWT has invalid signature encoding.');
        }

        if (hash_hmac('sha256', $head64.'.'.$body64, $this->base64UrlDecode($this->secret), true) !== $signature) {
            throw new RuntimeException('JWT has invalid signature.');
        }

        $timestamp = time();
        if ($body['iat'] - self::REQUEST_TIMESTAMP_LEEWAY > $timestamp || $timestamp > $body['iat'] + self::REQUEST_TIMESTAMP_LEEWAY) {
            throw new RuntimeException('JWT credentials have expired.');
        }

        return new AuthenticationChallenge($body['jti'], $body['sub'], $body['otp']);
    }

    /**
     * @return bool|string|null
     */
    private function jsonEncode($data)
    {
        if (empty($data))
            return null;

        try {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Exception $exception) {
            throw new RuntimeException('Error while encoding to JSON.', 0, $exception);
        }
    }

    private function jsonDecode($json)
    {
        if (empty($json))
            return array();

        try {
            return json_decode($json, true, self::JSON_DEFAULT_DEPTH);
        } catch (Exception $exception) {
            throw new RuntimeException('Error while decoding to JSON', 0, $exception);
        }
    }

    /**
     * @return array|string
     */
    private function base64UrlEncode($data)
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    private function base64UrlDecode($data): string
    {
        $remainder = strlen($data) % self::BASE64_PADDING_LENGTH;

        if ($remainder !== 0) {
            $data .= str_repeat('=', self::BASE64_PADDING_LENGTH - $remainder);
        }

        $decodedContent = base64_decode(strtr($data, '-_', '+/'), true);

        if (!is_string($decodedContent)) {
            throw new RuntimeException('Error while decoding from Base64: invalid characters used');
        }

        return $decodedContent;
    }
}
