<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoriaController extends AbstractController
{
    #[Route('/categorias', name: 'categorias')]
    public function index(CategoriaRepository $categoriaRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categorias = $categoriaRepository->findAll();

        return $this->render('categoria/index.html.twig', [
            'categorias' => $categorias
        ]);
    }

    #[Route('/categorias/new', name: 'categorias_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {

            $nombre = trim($request->request->get('nombre'));

            if ($nombre) {
                $categoria = new Categoria();
                $categoria->setNombre($nombre);

                $entityManager->persist($categoria);
                $entityManager->flush();

                return $this->redirectToRoute('categorias');
            }
        }

        return $this->render('categoria/new.html.twig');
    }

    #[Route('/categorias/edit/{id}', name: 'categorias_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        CategoriaRepository $categoriaRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categoria = $categoriaRepository->find($id);

        if (!$categoria) {
            throw $this->createNotFoundException('Categoría no encontrada');
        }

        if ($request->isMethod('POST')) {

            $nombre = trim($request->request->get('nombre'));

            if ($nombre) {
                $categoria->setNombre($nombre);
                $entityManager->flush();

                return $this->redirectToRoute('categorias');
            }
        }

        return $this->render('categoria/edit.html.twig', [
            'categoria' => $categoria
        ]);
    }

    #[Route('/categorias/delete/{id}', name: 'categorias_delete', methods: ['GET', 'POST'])]
    public function delete(
        int $id,
        Request $request,
        CategoriaRepository $categoriaRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categoria = $categoriaRepository->find($id);

        if (!$categoria) {
            throw $this->createNotFoundException('Categoría no encontrada');
        }

        if ($request->isMethod('POST')) {

            $entityManager->remove($categoria);
            $entityManager->flush();

            return $this->redirectToRoute('categorias');
        }

        return $this->render('categoria/delete.html.twig', [
            'categoria' => $categoria
        ]);
    }
}