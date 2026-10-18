<?php

namespace App\MessageHandler\Command;

use App\Message\Command\LogEmoji;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LogEmojiHandler
{
    private static array $emojis = [
        '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇',
        '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚',
        // Add more emojis as needed
    ];

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(LogEmoji $logEmoji)
    {
        $emojiIndex = $logEmoji->getEmojiIndex();
        $emoji = self::$emojis[$emojiIndex] ?? '❓';
        $this->logger->info("Logging emoji: {emoji}", ['emoji' => $emoji]);
    }
}
