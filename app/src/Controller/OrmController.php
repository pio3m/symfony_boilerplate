<?php

namespace App\Controller;

use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrmController extends AbstractController
{
    #[Route('/orm', name: 'app_orm')]
    public function index(): Response
    {
        return $this->render('orm/index.html.twig', [
            'controller_name' => 'OrmController',
        ]);
    }

    #[Route('/orm/note/s1-create/{title}', name: 'orm_note_s1', methods: ['GET'])]
    public function s1Create(string $title, EntityManagerInterface $em): Response
    {
        $note = (new Note())
            ->setTitle($title)
            ->setContent('Wersja robocza');

        // Encja trafia do Unit of Work (NEW), ale jeszcze brak SQL
        $em->persist($note);

        // return new Response('Po persist(): id = ' . var_export($note->getId(), true) . ' (powinno być null)');
        return $this->render('orm/index.html.twig', [
            'message' => 'Po persist(): id = ' . var_export($note->getId(), true) . ' (powinno być null)',
        ]); 
    }

    #[Route('/orm/note/s2-persist-check', name: 'orm_note_s2', methods: ['GET'])]
    public function s2PersistCheck(EntityManagerInterface $em): Response
    {
        $note = (new Note())
            ->setTitle('Persist-check')
            ->setContent('Draft');

        $em->persist($note);
        $managed = $em->contains($note) ? 'tak' : 'nie';

        return new Response('Czy encja jest managed po persist()? ' . $managed . ' (tak). Brak INSERT do czasu flush().');
    }

    #[Route('/orm/note/s3-flush', name: 'orm_note_s3', methods: ['GET'])]
    public function s3Flush(EntityManagerInterface $em): Response
    {
        $note = (new Note())
            ->setTitle('Flush moment')
            ->setContent('Start');

        $em->persist($note);
        $before = $note->getId();       // null
        $em->flush();                   // SQL: INSERT
        $after  = $note->getId();       // np. 42

        // return new Response("ID przed flush: " . var_export($before, true) . ", po flush: " . $after);
        return $this->render('orm/index.html.twig', [
            'message' => "ID przed flush: " . var_export($before, true) . ", po flush: " . $after,
        ]); 
    }

    #[Route('/orm/note/create/{title}', name: 'orm_note_create', methods: ['GET'])]
    public function create(string $title, EntityManagerInterface $em): Response
    {
        $note = (new Note())
            ->setTitle($title)
            ->setContent('Start');

        $em->persist($note);
        $em->flush(); // INSERT

        return new Response('Utworzono Note#' . $note->getId());
    }

    #[Route('/orm/note/update/{id}', name: 'orm_note_update', methods: ['POST', 'GET'])]
    public function update(int $id, EntityManagerInterface $em): Response
    {
        $note = $em->getRepository(Note::class)->find($id);
        if (!$note) {
            return new Response('Brak Note: ' . $id, 404);
        }

        $note->setContent('Treść po edycji'); // UoW: DIRTY
        $em->flush();                         // SQL: UPDATE (tylko zmienione pola)

        return new Response('Zaktualizowano Note#' . $id);
    }

    #[Route('/orm/note/delete/{id}', name: 'orm_note_delete', methods: ['POST', 'GET'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $note = $em->getRepository(Note::class)->find($id);
        if (!$note) {
            return new Response('Brak Note: ' . $id, 404);
        }

        $em->remove($note); // UoW: REMOVED
        $em->flush();       // SQL: DELETE

        return new Response('Usunięto Note#' . $id);
    }

    // --- CHANGESET     (podgląd zmian przed flush) ---
    #[Route('/orm/note/changeset', name: 'orm_note_changeset', methods: ['GET'])]
    public function changeset(EntityManagerInterface $em): Response
    {
        $note = (new Note())
            ->setTitle('Podgląd')
            ->setContent('Draft');

        $em->persist($note); // NEW w UoW

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // policz change sety teraz (edukacyjnie)
        $changes = $uow->getEntityChangeSet($note); // ['title' => [null, 'Podgląd'], 'content' => [null, 'Draft']]

        return $this->json([
            'changeset' => $changes,
            'hint' => 'Brak flush() — brak SQL.',
        ]);
    }

    // --- Wersja A: trzy kroki — trzy flush() ---
    #[Route('/orm/note/multi-flush', name: 'orm_note_multi', methods: ['GET'])]
    public function multiFlush(EntityManagerInterface $em): Response
    {
        $n = (new Note())
            ->setTitle('Multi')
            ->setContent('Krok 1');

        $em->persist($n);
        $em->flush();                 // SQL: INSERT

        $n->setContent('Krok 2');
        $em->flush();                 // SQL: UPDATE

        $em->remove($n);
        $em->flush();                 // SQL: DELETE

        return new Response('Wersja A: 1x INSERT, 1x UPDATE, 1x DELETE — zobacz w Profilerze -> Doctrine.');
    }

    // --- Wersja B: jeden flush na końcu (zobacz efekt netto) ---
    #[Route('/orm/note/single-flush', name: 'orm_note_single', methods: ['GET'])]
    public function singleFlush(EntityManagerInterface $em): Response
    {
        $n = (new Note())
            ->setTitle('Single')
            ->setContent('Krok 1');

        $em->persist($n);             // NEW
        $n->setContent('Krok 2');     // DIRTY
        $em->remove($n);              // REMOVED
        $em->flush();                 // Doctrine może zoptymalizować (INSERT znosi DELETE)

        return new Response('Wersja B: jeden flush — sprawdź w Profilerze, jakie SQL faktycznie poszły.');
    }

    // --- Showcase: pełny cykl INSERT -> UPDATE -> DELETE ---
    #[Route('/orm/note/showcase', name: 'orm_note_showcase', methods: ['GET'])]
    public function showcase(EntityManagerInterface $em): Response
    {
        $note = (new Note())
            ->setTitle('Showcase')
            ->setContent('Start');

        $em->persist($note);
        $em->flush();                 // INSERT

        $note->setContent('Po edycji');
        $em->flush();                 // UPDATE

        $em->remove($note);
        $em->flush();                 // DELETE

        return new Response('INSERT, potem UPDATE i DELETE wykonane — otwórz Profiler -> Doctrine.');
    }


    #[Route('/orm/note/tx-demo', name: 'orm_note_tx_demo', methods: ['GET'])]
    public function txDemo(Request $req, EntityManagerInterface $em): Response
    {
        $shouldFail = $req->query->getBoolean('fail'); // ?fail=1 zasymuluje błąd

        try {
            $em->wrapInTransaction(function (EntityManagerInterface $em) use ($shouldFail) {
                $a = (new Note())->setTitle('TX A')->setContent('Pierwsza');
                $b = (new Note())->setTitle('TX B')->setContent('Druga');

                $em->persist($a);
                $em->persist($b);

                if ($shouldFail) {
                    throw new \RuntimeException('Symulowany błąd – cała transakcja ma się wycofać');
                }
            });
            return new Response('Transakcja zakończona powodzeniem (2 INSERT). Sprawdź Profiler → Doctrine.');
        } catch (\Throwable $e) {
            return new Response('Rollback wykonany – brak INSERT. Powód: '.$e->getMessage(), 409);
        }
    }

    #[Route('/orm/note/tx-manual', name: 'orm_note_tx_manual', methods: ['GET'])]
    public function txManual(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();
        try {
            $n = (new Note())->setTitle('Manual TX')->setContent('Begin/Commit');
            $em->persist($n);
            $em->flush();      // zapis w ramach bieżącej transakcji
            $conn->commit();
            return new Response('Commit OK (1 INSERT).');
        } catch (\Throwable $e) {
            $conn->rollBack();
            return new Response('Rollback (brak INSERT): '.$e->getMessage(), 409);
        }
    }


    #[Route('/orm/notes/page/qb', name: 'orm_notes_page_qb', methods: ['GET'])]
    public function pageQb(Request $req, EntityManagerInterface $em): Response
    {
        $page = (int) $req->query->get('page', 2);      // przykład: strona 2
        $size = (int) $req->query->get('size', 5);      // przykład: 5 na stronę
        $q    = trim((string) $req->query->get('q', 'demo'));

        $qb = $em->getRepository(Note::class)->createQueryBuilder('n');

        if ($q !== '') {
            $qb->andWhere('n.title LIKE :q OR n.content LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }

        $qb->orderBy('n.title', 'ASC')
            ->addOrderBy('n.id', 'ASC')
            ->setFirstResult(($page - 1) * $size)       // offset
            ->setMaxResults($size);                     // limit

        $notes = $qb->getQuery()->getResult();

        return $this->json([
            'page'  => $page,
            'size'  => $size,
            'items' => array_map(fn(Note $n) => [
                'id' => $n->getId(), 'title' => $n->getTitle()
            ], $notes),
        ]);
    }


    #[Route('/orm/notes/page/dql', name: 'orm_notes_page_dql', methods: ['GET'])]
    public function pageDql(Request $req, EntityManagerInterface $em): Response
    {
        $page = (int) $req->query->get('page', 2);
        $size = (int) $req->query->get('size', 5);
        $q    = trim((string) $req->query->get('q', 'demo'));

        $base = 'SELECT n FROM App\Entity\Note n';
        $where = ($q !== '') ? ' WHERE (n.title LIKE :q OR n.content LIKE :q)' : '';
        $order = ' ORDER BY n.title ASC, n.id ASC';

        $dql = $base.$where.$order;

        $query = $em->createQuery($dql)
            ->setFirstResult(($page - 1) * $size)
            ->setMaxResults($size);

        if ($q !== '') {
            $query->setParameter('q', '%'.$q.'%');
        }

        $notes = $query->getResult();

        return $this->json([
            'page'  => $page,
            'size'  => $size,
            'items' => array_map(fn(Note $n) => [
                'id' => $n->getId(), 'title' => $n->getTitle()
            ], $notes),
        ]);
    }

}

