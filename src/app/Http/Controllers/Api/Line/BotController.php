<?php

namespace App\Http\Controllers\Api\Line;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\LINEBot\Event\MessageEvent\UnknownMessageContent;
use LINE\LINEBot\KitchenSink\AccountLinkEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\BeaconEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\FollowEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\JoinEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\LeaveEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\AudioMessageHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\ImageMessageHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\LocationMessageHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\StickerMessageHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\TextMessageHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\VideoMessageHandler;
use LINE\LINEBot\KitchenSink\EventHandler\PostbackEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\ThingsEventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\UnfollowEventHandler;
use LINE\Constants\HTTPHeader;
use LINE\Parser\Event\UnknownEvent;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\MessageEvent;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use LINE\Webhook\Model\AccountLinkEvent;
use LINE\Webhook\Model\AudioMessageContent;
use LINE\Webhook\Model\BeaconEvent;
use LINE\Webhook\Model\FollowEvent;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Webhook\Model\JoinEvent;
use LINE\Webhook\Model\LeaveEvent;
use LINE\Webhook\Model\LocationMessageContent;
use LINE\Webhook\Model\PostbackEvent;
use LINE\Webhook\Model\StickerMessageContent;
use LINE\Webhook\Model\TextMessageContent;
use LINE\Webhook\Model\ThingsEvent;
use LINE\Webhook\Model\UnfollowEvent;
use LINE\Webhook\Model\VideoMessageContent;
use LINE\LINEBot;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;

class BotController extends Controller
{
    protected $messagingApi;

    public function __construct() {
        $client = new \GuzzleHttp\Client();
        $config = new \LINE\Clients\MessagingApi\Configuration();
        $token = env('LINE_CHANNEL_ACCESS_TOKEN', '');
        $config->setAccessToken($token);
        $this->messagingApi = new \LINE\Clients\MessagingApi\Api\MessagingApiApi($client, $config);
    }

    public function callback(Request $req){
        $channelToken = env('LINE_CHANNEL_ACCESS_TOKEN');
        $config = new \LINE\Clients\MessagingApi\Configuration();
        $config->setAccessToken($channelToken);
        $bot = new MessagingApiApi(new \GuzzleHttp\Client(), $config);
        $signature = $req->header(HTTPHeader::LINE_SIGNATURE);
        Log::info("signature:" . $signature);
        if (empty($signature)) {
            return abort(400, 'Bad Request');
        }
        $body = file_get_contents("php://input");
        $text = $req->getContent();
        $text = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
        Log::info("body:" . $text);
        // Check request with signature and parse request
        try {
            $secret = env('LINE_CHANNEL_SECRET', '');
            $parsedEvents = EventRequestParser::parseEventRequest($text, $secret, $signature);
        } catch (InvalidSignatureException $e) {
            Log::info($e->getMessage());
            return abort(400, 'Bad signature');
        } catch (InvalidEventRequestException $e) {
            Log::info($e->getMessage());
            return abort(400, "Invalid event request");
        }

        foreach ($parsedEvents->getEvents() as $event) {
            if (!($event instanceof MessageEvent)) {
                Log::info('Non message event has come');
                continue;
            }

            $message = $event->getMessage();
            if (!($message instanceof TextMessageContent)) {
                Log::info('Non text message has come');
                continue;
            }

            $replyText = $message->getText();
            Log::info($replyText);
            $bot->replyMessage(new ReplyMessageRequest([
                'replyToken' => $event->getReplyToken(),
                'messages' => [
                    (new TextMessage(['text' => $replyText]))->setType('text'),
                ],
            ]));
        }
        return response('ok');
    }


}