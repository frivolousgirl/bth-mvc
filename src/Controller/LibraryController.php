<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LibraryController extends AbstractController
{
    private BookRepository $bookRepository;
    private ManagerRegistry $managerRegistry;

    public function __construct(BookRepository $bookRepository
        , ManagerRegistry $managerRegistry)
    {
        $this->bookRepository = $bookRepository;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/library', name: 'library')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', [
            'controller_name' => 'LibraryController',
        ]);
    }

    #[Route('/library/list/{id}/edit', name: 'library_edit', methods: ['GET'])]
    public function editBook(int $id)
    {
        $book = $this->bookRepository->find($id);

        return $this->render("library/edit.html.twig", ["book" => $book]);
    }

    #[Route('/library/list/{id}', name: "library_update", methods: ['POST'])]
    public function updateBook(int $id, Request $request)
    {
        $entityManager = $this->managerRegistry->getManager();
        $book = $this->bookRepository->find($id);

        if ($book) 
        {
            $book->setTitle($request->request->get('title'));
            $book->setIsbn($request->request->get('isbn'));
            $book->setAuthor($request->request->get('author'));
            $book->setImage($request->request->get('image'));

            $entityManager->flush();
        }

        return $this->redirectToRoute("library_list");
    }

    #[Route('/library/list/{id}', name: 'library_list_single', methods: ['GET'])]
    public function listSingle(int $id): Response
    {
        $book = $this->bookRepository->find($id);

        return $this->render("library/single.html.twig", ["book" => $book]);
    }

    #[Route('/library/list', name:'library_list')]
    public function list(): Response
    {
        $books = $this->bookRepository->findAll();

        return $this->render("library/list.html.twig", ["books" => $books]);
    }

    #[Route('/library/add', name: 'library_add')]
    public function add(): Response
    {
        return $this->render("library/add.html.twig");
    }

    #[Route('/library/delete/{id}', name: "library_delete", methods: ['POST'])]
    public function deleteBook(int $id): Response
    {
        $book = $this->bookRepository->find($id);

        if ($book)
        {
            $manager = $this->managerRegistry->getManager();

            $manager->remove($book);
            $manager->flush();
        }

        return $this->redirectToRoute("library_list");
    }

    #[Route('/library/create', name: 'library_create', methods: ['POST'])]
    public function createBook(
        Request $request,
        ManagerRegistry $doctrine
    ): Response {
        $entityManager = $doctrine->getManager();

        $title = $request->request->get('title');
        $isbn = $request->request->get('isbn');
        $author = $request->request->get('author');
        $image = $request->request->get('image');

        $book = new Book();
        $book->setTitle($title);
        $book->setIsbn($isbn);
        $book->setAuthor($author);
        $book->setImage($image);

        // tell Doctrine you want to (eventually) save the Product
        // (no queries yet)
        $entityManager->persist($book);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->redirectToRoute("library_list");
    }
}
