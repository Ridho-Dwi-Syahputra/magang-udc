<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Sesi login Anda tidak valid atau telah habis. Silakan login kembali.',
                    'data' => null
                ], 401));
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Paksa JSON response untuk semua rute API
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $pesan = $e->getPrevious() instanceof ModelNotFoundException
                    ? 'Data yang dicari tidak ditemukan.'
                    : 'Endpoint tidak ditemukan.';

                return response()->json([
                    'success' => false,
                    'message' => $pesan,
                    'data' => null
                ], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang diberikan tidak valid.',
                    'data' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi login Anda tidak valid atau telah habis. Silakan login kembali.',
                    'data' => null
                ], 401);
            }
        });
    })->create();
