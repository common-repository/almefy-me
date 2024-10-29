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

use Exception;
use Almefy\Exception\NetworkException;
use Almefy\Exception\ServerException;
use Almefy\Exception\TransportException;
use InvalidArgumentException;
use RuntimeException;

class Client
{
    const VERSION = '0.9.3';

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

    /**
     * @var string
     */
    private $api;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * cURL synchronous requests handle.
     *
     * @var resource|null
     */
    private $handle;

    /**
     * Client constructor.
     *
     * @param string $key
     * @param string $secret
     * @param string $api
     *
     * @throws InvalidArgumentException
     */
    public function __construct($key, $secret, $api = 'https://api.almefy.com')
    {
        if (empty($key) || empty($secret)) {
            throw new InvalidArgumentException('Invalid "key" or "secret" while initiating Almefy client');
        }

        $this->key = $key;
        $this->secret = $secret;
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function check()
    {
        $this->doRequest(self::POST_REQUEST, sprintf('%s/v1/entity/check', $this->api), [
            'message' => 'ping'
        ]);
    }

    /**
     * @return Identity[]
     */
    public function getIdentities()
    {
        $response = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/identities', $this->api));

        $identities = [];
        foreach ($response as $item) {
            $identities[] = Identity::fromArray($item);
        }

        return $identities;
    }

    /**
     * @param $identifier
     *
     * @return Identity
     */
    public function getIdentity($identifier)
    {
        $response = $this->doRequest(self::GET_REQUEST, sprintf('%s/v1/entity/identities/%s', $this->api, urlencode($identifier)));

        return Identity::fromArray($response);
    }

    /**
     * @param $identifier
     * @param array $options
     *
     * @return EnrollmentToken
     */
    public function enrollIdentity($identifier, $options = [])
    {
        $defaults = [
            'enrollmentType'  => Client::ONE_STEP_ENROLLMENT,
            'nickname'        => null,
            'sendEmail'       => false,
            'sendEmailTo'     => '',
            'sendEmailLocale' => 'en_US'
        ];

        $data = array_intersect_key($options, $defaults) + $defaults;
        $data['identifier'] = $identifier; // This cannot be changed by options

        $response = $this->doRequest(self::POST_REQUEST, sprintf('%s/v1/entity/identities/enroll', $this->api), $data);

        return EnrollmentToken::fromArray($response);
    }

    /**
     * @param $identifier
     * @param array $options
     *
     * @return EnrollmentToken
     *
     * @deprecated Use enrollIdentity() instead
     */
    public function provisionIdentity($identifier, $options = [])
    {
        return $this->enrollIdentity($identifier, $options);
    }

    /**
     * @param string $oldIdentifier
     * @param string $newIdentifier
     */
    public function renameIdentity($oldIdentifier, $newIdentifier)
    {
        $this->doRequest(self::PATCH_REQUEST, sprintf('%s/v1/entity/identities/%s/rename', $this->api, urlencode($oldIdentifier)), [
            'identifier' => $newIdentifier
        ]);
    }

    /**
     * @param string $identifier
     */
    public function deleteIdentity($identifier)
    {
        $this->doRequest(self::DELETE_REQUEST, sprintf('%s/v1/entity/identities/%s', $this->api, urlencode($identifier)));
    }

    /**
     * @param $id
     *
     * @return void
     */
    public function deleteToken($id)
    {
        $this->doRequest(self::DELETE_REQUEST, sprintf('%s/v1/entity/tokens/%s', $this->api, $id));
    }

    /**
     * @deprecated Client::authenticate should be used instead
     *
     * @param AuthenticationChallenge $token
     *
     * @return bool
     */
    public function verifyToken($token)
    {
        return $this->authenticate($token);
    }

    /**
     * @param AuthenticationChallenge $token
     *
     * @return bool
     */
    public function authenticate($token)
    {
        try {
            $this->doRequest(self::POST_REQUEST, sprintf('%s/v1/entity/identities/%s/authenticate', $this->api, urlencode($token->getIdentifier())), [
                'challenge' => $token->getChallenge(),
                'otp' => $token->getOtp()
            ]);

            return true;

        } catch (TransportException $e) {
        }

        return false;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     *
     * @return mixed
     *
     * @throws NetworkException|ServerException
     */
    protected function doRequest($method, $url, $data = null)
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
            'User-Agent: Almefy PHP Client '.self::VERSION
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

    private function createApiToken($method, $url, $body = '')
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

    public function createJwt($claims = [])
    {
    }

    /**
     * @param $jwt
     * @return AuthenticationChallenge
     *@throws RuntimeException
     */
    public function decodeJwt($jwt)
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

    private function base64UrlEncode($data)
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    private function base64UrlDecode($data)
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