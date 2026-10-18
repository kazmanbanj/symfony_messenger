<?php

namespace App\Message\Command;

class LogEmoji
{
    public function __construct(
        private int $emojiIndex
    ) {
    }

    public function getEmojiIndex(): int
    {
        return $this->emojiIndex;
    }
}
