<?php

namespace App\Controller;

use App\Repository\ArticuloRepository;
use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TiendaController extends AbstractController
{
    #[Route('/', name: 'inicio')]
    public function inicio(
        ArticuloRepository $articuloRepository,
        CategoriaRepository $categoriaRepository
    ): Response {

        $articulos = $articuloRepository->findAll();
        $categorias = $categoriaRepository->findAll();

        return $this->render('tienda/inicio.html.twig', [
            'articulos' => $articulos,
            'categorias' => $categorias,
            'categoriaActiva' => null
        ]);
    }

    #[Route('/categoria/{id}', name: 'categoria')]
    public function filtrarPorCategoria(
        int $id,
        ArticuloRepository $articuloRepository,
        CategoriaRepository $categoriaRepository
    ): Response {

        $categoria = $categoriaRepository->find($id);

        if (!$categoria) {
            throw $this->createNotFoundException('Categoría no encontrada');
        }

        $articulos = $articuloRepository->findBy(['categoria' => $categoria]);
        $categorias = $categoriaRepository->findAll();

        return $this->render('tienda/inicio.html.twig', [
            'articulos' => $articulos,
            'categorias' => $categorias,
            'categoriaActiva' => $categoria
        ]);
    }
}