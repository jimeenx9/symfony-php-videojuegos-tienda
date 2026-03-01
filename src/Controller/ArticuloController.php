<?php

namespace App\Controller;

use App\Entity\Articulo;
use App\Repository\ArticuloRepository;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticuloController extends AbstractController
{
    #[Route('/articulos/new', name: 'articulos_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        CategoriaRepository $categoriaRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categorias = $categoriaRepository->findAll();
        $error = null;

        if ($request->isMethod('POST')) {

            $nombre = trim($request->request->get('nombre'));
            $precio = (float) $request->request->get('precio');
            $iva = (int) $request->request->get('iva') ?: 21;
            $stock = (int) $request->request->get('stock');
            $descripcion = $request->request->get('descripcion');
            $categoriaId = (int) $request->request->get('categoria');

            if (!$nombre || !$precio || !$stock || !$categoriaId) {
                $error = "Todos los campos obligatorios deben completarse.";
            } else {

                $categoria = $categoriaRepository->find($categoriaId);

                if (!$categoria) {
                    throw $this->createNotFoundException('Categoría no encontrada');
                }

                $articulo = new Articulo();
                $articulo->setNombre($nombre);
                $articulo->setPrecio($precio);
                $articulo->setIva($iva);
                $articulo->setStock($stock);
                $articulo->setDescripcion($descripcion);
                $articulo->setCategoria($categoria);

                // 🔥 Upload imagen
                $imagenFile = $request->files->get('imagen');

                if ($imagenFile) {
                    $nombreArchivo = uniqid() . '.' . $imagenFile->guessExtension();
                    $imagenFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/img',
                        $nombreArchivo
                    );
                    $articulo->setImagen($nombreArchivo);
                }

                $entityManager->persist($articulo);
                $entityManager->flush();

                return $this->redirectToRoute('inicio');
            }
        }

        return $this->render('articulo/new.html.twig', [
            'categorias' => $categorias,
            'error' => $error
        ]);
    }

    #[Route('/articulos/edit/{id}', name: 'articulos_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        ArticuloRepository $articuloRepository,
        CategoriaRepository $categoriaRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $articulo = $articuloRepository->find($id);

        if (!$articulo) {
            throw $this->createNotFoundException('Artículo no encontrado');
        }

        $categorias = $categoriaRepository->findAll();
        $error = null;

        if ($request->isMethod('POST')) {

            $nombre = trim($request->request->get('nombre'));
            $precio = (float) $request->request->get('precio');
            $iva = (int) $request->request->get('iva') ?: 21;
            $stock = (int) $request->request->get('stock');
            $descripcion = $request->request->get('descripcion');
            $categoriaId = (int) $request->request->get('categoria');

            if (!$nombre || !$precio || !$stock || !$categoriaId) {
                $error = "Todos los campos obligatorios deben completarse.";
            } else {

                $categoria = $categoriaRepository->find($categoriaId);

                if ($categoria) {
                    $articulo->setCategoria($categoria);
                }

                $articulo->setNombre($nombre);
                $articulo->setPrecio($precio);
                $articulo->setIva($iva);
                $articulo->setStock($stock);
                $articulo->setDescripcion($descripcion);

                // 🔥 Upload imagen
                $imagenFile = $request->files->get('imagen');

                if ($imagenFile) {
                    $nombreArchivo = uniqid() . '.' . $imagenFile->guessExtension();
                    $imagenFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/img',
                        $nombreArchivo
                    );
                    $articulo->setImagen($nombreArchivo);
                }

                $entityManager->flush();

                return $this->redirectToRoute('inicio');
            }
        }

        return $this->render('articulo/new.html.twig', [
            'articulo' => $articulo,
            'categorias' => $categorias,
            'error' => $error
        ]);
    }

    #[Route('/articulos/delete/{id}', name: 'articulos_delete', methods: ['GET', 'POST'])]
    public function delete(
        int $id,
        Request $request,
        ArticuloRepository $articuloRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $articulo = $articuloRepository->find($id);

        if (!$articulo) {
            throw $this->createNotFoundException('Artículo no encontrado');
        }

        // 👉 Si es POST → borrar definitivamente
        if ($request->isMethod('POST')) {

            $entityManager->remove($articulo);
            $entityManager->flush();

            return $this->redirectToRoute('inicio');
        }

        // 👉 Si es GET → mostrar confirmación
        return $this->render('articulo/delete.html.twig', [
            'articulo' => $articulo
        ]);
    }

    #[Route('/stock', name: 'stock')]
    public function stock(ArticuloRepository $articuloRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $articulos = $articuloRepository->findAll();

        return $this->render('articulo/stock.html.twig', [
            'articulos' => $articulos
        ]);
    }
}