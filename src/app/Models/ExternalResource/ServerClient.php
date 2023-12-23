<?php
namespace App\Models\ExternalResource;

use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Log;

class ServerClient
{
    protected $client;

    protected $timeout = 10;

    protected $msg = '';

    protected $status_code = 0;

    public function __construct($url = null, $timeout = null)
    {
        $param = array();
        
        $param['verify'] = false;
        if($url) {
            $param['base_uri'] = $url;
        }
        if($timeout === null) {
            $param['timeout'] = $this->timeout;
        } else if($timeout !== 0) {
            $param['timeout'] = $timeout;
        }
        $this->client = new \GuzzleHttp\Client($param);
    }

    protected function formatQuery($url, $data)
    {
        $query = http_build_query($data);
        $url .= '?'.$query;
        return $url;
    }

    public function getJsonDataByBody($url = '', $data = array(), $query_data = array(), $header = [], $method = 'GET')
    {
        $header = array_merge($header, ['Accept'=>'application/json']);
        $header = array_merge($header, ['Content-Type'=>'application/json']);
        $requestData = ['headers' => $header];
        $requestData['body'] = $data;
        if (!empty($query_data)) {
            $url = $this->formatQuery($url, $query_data);
        }
        try {
            $response = $this->client->request($method, $url, $requestData);
            $status_code = $response->getStatusCode();
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            $this->msg = $e->getMessage();
            $status_code = 500;
        }
        if ($status_code !== 200) {
            return false;
        } else {
            $body = $response->getBody();
            return json_decode($body->getContents());
        }

    }

    public function getJsonData($url = '', $data = array(), $header = [], $method = 'GET')
    {
        $header = array_merge($header, ['Accept'=>'application/json']);
        $requestData = ['headers' => $header];
        if (!empty($data)) {
            switch ($method) {
                case 'POST':
                    $requestData['form_params'] = $data;
                    break;
                default:
                    $url = $this->formatQuery($url, $data);
                    break;
            }
        }

        try {
            $response = $this->client->request($method, $url, $requestData);
            $status_code = $response->getStatusCode();
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            $status_code = 500;
        }
        if ($status_code !== 200) {
            return false;
        } else {
            $body = $response->getBody();
            return json_decode($body->getContents());
        }

    }

    public function request($url, $headers = ['Accept'=>'application/json'], $data = array(), $method = 'get')
    {
        $method = 'request'.ucfirst(strtolower($method));
        return $this->{$method}($url, $headers, $data);
    }

    public function download($remote, $local, $method = 'get')
    {
        try {
            $response = $this->client->request($method, $remote, ['sink' => $local]);
            $status_code = $response->getStatusCode();
        } catch(\Exception $e) {
            $status_code = $e->getCode();
            $this->msg = $e->getMessage();
        }

        $this->status_code = $status_code;
        if ($status_code != 200) {
            return false;
        } else {
            return file_exists($local);
        }
    }

    protected function requestGet($url, $headers, $data)
    {
        if (!empty($data))
        {
            $query = http_build_query($data);
            $url.='?'.$query;
        }
        try
        {
            $response = $this->client->get($url, $headers);
            $status_code = $response->getStatusCode();
        }
        catch(\Exception $e)
        {
            $status_code = 404;
        }
        if ($status_code != 200)
        {
            return false;
        }
        else
        {
            $body = $response->getBody();
            return $body->getContents();
        }
    }

    public function getMessage()
    {
        return $this->msg;
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    protected function requestPost($url, $headers, $data)
    {
        try
        {
            // $response = $this->client->post($url, $headers, $data);
            $response = $this->client->request('POST', $url, $headers, $data);
            $status_code = $response->getStatusCode();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();exit();
        }
        if ($status_code != 200)
        {
            return false;
        }
        else
        {
            return $response->json();
        }
    }

}