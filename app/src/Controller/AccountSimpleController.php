<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class AccountSimpleController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}


    #[Route('/deposit/{id<\d+>}/{amount}', methods: ['POST'])]
    public function deposit(int $id, string $amount): JsonResponse
    {
        $conn = $this->em->getConnection();

        // prosta walidacja kwoty z URL (obsługa przecinka)
        $raw = str_replace(',', '.', trim($amount));
        if ($raw === '' || !is_numeric($raw) || (float)$raw <= 0) {
            return $this->json(['error' => 'Kwota musi być > 0'], 400);
        }
        $cents = (int) round(((float)$raw) * 100);

        $conn->beginTransaction();
        try {
            /** @var Account|null $acc */
            $acc = $this->em->find(Account::class, $id);

            if (!$acc) {
                throw new \RuntimeException('Konto nie istnieje.');
            }

            // $acc->setBalanceCents($acc->getBalanceCents() + $cents);
            $acc->addBalanse($cents);
            $this->em->flush();

            $conn->commit();

            return $this->json([
                'balance' => number_format($acc->getBalanceCents() / 100, 2, '.', ''),
            ], 200);
        } catch (\Throwable $e) {
            $conn->rollBack();
            $status = $e instanceof \RuntimeException ? 404 : 500;
            return $this->json(['error' => $e->getMessage()], $status);
        }
    }

    #[Route('/withdraw/{id<\d+>}/{amount}', methods: ['POST'])]
    public function withdraw(int $id, string $amount): JsonResponse
    {
        $conn = $this->em->getConnection();

        // prosta walidacja kwoty z URL (obsługa przecinka)
        $raw = str_replace(',', '.', trim($amount));
        if ($raw === '' || !is_numeric($raw) || (float)$raw <= 0) {
            return $this->json(['error' => 'Kwota musi być > 0'], 400);
        }
        $cents = (int) round(((float)$raw) * 100);

        $conn->beginTransaction();
        try {
            /** @var Account|null $acc */
            $acc = $this->em->find(Account::class, $id);
            if (!$acc) {
                throw new \RuntimeException('Konto nie istnieje.');
            }

            if ($acc->getBalanceCents() < $cents) {
                throw new \LogicException('Brak środków – transakcja wycofana.');
            }

            $acc->setBalanceCents($acc->getBalanceCents() - $cents);
            $this->em->flush();

            $conn->commit();

            return $this->json([
                'balance' => number_format($acc->getBalanceCents() / 100, 2, '.', ''),
            ], 200);
        } catch (\LogicException $e) {
            $conn->rollBack();
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $conn->rollBack();
            $status = $e instanceof \RuntimeException ? 404 : 500;
            return $this->json(['error' => $e->getMessage()], $status);
        }
    }

    #[Route('/account/simple', name: 'app_account_simple')]
    public function index(): Response
    {
        return $this->render('account_simple/index.html.twig', [
            'controller_name' => 'AccountSimpleController',
        ]);
    }
}
