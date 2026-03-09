<?php

namespace App\MessageHandler;

use App\Message\DeleteImagePost;
use App\Message\DeletePhotoFile;
use App\Repository\ImagePostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteImagePostHandler implements MessageHandlerInterface
{
    private $messageBus;
    private $entityManager;
    private $imagePostRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager,
        ImagePostRepository $imagePostRepository
    ) {
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
        $this->imagePostRepository = $imagePostRepository;
    }


    public function __invoke(DeleteImagePost $deleteImagePost)
    {
        $imagePostId = $deleteImagePost->getImagePostId();
        $imagePost = $this->imagePostRepository->find($imagePostId);

        if (!$imagePost) {
            return;
        }

        $filename = $imagePost->getFilename();

        $this->entityManager->remove($imagePost);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new DeletePhotoFile($filename));
    }
}