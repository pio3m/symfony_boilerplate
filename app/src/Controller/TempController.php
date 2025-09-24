<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TempController extends AbstractController
{
    #[Route('/temp', name: 'app_temp')]
    public function index(): Response
    {
        $temp = 'piotr';
        dump($temp);

        return $this->render('temp/index.html.twig', [
            'abc' => 'TempController works!',
        ]);
    }
}
