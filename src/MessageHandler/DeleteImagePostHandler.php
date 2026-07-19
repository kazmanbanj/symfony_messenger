<?php

namespace App\MessageHandler;

use App\Message\DeleteImagePost;
use App\Message\DeletePhotoFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteImagePostHandler
{
    private $messageBus;
    private $entityManager;

    public function __construct(
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager
    ) {
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
    }

    public function __invoke(
        DeleteImagePost $deleteImagePost
    ) {
        $imagePost = $deleteImagePost->getImagePost();
        $filename = $imagePost->getFilename();

        $this->entityManager->remove($imagePost);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new DeletePhotoFile($filename));
    }
}