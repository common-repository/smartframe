<?php

namespace SmartFrameLib\Api;
if (!defined('ABSPATH')) exit;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use SmartFrameLib\Config\Config;

/**
 * Class SmartFrameApi
 * @package SmartFrameLib\Api
 */
class SmartFrameApi
{

    /**
     * Url of api to connect
     * @var string
     */
    private $apiEndpoint;

    /**
     *  Header name to authenticate  Url of api to connect
     * @var string
     */
    private $auth;

    /**
     * @var Client
     */
    private $client;

    /**
     * SmartFrameApi constructor.
     * @param $apiEndpoint
     * @param $auth
     */
    public function __construct($apiEndpoint, $auth)
    {
        $this->client = new Client([
//            'cookies' => true,
//            'defaults' => [
//                'config' => [
//                    'curl' => [
//                        'body_as_string' => true,
//                    ],
//                ],
//            ],
        ]);
        $this->apiEndpoint = $apiEndpoint;
        $this->auth = $auth;
    }

    /**
     * Retrieve smartframe profile from Smartframe API url.
     *
     * @since     1.0.0
     * @access    public
     */
    public function getAccountInfo()
    {
        return $this->client->request('GET', $this->apiEndpoint . '/account', ['headers' => ['x-api-key' => $this->auth]]);
    }

    /**
     * @param $method
     * @param string $uri
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri = '', array $options = [])
    {
        return $this->client->request($method, $this->apiEndpoint . $uri, $options);
    }

    /**
     * @param $imageId
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getImage($imageId)
    {
        return $this->client->request('GET', $this->apiEndpoint . '/images/' . $imageId, [
            'headers' => [
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
        ]);
    }

    public function setExternalImageSource($externalSourceData)
    {
        $data = [
            'headers' => [
//                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
            'form_params' => $externalSourceData,
        ];

        return $this->client->request('POST', $this->apiEndpoint . '/account/set-ext-img-src', $data);
    }

    public function getSfmHead($sfmUrl)
    {
        return $this->client->request('HEAD', $sfmUrl);
    }

    /**
     * @param $imageModel
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postImage($imageModel)
    {
        return $this->client->request('POST', $this->apiEndpoint . '/images', [
            'headers' => [
                'x-api-key' => $this->auth,
            ],
            'multipart' => [
                [
                    'name' => 'id', 'contents' => $imageModel->name,
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($imageModel->image, 'rb'),
                ],
            ],
        ]);
    }

    /**
     * @param $imageModel
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateImage($imageModel)
    {
        $data = [
            'headers' => [
//                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
        ];

        if (isset ($imageModel->image)) {
            $multiPart['multipart'] = [
                ['name' => 'id', 'contents' => $imageModel->name],
                ['name' => 'jpegFile', 'contents' => fopen($imageModel->image, 'r')],
            ];

            $data = array_merge_recursive($data, $multiPart);
        }

        try {
            return $this->client->request('PUT', $this->apiEndpoint . '/images/' . $imageModel->name, $data);
        } catch (ClientException $e) {
            return null;
        }
    }

    public function updateImageMetadata($imageModel)
    {
        $data = [
            'headers' => [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
            'body' => json_encode([
                'metadata' => $imageModel->metaData,

            ]),
        ];

        try {
            return $this->client->request('PUT', $this->apiEndpoint . '/images/' . $imageModel->name, $data);
        } catch (Exception $e) {
        }
    }

    public function getImageMetadata($imageId)
    {
        $data = [
            'headers' => [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
        ];

        try {
            return $this->client->request('GET', $this->apiEndpoint . '/images/' . $imageId, $data);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param $imageId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteImage($imageId)
    {
        try {
            $response = $this->client->request('DELETE', $this->apiEndpoint . '/images/' . $imageId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'x-api-key' => $this->auth,
                ],
            ]);
            if ($response->getStatusCode() != 204) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAvailableListOfThemes()
    {
        return $this->client->request('GET', $this->apiEndpoint . '/themes', [
            'headers' => [
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],

            'multipart' => [
            ],
        ]);
    }

    public function connectNoRegisteredAccount($data)
    {
        return $this->client->request('POST', $this->apiEndpoint . '/account/connect-to-no-register?utm_campaign=WordPress%20Plugin%20v1.5.3%20-%20Guest%20user&utm_medium=referral&utm_source=WordPress Plugin - ' . $_SERVER['HTTP_HOST'] . '&utm_content=WordPress%20Plugin%20v1.5%20-Guest-registerd', [
            'headers' => [
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
            'form_params' => $data,
        ]);
    }

    public function postStatisticsData($statsData)
    {
        $data = [
            'headers' => [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->auth,
            ],
            'body' => json_encode($statsData),
        ];

        return $this->client->request('POST', Config::instance()->getConfig('api.statistics.endpoint') . '/statistics/wordpress', $data);
    }

}