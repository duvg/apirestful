<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        // Handle validations exceptions
        if ($exception instanceof ValidationException) 
        {
            $this->convertValidationExceptionToResponse($exception, $request);
        }

        // Handle model not found exception
        if ($exception instanceof ModelNotFoundException) 
        {
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("No existe ninguna instancia de $model con el id especificado", 404);
        }

        // Handle authentication exceptions
        if ($exception instanceof AuthenticationException) 
        {
            return $this->unauthenticated($request, $exception);
        }

        // Handle authorization exceptions
        if ($exception instanceof AuthorizationException) 
        {
            return $this->errorResponse('No tienes permisos para acceder a este recurso', 403);
        }

        // Handle not found http exception
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('No se encontro la URL especificada', 404);
        }

        // Handle method not allowed
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('El metodo especificado en la petición no es valido', 405);
        }

        // Handle any exception hhttp
        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        // Handle integrity relationship exception
        if ($exception instanceof QueryException) {
            $code = $exception->errorInfo[1];

            if ($code == 1451) {
                 return $this->errorResponse('No es posible eliminar de forma permanete el recurso porque esta relacionado con otro', 409);
            }

            return $this->errorResponse('Ups! algo salio mal, intenta más tarde', 500);
            
        }

        if ($exception instanceof TokenMismatchException) 
        {
            return redirect()->back()->withInput($request->input( ));
        }

        // Handle unexpected exceptions

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }
        return $this->errorResponse('Ups! algo salio mal, intenta más tarde', 500);
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
        if ($this->isFrontend($request)) 
        {
            return redirect()->guest('login');
        }

        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $exception, $request)
    {
        $errors = $exception->validator->errors()->getMessages();

        if ($this->isFrontend($request))
        {
            return $request->ajax() ? response()->json($errors, 422) : redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($errors);
        }
        return $this->errorResponse($errors, 200);
    }

    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
