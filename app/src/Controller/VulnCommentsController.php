<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VulnCommentsController extends AbstractController
{
    #[Route('/demo/comments', name: 'vuln_comments_list', methods: ['GET'])]
    public function list(Connection $conn): Response
    {
        $rows = $conn->fetchAllAssociative("SELECT id, author, content, created_at FROM comment ORDER BY id DESC");
        return $this->render('vuln/comments_list.html.twig', ['comments' => $rows]);
    }

    #[Route('/demo/comments/add', name: 'vuln_comments_add', methods: ['POST'])]
    public function add(Request $r, Connection $conn): Response
    {
        $author  = (string) $r->request->get('author', '');
        $content = (string) $r->request->get('content', '');
        $sql = "INSERT INTO comment (author, content, created_at) VALUES ('".$author."', '".$content."', NOW())";
        $conn->executeStatement($sql);
        return $this->redirectToRoute('vuln_comments_list');
    }

    #[Route('/demo/comments/delete', name: 'vuln_comments_delete', methods: ['POST'])]
    public function delete(Request $r, Connection $conn): Response
    {
        $filter = (string) $r->request->get('filter', '');
        $sql = "DELETE FROM comment WHERE author LIKE '%".$filter."%'";
        $conn->executeStatement($sql);
        return $this->redirectToRoute('vuln_comments_list');
    }

     // /xss/unsafe
    #[Route('/xss/unsafe', methods: ['GET'])]
    public function xssUnsafe(Request $r): Response {
//        /xss/unsafe?message=%3Cscript%3Ealert(1)%3C/script%3E
        $msg = (string) $r->query->get('message', 'Hello');
        return $this->render('xss/unsafe.html.twig', ['message' => $msg]);
    }

    #[Route('/transfer', name: 'transfer_form', methods: ['GET'])]
    public function transferForm(): Response
    {
        return $this->render('security/transfer.html.twig');
    }

    #[Route('/transfer', name: 'transfer', methods: ['POST'])]
    public function transfer(Request $r): Response
    {
        $token = (string) $r->request->get('_token', '');
        if (!$this->isCsrfTokenValid('transfer_form', $token)) {
            return new Response('Invalid CSRF token', 400);
        }

        // ...bezpieczna logika przelewu
        return new Response('OK');
    }
}
