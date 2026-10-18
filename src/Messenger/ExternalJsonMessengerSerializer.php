<?php

namespace App\Messenger;

use App\Message\Command\LogEmoji;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ExternalJsonMessengerSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'] ?? '';
        $headers = $encodedEnvelope['headers'] ?? [];

        $data = json_decode($body, true);

        if(!isset($headers['type'])) {
            throw new MessageDecodingFailedException('Missing emoji header.');
        }

        switch ($headers['type']) {
            case 'emoji':
                return $this->createLogEmojiEnvelope($data);
            default:
                throw new MessageDecodingFailedException(sprintf('Unknown message type "%s".', $headers['type']));
        };
    }

    private function createLogEmojiEnvelope(array $data): Envelope
    {
        $message = new LogEmoji($data['emoji'] ?? 0);

        $envelope = new Envelope($message);
        $envelope = $envelope->with(new BusNameStamp('command.bus'));

        return $envelope;
    }

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();
        if (!$message instanceof LogEmoji) {
            throw new \InvalidArgumentException('Invalid message class.');
        }

        $data = [
            'emoji' => $message->getEmojiIndex(),
        ];

        $allStamps = [];
        foreach ($envelope->all() as $stamps) {
            $allStamps = array_merge($allStamps, $stamps);
        }

        return [
            'body' => json_encode($data),
            'headers' => [
                'stamps' => serialize($allStamps),
            ],
        ];
    }
}
