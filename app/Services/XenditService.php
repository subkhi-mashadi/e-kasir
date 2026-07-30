<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class XenditService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.xendit.co';

    public function __construct(?Company $company = null)
    {
        $this->secretKey = ($company?->xendit_secret_key)
            ?: config('services.xendit.secret_key', '');
    }

    public function chargeQris(string $referenceId, int $amount): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->withHeaders(['api-version' => '2022-07-31'])
            ->post("{$this->baseUrl}/qr_codes", [
                'reference_id' => $referenceId,
                'type'         => 'DYNAMIC',
                'currency'     => 'IDR',
                'amount'       => $amount,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Xendit API error ' . $response->status() . ': ' . $response->body());
        }

        $data      = $response->json();
        $qrString  = $data['qr_string'] ?? null;
        $qrDataUrl = null;

        if ($qrString) {
            $png       = QrCode::format('png')->size(300)->margin(1)->generate($qrString);
            $qrDataUrl = 'data:image/png;base64,' . base64_encode($png);
        }

        return [
            'reference_id' => $data['reference_id'],
            'xendit_id'    => $data['id'],
            'qr_string'    => $qrString,
            'qr_url'       => $qrDataUrl,
            'status'       => $data['status'] ?? 'ACTIVE',
        ];
    }

    public function getStatus(string $referenceId): ?array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/qr_codes/{$referenceId}/payments");

        if (! $response->successful()) {
            return null;
        }

        $payments = $response->json('data', []);
        $latest   = collect($payments)->sortByDesc('created')->first();

        return [
            'status' => $latest['status'] ?? null,
            'amount' => $latest['amount'] ?? null,
        ];
    }

    public function isTestMode(): bool
    {
        return str_starts_with($this->secretKey, 'xnd_development_');
    }

    public function simulatePayment(string $xenditQrId, int $amount): bool
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->withHeaders(['api-version' => '2022-07-31'])
            ->post("{$this->baseUrl}/qr_codes/{$xenditQrId}/payments/simulate", [
                'amount' => $amount,
            ]);

        return $response->successful();
    }

    public function verifyWebhookToken(string $token): bool
    {
        return hash_equals(config('xendit.webhook_token', ''), $token);
    }
}
