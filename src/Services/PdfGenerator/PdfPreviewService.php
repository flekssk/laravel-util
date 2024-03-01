<?php

declare(strict_types=1);

namespace FKS\Services\PdfGenerator;

use App\Models\User;
use App\Services\FKSApiService;
use Illuminate\Support\Facades\Cache;
use function auth;
use function session;

class PdfPreviewService
{
    /**
     * @param string $FKSUserId
     * @param string $gatewayXApiToken
     * @return void
     */
    public function auth(
        string $FKSUserId,
        string $gatewayXApiToken
    ): void {
        $awvUserId = User::query()
            ->where('user_id', $FKSUserId)
            ->value('id');

        auth()->onceUsingId($awvUserId);

        session([
            FKSApiService::SESSION_GATEWAY_TOKEN_PARAM_NAME => $gatewayXApiToken,
        ]);
    }

    /**
     * @param string $FKSUserId
     * @param string $xApiToken
     * @param string $gatewayXApiToken
     * @param string $cacheKey
     * @return array
     */
    public function getPdfConfigs(
        string $FKSUserId,
        string $xApiToken,
        string $gatewayXApiToken,
        ?string $cacheKey
    ): array {
        $generatePdfParams = null;
        if(!empty($cacheKey)){
            $generatePdfParams = Cache::get($cacheKey);
        }

        return [
            'apiToken' => $xApiToken,
            'gatewayApiToken' => $gatewayXApiToken,
            'userId' => $FKSUserId,
            'generatePdfParams' => $generatePdfParams,
        ];
    }
}
