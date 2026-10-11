<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl;
    protected array $httpOptions;

    public function __construct()
    {
        $this->secretKey = config('paystack.secret_key') ?? '';
        $this->baseUrl   = config('paystack.payment_url', 'https://api.paystack.co');

        // 🔑 The fix — force Guzzle to use this CA bundle
        $caBundle = config('paystack.ca_bundle', 'C:\wamp64\bin\php\php8.5.0\extras\ssl\cacert.pem');

        $this->httpOptions = [
            'verify'  => file_exists($caBundle) ? $caBundle : true,
            'timeout' => 30,
        ];
    }

    public function initialize(User $user, float $amount, array $metadata = []): array
    {
        if (empty($this->secretKey)) {
            throw new \Exception('Paystack secret key not configured.');
        }

        $reference = 'PSK-' . strtoupper(Str::random(12));

        $response = Http::withToken($this->secretKey)
            ->withOptions($this->httpOptions)   // ← SSL fix applied
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email'        => $user->email,
                'amount'       => (int) round($amount * 100),   // Naira → kobo
                'reference'    => $reference,
                'callback_url' => config('paystack.callback_url'),
                'metadata'     => array_merge($metadata, [
                    'user_id' => $user->id,
                    'name'    => $user->full_name,
                ]),
            ]);

        if (!$response->successful()) {
            Log::error('Paystack init failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('Paystack initialization failed: ' . $response->body());
        }

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new \Exception('Paystack error: ' . ($body['message'] ?? 'Unknown error'));
        }

        return [
            'authorization_url' => $body['data']['authorization_url'],
            'access_code'       => $body['data']['access_code'],
            'reference'         => $body['data']['reference'],
        ];
    }

    public function verify(string $reference): array
    {
        if (empty($this->secretKey)) {
            throw new \Exception('Paystack secret key not configured.');
        }

        $response = Http::withToken($this->secretKey)
            ->withOptions($this->httpOptions)   // ← SSL fix applied
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        if (!$response->successful()) {
            throw new \Exception('Paystack verify failed: ' . $response->body());
        }

        return $response->json();
    }
}