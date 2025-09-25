<?php

namespace App\Controller;

use App\Service\NotifierResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
    public function greetings(Request $req): JsonResponse
    {
        $name = $req->query->get('name', 'Guest');
        $strategyKey = $req->query->get('strategy', 'formal');

        // Strategie
        interface GreetingsStrategy {
            public function greet(string $name): string;
        }

        class FormalGreetingsStrategy implements GreetingsStrategy {
            public function greet(string $name): string {
                return "Good day, " . $name . ".";
            }
        }

        class CasualGreetingsStrategy implements GreetingsStrategy {
            public function greet(string $name): string {
                return "Hi " . $name . "!";
            }
        }

        class GreetingsResolver {
            public static function byKey(string $key): GreetingsStrategy {
                return match($key) {
                    'formal' => new FormalGreetingsStrategy(),
                    'casual' => new CasualGreetingsStrategy(),
                    default => throw new \Exception('Unknown strategy'),
                };
            }
        }

        try {
            $strategy = GreetingsResolver::byKey($strategyKey);
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
