<?php

namespace App\Controller;

use App\Entity\ImagePost;
use App\Message\AddPonkaToImage;
use App\Message\DeleteImagePost;
use App\Photo\PhotoPonkaficator;
use App\Repository\ImagePostRepository;
use App\Photo\PhotoFileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImagePostController extends AbstractController
{
    /**
     * @Route("/api/images", methods="GET")
     */
    public function list(ImagePostRepository $repository)
    {
        $posts = $repository->findBy([], ['createdAt' => 'DESC']);

        return $this->toJson([
            'items' => $posts
        ]);
    }

    /**
     * @Route("/api/images", methods="POST")
     */
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
            return $this->json($errors, 400);
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
            new DelayStamp(500)
        ]);
        $messageBus->dispatch($envelope);

        // FilePond expects a STRING serverId
        return $this->json($imagePost, 201);
    }

    /**
     * @Route("/api/images/{id}", methods="DELETE")
     */
    public function delete(ImagePost $imagePost, MessageBusInterface $messageBus)
    {
        sleep(1); // Simulate some delay for better UX

        $messageBus->dispatch(new DeleteImagePost($imagePost));

        return new Response(null, 204);
    }

    /**
     * @Route("/api/images/{id}", methods="GET", name="get_image_post_item")
     */
    public function getItem(ImagePost $imagePost)
    {
        return $this->toJson($imagePost);
    }

    private function toJson($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        // add the image:output group by default
        if (!isset($context['groups'])) {
            $context['groups'] = ['image:output'];
        }

        return $this->json($data, $status, $headers, $context);
    }
}
