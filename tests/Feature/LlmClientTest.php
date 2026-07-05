<?php

use App\Agent\LlmClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

function llmOk(string $content): array
{
    return ['choices' => [['message' => ['role' => 'assistant', 'content' => $content]]]];
}

function makeClient(array $retry = []): LlmClient
{
    return new LlmClient(
        [
            'deepseek' => ['base_url' => 'https://deepseek.test/v1', 'model' => 'm', 'api_key' => 'k'],
            'openai' => ['base_url' => 'https://openai.test/v1', 'model' => 'm', 'api_key' => 'k'],
        ],
        ['deepseek', 'openai'],
        null,
        $retry,
    );
}

beforeEach(function () {
    Sleep::fake();
});

it('retries a provider on server errors before failing over', function () {
    Http::fake([
        'deepseek.test/*' => Http::sequence()->push('boom', 500)->push('boom', 500),
        'openai.test/*' => Http::response(llmOk('rescued')),
    ]);

    $client = makeClient(['attempts_per_provider' => 2, 'backoff_seconds' => [1], 'rounds' => 1]);

    $message = $client->chat([['role' => 'user', 'content' => 'hi']], []);

    expect($message['content'])->toBe('rescued')
        ->and($client->activeProvider())->toBe('openai');

    Http::assertSentCount(3);
});

it('does not waste retries on a client error like HTTP 400', function () {
    Http::fake([
        'deepseek.test/*' => Http::response('bad request', 400),
        'openai.test/*' => Http::response(llmOk('rescued')),
    ]);

    $client = makeClient(['attempts_per_provider' => 3, 'backoff_seconds' => [1], 'rounds' => 1]);

    $message = $client->chat([['role' => 'user', 'content' => 'hi']], []);

    expect($message['content'])->toBe('rescued');

    // One deepseek attempt (400 is not retryable), then straight to openai.
    Http::assertSentCount(2);
});

it('sweeps all providers again in a later round instead of dying', function () {
    Http::fake([
        'deepseek.test/*' => Http::sequence()->push('down', 503)->push(llmOk('recovered')),
        'openai.test/*' => Http::response('down too', 503),
    ]);

    $client = makeClient(['attempts_per_provider' => 1, 'backoff_seconds' => [1], 'rounds' => 2, 'round_backoff_seconds' => 30]);

    $message = $client->chat([['role' => 'user', 'content' => 'hi']], []);

    expect($message['content'])->toBe('recovered');

    // Round 1: deepseek 503, openai 503. Round 2: deepseek recovers.
    Http::assertSentCount(3);
    Sleep::assertSleptTimes(1);
});

it('gives up with a clear error after all rounds fail', function () {
    Http::fake([
        'deepseek.test/*' => Http::response('down', 503),
        'openai.test/*' => Http::response('down', 503),
    ]);

    $client = makeClient(['attempts_per_provider' => 1, 'backoff_seconds' => [1], 'rounds' => 2, 'round_backoff_seconds' => 1]);

    $client->chat([['role' => 'user', 'content' => 'hi']], []);
})->throws(RuntimeException::class, 'All LLM providers failed after retries.');

it('reports a permanently rejected run when no provider has a key', function () {
    $client = new LlmClient(
        ['deepseek' => ['base_url' => 'https://deepseek.test/v1', 'model' => 'm', 'api_key' => null]],
        ['deepseek'],
    );

    $client->chat([['role' => 'user', 'content' => 'hi']], []);
})->throws(RuntimeException::class, 'No LLM provider has an API key configured.');
