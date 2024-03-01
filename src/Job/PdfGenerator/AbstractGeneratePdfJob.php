<?php

declare(strict_types=1);

namespace FKS\Job\PdfGenerator;

use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FKS\Events\PdfGenerator\PdfGeneratedEvent;
use FKS\Facades\FKS;
use FKS\Facades\FKSLogger;
use FKS\Services\PdfGenerator\ChromiumService;
use FKS\Services\PdfGenerator\PdfStorageService;
use FKS\ValueObjects\Id;
use function event;

/**
 * Class AbstractGeneratePdfJob.
 *
 * @package App\Jobs
 */
abstract class AbstractGeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $id;
    protected string $url;
    protected User $user;
    protected array $config;
    protected ?string $type = null;
    protected string $generationUuidId;

    abstract protected function getDiskStoragePath(): string;
    abstract protected function getSuccessNotificationParams(): array;
    abstract protected function getFailNotificationParams(?string $debugMessage = null): array;


    public function __construct(
        string $url,
        User $user,
        array $config
    ) {
        $this->url = $url;
        $this->user = $user;
        $this->config = $config;
        $this->type = $this->config['type'] ?? null;
        $this->generationUuidId = FKS::getNewId()->getUuid();
    }

    /**
     * @param PdfStorageService $storage
     * @param ChromiumService $chromiumService
     *
     * @return void
     *
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws Exception
     *
     */
    public function handle(
        PdfStorageService $storage,
        ChromiumService $chromiumService
    ): void {
        try {
            FKSLogger::setLabels([
                'action_type' => 'generate_pdf',
                'action' => 'generate_pdf.' . $this->type !== null ? $this->type : 'without_type',
            ]);
            FKSLogger::beginProfile($token = 'Generate PDF');
            $loadStartTime = 0;
            $loadEndTime = 0;
            $storeStartTime = 0;
            $storeEndTime = 0;
            $loadStartTime = microtime(true);
            $pdfContent = $chromiumService->getPdfContentByUrl($this->url);
            $loadEndTime = microtime(true);

            $storeStartTime = microtime(true);
            $storage->put($this->getDiskStoragePath(), $pdfContent);
            $storeEndTime = microtime(true);

            $this->sendNotificationToUser($this->generationUuidId);
            $this->afterSuccessGeneration($this->generationUuidId);
        } catch (Exception $e) {
            FKSLogger::error($e->getMessage());
            $this->sendFailedNotificationToUser($this->generationUuidId, $e->getMessage());
            $this->afterFailedGeneration($this->generationUuidId, $e->getMessage());
            FKS::logException($e);
        }
        FKSLogger::endProfile(
            $token,
            [
                'url' => $this->url,
                'user_id' => Id::create($this->user->id)->getHex(),
                'config' => $this->config,
                'load_time' => $loadEndTime - $loadStartTime,
                'store_time' => $storeEndTime - $storeStartTime,
            ]
        );
    }

    public function getGenerationUuidId(): string
    {
        return $this->generationUuidId;
    }

    protected function afterSuccessGeneration(string $generationId): void
    {
        //
    }

    protected function afterFailedGeneration(string $generationId, string $failMessage): void
    {
        //
    }

    /**
     * @return void
     * @throws Exception
     */
    private function sendNotificationToUser(string $notificationId): void
    {
        event(
            new PdfGeneratedEvent(
                PdfGeneratedEvent::BROADCAST_AS_NAME_SUCCESS,
                $this->user,
                $notificationId,
                $this->getSuccessNotificationParams()
            )
        );
    }

    /**
     * @param string|null $debugMessage
     * @return void
     * @throws Exception
     */
    private function sendFailedNotificationToUser(string $notificationId, ?string $debugMessage = null): void
    {
        event(
            new PdfGeneratedEvent(
                PdfGeneratedEvent::BROADCAST_AS_NAME_FAIL,
                $this->user,
                $notificationId,
                $this->getFailNotificationParams($debugMessage)
            )
        );
    }
}
