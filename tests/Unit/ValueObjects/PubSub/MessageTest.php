<?php

namespace Tests\Unit\ValueObjects\PubSub;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use FKS\ValueObjects\PubSub\Message;

/**
 * @covers \FKS\ValueObjects\PubSub\Message
 */
class MessageTest extends TestCase
{
    public function testMessage()
    {
        $message = new Message(['event-type' => 'event type name'], []);
        $this->assertEquals($message->getEventType(), 'event type name');

        $message = new Message(['event_type' => 'event type name2'], []);
        $this->assertEquals($message->getEventType(), 'event type name2');


        $message = new Message(['eventType' => 'event type name3'], []);
        $this->assertEquals($message->getEventType(), 'event type name3');
    }

    public function testGetMemberId()
    {
        $id = Uuid::uuid4()->toString();

        $message = new Message(['member_id' => $id], []);
        $this->assertEquals($message->getMemberId(), $id);

        $message = new Message([], ['memberId' => $id]);
        $this->assertEquals($message->getMemberId(), $id);

        $message = new Message(['member-id' => $id], []);
        $this->assertEquals($message->getMemberId(), $id);
    }
}
