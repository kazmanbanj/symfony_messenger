<?php

namespace App\MessageHandler\Query;

use App\Message\Query\GetTotalImageCount;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetTotalImageCountHandler
{
    public function __invoke(GetTotalImageCount $getTotalImageCount)
    {
        return 50;
    }
}
