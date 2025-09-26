<?php

namespace App\Controller;

use App\Service\NotifierResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GreetingsResolver;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TaskController.php',
        ]);
    }

    // Implementacja wzorca strategia dla serwisu greetings

    #[Route('/greetings', methods: ['GET'])]
    public function greetings(Request $req, GreetingsResolver $resolver): JsonResponse
    {
        $name = $req->query->get('name', 'Guest');
        $strategyKey = $req->query->get('strategy', 'formal');

        try {
            $strategy = $resolver->byKey($strategyKey);
            $greeting = $strategy->greet($name);
        } catch (\Exception $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }

        return $this->json(['ok' => true, 'greeting' => $greeting, 'strategy' => $strategyKey]);
    }



    #[Route('/notify', methods: ['GET','POST'])]
    public function notify(Request $req, NotifierResolver $resolver): JsonResponse
    {
        $channel = $req->query->get('channel');
        $message = 'Task created';

        try {
            $resolver->byKey($channel)->notify($message);
        } catch (\Exception $e) {

            return $this->json(['ok' => false, 'error' => "nie ma takiego kanału"], 400);
        }
        
        return $this->json(['ok' => true, 'channel' => $channel]);
    }
}
