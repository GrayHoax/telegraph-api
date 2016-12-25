<?php

namespace Deploykit\Telegraph;

use Deploykit\Telegraph\Entities\Page;
use Deploykit\Telegraph\Entities\Account;
use Symfony\Component\DomCrawler\Crawler;
use Deploykit\Telegraph\Entities\PageList;
use Deploykit\Telegraph\Entities\PageViews;

class Client
{
    protected $url = 'https://api.telegra.ph/';

    protected $http;

    public function __construct($httpClient = null)
    {
        $this->http = $httpClient ?: new \GuzzleHttp\Client(['base_uri' => $this->url]);
    }

    public function createAccount($shortName, $authorName = '', $authorUrl = '')
    {
        $response = $this->http->post('/createAccount', [
            'json' => [
                'short_name' => $shortName,
                'author_name' => $authorName,
                'author_url' => $authorUrl
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new Account($response['result']);
    }

    public function editAccountInfo($account, $shortName = '', $authorName = '', $authorUrl = '')
    {
        $response = $this->http->post('/editAccountInfo', [
            'json' => [
                'access_token' => $this->getAccessToken($account),
                'short_name' => $shortName,
                'author_name' => $authorName,
                'author_url' => $authorUrl
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new Account($response['result']);
    }

    public function getAccountInfo($account, $fields = ['short_name', 'author_name', 'author_url'])
    {
        $availableFields = ['short_name', 'author_name', 'author_url', 'auth_url', 'page_count'];

        foreach ($fields as $field) {
            if (!in_array($field, $availableFields)) {
                throw new \Exception();
            }
        }

        $response = $this->http->post('/getAccountInfo', [
            'json' => [
                'access_token' => $this->getAccessToken($account),
                'fields' => $fields
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new Account($response['result']);
    }

    public function revokeAccessToken($account)
    {
        $response = $this->http->post('/revokeAccessToken', [
            'json' => [
                'access_token' => $this->getAccessToken($account)
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new Account($response['result']);
    }

    public function createPage($account, $title, $content, $authorName = '', $authorUrl = '', $returnContent = false)
    {
        $accessToken = $this->getAccessToken($account);
    }

    public function editPage()
    {
    }

    public function getPage($path, $returnContent = false)
    {
        $response = $this->http->post('/getPage', [
            'json' => [
                'path' => $path,
                'return_content' => $returnContent
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new Page($response['result']);
    }

    public function getPageList($account, $offset = 0, $limit = 50)
    {
        $response = $this->http->post('/getPageList', [
            'json' => [
                'access_token' => $this->getAccessToken($account),
                'offset' => $offset,
                'limit' => $limit
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new PageList($response['result']);
    }

    public function getViews($path, $year = null, $month = null, $day = null, $hour = null)
    {
        $json = array_filter(compact('path', 'year', 'month', 'day', 'hour'));

        $response = $this->http->post('/getViews', [
            'json' => $json
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        return new PageViews($response['result']);
    }

    /**
     * @param  \Deploykit\Telegraph\Entities\Account|string  $account
     * @return  mixed
     */
    protected function getAccessToken($account)
    {
        if ($account instanceof Account) {
            return $account['access_token'];
        }

        return $account;
    }
}
