<?php

namespace App\MessageHandler\Command;

use App\Message\Command\DeleteImagePost;
use App\Message\Event\ImagePostDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteImagePostHandler
{
    public function __construct(
        private MessageBusInterface $eventBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteImagePost $deleteImagePost): void
    {
        $imagePost = $deleteImagePost->getImagePost();
        $filename = $imagePost->getFilename();

        $this->entityManager->remove($imagePost);
        $this->entityManager->flush();

        $this->eventBus->dispatch( new ImagePostDeletedEvent($filename) );
    }
}