<?php

namespace App\Controller;

use App\Service\GreetingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class GreetingsController extends AbstractController
{
    #[Route('/greetings', name: 'app_greetings')]
    public function index(GreetingsService $greetingsService): Response
    {
        return $this->render('greetings/index.html.twig', [
            'controller_name' => 'GreetingsController',
            'powitanie' => $greetingsService->getGreeting(),
        ]);
    }
    
    //np. /greetings2?names[]=Ala&names[]=Ola&names[]=Ela
    #[Route('/greetings2', name: 'app_greetings2')]
    public function index2(Request $request): Response
    {
        $names = $request->query->all('names');
        $name = !empty($names) ? $names[array_rand($names)] : 'Guest';
        return $this->render('greetings/index.html.twig', [
            'controller_name' => 'GreetingsController',
            'powitanie' => sprintf('Witaj, %s!', $name),
        ]);
    }
}
