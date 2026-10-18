<?php

namespace App\Controller;

use App\Entity\ImagePost;
use App\Message\Command\AddPonkaToImage;
use App\Message\Command\DeleteImagePost;
use App\Message\Command\LogEmoji;
use App\Photo\PhotoFileManager;
use App\Repository\ImagePostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImagePostController extends AbstractController
{
    #[Route('/api/images', methods: ['GET'])]
    public function list(ImagePostRepository $repository, PhotoFileManager $photoManager, UrlGeneratorInterface $router)
    {
        $posts = $repository->findBy([], ['createdAt' => 'DESC']);

        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'id' => $post->getId(),
                'originalFilename' => $post->getOriginalFilename(),
                'ponkaAddedAt' => $post->getPonkaAddedAt(),
                'createdAt' => $post->getCreatedAt(),
                '@id' => '/api/images/'.$post->getId(),
                'url' => $photoManager->getPublicPath($post),
            ];
        }

        return $this->toJson([
            'items' => $items
        ]);
    }

    #[Route('/api/images', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        PhotoFileManager $photoManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        /** @var UploadedFile $imageFile */
        $imageFile = $request->files->get('file');

        if (!$imageFile) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        $errors = $validator->validate($imageFile, [
            new Image(),
            new NotBlank()
        ]);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $err) {
                $messages[] = $err->getMessage();
            }

            $detail = implode(" ", $messages);

            return $this->json(['detail' => $detail], 400);
        }

        // Upload file
        $newFilename = $photoManager->uploadImage($imageFile);

        // Create entity
        $imagePost = new ImagePost();
        $imagePost->setFilename($newFilename);
        $imagePost->setOriginalFilename($imageFile->getClientOriginalName());

        $entityManager->persist($imagePost);
        $entityManager->flush();

        // Dispatch async processing
        $message = new AddPonkaToImage($imagePost->getId());
        $envelope = new Envelope($message, [
            new DelayStamp(1000),
            new AmqpStamp('high')
        ]);

        dump($messageBus->dispatch($envelope));
        $messageBus->dispatch(new LogEmoji(2));

        // FilePond expects a STRING serverId, but we want to return the full item
        // shape so the client can render the newly uploaded image immediately.
        return $this->json([
            'id' => $imagePost->getId(),
            'originalFilename' => $imagePost->getOriginalFilename(),
            'ponkaAddedAt' => $imagePost->getPonkaAddedAt(),
            'createdAt' => $imagePost->getCreatedAt(),
            '@id' => '/api/images/'.$imagePost->getId(),
            'url' => $photoManager->getPublicPath($imagePost),
        ], 201);
    }

    #[Route('/api/images/{id}', methods: ['DELETE'])]
    public function delete(ImagePost $imagePost, MessageBusInterface $messageBus)
    {
        // sleep(1); // Simulate some delay for better UX

        $messageBus->dispatch(new DeleteImagePost($imagePost));

        return new Response(null, 204);
    }

    #[Route('/api/images/{id}', methods: ['GET'], name: 'get_image_post_item')]
    public function getItem(ImagePost $imagePost)
    {
        return $this->toJson($imagePost);
    }

    private function toJson(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        // add the image:output group by default
        if (!isset($context['groups'])) {
            $context['groups'] = ['image:output'];
        }

        return $this->json($data, $status, $headers, $context);
    }
}
