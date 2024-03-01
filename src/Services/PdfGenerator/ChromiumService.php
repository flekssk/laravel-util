<?php

declare(strict_types=1);

namespace FKS\Services\PdfGenerator;

use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use FKS\Exceptions\PdfGenerator\NavigatePdfUrlException;

class ChromiumService
{
    /**
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws OperationTimedOut
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws Exception
     */
    public function getPdfContentByUrl($url): string
    {
        $browserFactory = new BrowserFactory("/usr/bin/chromium");
        $browser = $browserFactory->createBrowser([
            'noSandbox' => true,
            'customFlags' => [
                '--allow-running-insecure-content',
                '--allow-insecure-localhost',
                '--ignore-certificate-errors',
                '--ignore-ssl-errors',
                '--disable-dev-shm-usage',
            ]
        ]);

        return self::retry(
            function () use ($browser, $url) {
                $page = $browser->createPage();
                $page->navigate($url, ['strict' => true])->waitForNavigation(Page::NETWORK_IDLE);
                echo (json_encode($page->getCurrentLifecycle()));
                $page->waitUntilContainsElement('#pdf-generate', 150000);
                $fileContent = $page->pdf()->getBase64(3000000);
                $browser->close();

                return base64_decode($fileContent);
            },
            $url
        );
    }

    public function getPreviewUri(
        string  $baseRoute,
        string  $apiToken,
        string  $currentUser,
        ?array   $attributes = [],
        ?array   $additionalQueryParams = [],
        ?string $cacheKey = null
    ): string {
        $queryParams = [
            'xApiToken' => $apiToken,
            'gatewayXApiToken' => $apiToken,
            'userId' => $currentUser,
        ];
        if ($cacheKey !== null) {
            $queryParams['cacheKey'] = $cacheKey;
        }
        $queryParams = array_merge($queryParams, $additionalQueryParams);
        $query = http_build_query($queryParams);

        $route = \route(
            $baseRoute,
            $attributes,
            false
        );

        return $route . '?' . $query;
    }

    /**
     * @param callable $callable
     * @param string $url
     * @param int $maxRetries
     * @param int $initialWait
     * @param int $exponent
     *
     * @return false|mixed
     *
     * @throws Exception
     */
    public static function retry(callable $callable, string $url, int $maxRetries = 5, int $initialWait = 1, int $exponent = 2): mixed
    {
        try {
            return call_user_func($callable);
        } catch (Exception $e) {
            echo ($e->getMessage());
            if ($maxRetries > 0) {
                usleep($initialWait * 1000);
                return self::retry($callable, $url, $maxRetries - 1, $initialWait * $exponent, $exponent);
            }
            NavigatePdfUrlException::becausePageIsNotLoaded($url);
        }
    }
}
