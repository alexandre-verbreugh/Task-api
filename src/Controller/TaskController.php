<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/tasks', name: 'app_tasks_')]
final class TaskController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();
        return $this->json($tasks, 200, [], [
            'groups' => ['task:read'],
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse 
    {
        $task = $serializer->deserialize($request->getContent(), Task::class, 'json');

        $task->setCreatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($task);
        $em->flush();

        return $this->json($task, Response::HTTP_CREATED, [], ['groups' => 'task:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        Task $task,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $serializer->deserialize($request->getContent(), Task::class, 'json', [
            'object_to_populate' => $task,
        ]);

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json($task, Response::HTTP_OK, [], ['groups' => 'task:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($task);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}
