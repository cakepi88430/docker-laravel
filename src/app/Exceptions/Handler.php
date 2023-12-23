<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Throwable;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
        ExceptionBase::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        $info = $this->getRequestInfo($request);
        if ($info->isApi && !config('app.debug')) {
            return $this->handleApiResponse($request, $exception, $info->accept);
        }
        return parent::render($request, $exception);
    }

    protected function getRequestInfo($request)
    {
        $info = new \stdClass;
        $paths = $request->segments();
        $info->isApi = (isset($paths[0]) && $paths[0] === 'api') ? true : false;
        $info->accept = $this->wantsXml($request) ? 'xml' : 'json';
        return $info;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $info = $this->getRequestInfo($request);
        if ($info->isApi)
        {
            return $this->handleApiResponse($request, $exception, $info->accept);
        }

        return redirect()->guest(route('login'));
    }

    protected function handleApiResponse($request, Throwable $e, $ext = 'json')
    {
        // dd($e->validator->errors()->getMessages());
        switch(true) {
            case $e instanceOf ExceptionBase:
                $data = $e->toArray();
                $status = $e->getStatus();
                break;
            case $e instanceOf NotFoundHttpException:
                $data = array_merge([
                    'id' => 'url_not_found',
                    'status' => '404'
                ], config('errors.url_not_found'));
                $status = 404;
                break;
            case $e instanceOf ModelNotFoundException:
                $data = array_merge([
                    'id' => 'not_found',
                    'status' => '404'
                ], config('errors.not_found'));
                $status = 404;
                break;
            case $e instanceOf AuthenticationException:
                $data = array_merge([
                    'id' => 'unauthenticated',
                    'status' => '401'
                ], config('errors.unauthenticated'));
                $status = 401;
                break;
            case $e instanceOf AuthorizationException:
                $data = array_merge([
                    'id' => 'unauthorized',
                    'status' => '403'
                ], config('errors.unauthorized'));
                $status = 403;
                break;
            case $e instanceOf ValidationException:
                // dd('here');
                $data = array_merge(
                    [
                        'id' => 'invalidate',
                        'status' => '400',
                    ],
                    config('errors.invalidate'),
                    ['detail' => $e->validator->errors()->getMessages()]
                );
                $status = 400;
                break;
            default:
                $data = array_merge([
                    'id' => 'internal_error',
                    'status' => '500'
                ], config('errors.internal_error'));
                $status = 500;
                Log::error($e->getMessage());
                break;

        }

        if ($ext === 'xml') {
            return response()->xml(['error'=>$data], $status);
        } else {
            return response()->json(['error'=>$data], $status);
        }

    }

    protected function wantsXml($request)
    {
        $acceptable = $request->getAcceptableContentTypes();
        return isset($acceptable[0]) && Str::contains($acceptable[0], ['/xml', '+xml']);

    }
}
