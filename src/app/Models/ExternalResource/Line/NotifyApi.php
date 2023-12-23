<?php
namespace App\Models\ExternalResource\Line;

use \App\Models\ExternalResource\ServerClient;

class NotifyApi
{
    protected $client;
    const baseUrl = 'https://notify-api.line.me/api/notify';

    protected $token;
    
    protected $header;

    protected $query_data;
    
    public function __construct()
    {
        $this->client = new ServerClient(null, 20);
        $this->token = env('LINE_NOTIFY_TOKEN', '');
        $this->header = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function sendMessage($message) {
        $query_data = array(
            'message' => $message
        );
        $result = $this->client->getJsonData(self::baseUrl, $query_data, $this->header, 'POST');

        return $result;
    }
}