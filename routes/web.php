<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/proxy', function () {
    if (! request()->has('payload')) {
        return json_encode(['ok' => false, 'found' => false, 'message' => 'no payload']);
    }

    $payload = json_decode(request()->payload, true);

    if (! isset($payload['bearer'])) {
        if ((! isset($payload['api_token']) || ! isset($payload['api_app']))) {
            return json_encode(['ok' => false, 'found' => false, 'message' => 'no auth']);
        }
    }

    if (! isset($payload['endpoint']) || ! isset($payload['data'])) {
        return json_encode(['ok' => false, 'found' => false, 'message' => 'no body']);
    }

    $client = isset($payload['bearer']) ?
                Http::withToken($payload['bearer']) :
                Http::withHeaders(['app' => $payload['api_app'], 'token' => $payload['api_token']]);
    $response = $client->withOptions(['verify' => false])->post($payload['endpoint'], $payload['data']);
    if (! $response->ok()) {
        return json_encode(['ok' => false, 'found' => false, 'message' => 'endpoint error']);
    }

    return $response->body();
});
