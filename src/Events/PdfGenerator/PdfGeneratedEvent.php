<?php

declare(strict_types=1);

namespace FKS\Events\PdfGenerator;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use function class_basename;

class PdfGeneratedEvent implements ShouldBroadcast
{
    public const BROADCAST_AS_NAME_SUCCESS = 'PdfGeneratedSuccessEvent';
    public const BROADCAST_AS_NAME_FAIL = 'PdfGeneratedFailEvent';

    public string $broadcastAsName;
    public User $user;
    public string $eventType;
    public string $messageType = 'link';
    public string $requestTime;
    public string $notifId;
    public array $params;
    protected $notificationClass = 'App\Notifications\DataTableExportGenerated';

    public function __construct(
        string $broadcastAsName,
        User $user,
        string $notificationId,
        array $params = []
    ) {
        $this->broadcastAsName = $broadcastAsName;
        $this->user = $user;
        $this->eventType = $params['eventType'] ?? '';
        $this->notifId = $notificationId;
        $this->requestTime = date('m-d-Y');
        $this->params = $params;
        $this->params['id'] = $notificationId;
        $this->params['read'] = false;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.' . class_basename($this->user) . '.' . $this->user->getKey());
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->params;
    }

    public function broadcastAs(): string
    {
        return $this->broadcastAsName;
    }
}
