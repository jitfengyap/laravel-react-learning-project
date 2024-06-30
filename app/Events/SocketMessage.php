<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SocketMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {

        // Log when the event is constructed
        Log::info('SocketMessage event created for message ID: ' . $message->id);
    }

    public function broadcastWith(): array
    {
        return [
            'message' => new MessageResource($this->message),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $m = $this->message;
        $channels = [];

        if ($m->group_id) {
            $channelName = 'message.group.' . $m->group_id;
            // Log::info('Broadcasting on channel: ' . $channelName);
            $channels[] = new PrivateChannel($channelName);
        } else {
            $userIds = collect([$m->sender_id, $m->receiver_id])->sort()->implode('-');
            $channelName = 'message.user.' . $userIds;
            // Log::info('Broadcasting on channel: ' . $channelName);
            $channels[] = new PrivateChannel($channelName);
        }

        return $channels;
    }
}
