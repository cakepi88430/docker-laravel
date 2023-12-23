<?php

namespace App\Http\Controllers\Api\Line;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotifyController extends Controller
{
    protected $notify_api;

    public function __construct() {
        $this->notify_api = app()->make(\App\Models\ExternalResource\Line\NotifyApi::class);
    }

    public function sendMessage(Request $request) {
        $data = $request->all();
        $this->validator($data)->validate();

        $message = $request->input('message');
        $res = $this->notify_api->sendMessage($message);

        if($res !== false) {
            return response()->json($res, 200);
        } else {
            return response()->json(array('message' => 'unknown error'), 200);
        }
    }

    /**
     * Get a validator for an creating role request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'message' => 'required|string|max:100',
        ]);
    }

}