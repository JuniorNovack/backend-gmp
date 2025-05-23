<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use PDOException;
use Psr\Log\LoggerInterface;
use Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
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
        $this->reportable(
            function (Throwable $e) {
                try {
                    $logger = $this->container->make(LoggerInterface::class);
                } catch (\Exception $e) {
                    throw $e;
                }

                $response = $this->render(app()->request, $e);

                $logger->error(
                    $e->getMessage(),
                    array_merge(
                        $this->exceptionContext($e),
                        $this->context(),
                        ['exception' => $e, 'response' => $response]
                    )
                );

                return false;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function render($request, Throwable $e)
    {
        if (empty($e)) {
            return parent::render($request, $e);
        }

        if ($e instanceof BaseException) {
            return $e->render($request);
        }

        $status = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        $extra = $this->convertExceptionToArray($e);

        if ($e instanceof AuthenticationException) {
            return Response::error(
                'Authentication failed',
                HttpResponse::HTTP_UNAUTHORIZED,
                $extra
            );
        }

        if ($e instanceof PDOException) {
            return Response::error(
                'Internal database error',
                HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                $extra
            );
        }

        if ($e instanceof MethodNotAllowedException) {
            return Response::error(
                'The specified method for the request is invalid',
                HttpResponse::HTTP_METHOD_NOT_ALLOWED,
                $extra
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return Response::error(
                'The specified URL cannot be found',
                HttpResponse::HTTP_NOT_FOUND,
                $extra
            );
        }

        if ($e instanceof ValidationException) {
            $reason = "";
            $errors = $e->errors();
            array_walk_recursive(
                $errors,
                function ($message) use (&$reason) {
                    $reason .= (strlen($reason)) ? " | " : $reason;
                    $reason .= $message;

                    return $reason;
                }
            );

            return Response::error($reason, HttpResponse::HTTP_BAD_REQUEST, $extra);
        }

        if ($this->isHttpException($e)) {
            return Response::error('Request Error', $status, $extra);
        }

        return Response::error('Technical error. Try later', $status, $extra);
    }
}
