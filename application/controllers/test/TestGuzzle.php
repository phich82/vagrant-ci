<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TestGuzzle extends CI_Controller
{
    public function test()
    {
        $client = new GuzzleHttp\Client(['verify' => true]);
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts/1');
        var_dump($response->getStatusCode(), json_decode($response->getBody(), true));
    }
}
