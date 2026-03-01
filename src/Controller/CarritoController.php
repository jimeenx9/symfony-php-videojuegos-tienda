<?php

namespace App\Controller;

use App\Repository\ArticuloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CarritoController extends AbstractController
{
    #[Route('/carrito', name: 'carrito')]
    public function index(Request $request, ArticuloRepository $articuloRepository): Response
    {
        $session = $request->getSession();
        $carrito = $session->get('carrito', []);

        $articulos = [];
        $total = 0;

        foreach ($carrito as $id => $cantidad) {
            $articulo = $articuloRepository->find($id);
            if ($articulo) {
                $articulos[] = [
                    'articulo' => $articulo,
                    'cantidad' => $cantidad
                ];
                $total += $articulo->getPrecioFinal() * $cantidad;
            }
        }

        return $this->render('carrito/index.html.twig', [
            'articulos' => $articulos,
            'total' => $total
        ]);
    }

    #[Route('/carrito/add/{id}', name: 'carrito_add', requirements: ['id' => '\d+'])]
    public function add(int $id, Request $request, ArticuloRepository $articuloRepository): Response
    {
        $articulo = $articuloRepository->find($id);

        if (!$articulo) {
            throw $this->createNotFoundException('Artículo no encontrado');
        }

        $session = $request->getSession();
        $carrito = $session->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]++;
        } else {
            $carrito[$id] = 1;
        }

        $session->set('carrito', $carrito);

        return $this->redirectToRoute('carrito');
    }

    #[Route('/carrito_delete/{id}', name: 'carrito_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request): Response
    {
        $session = $request->getSession();
        $carrito = $session->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            $session->set('carrito', $carrito);
        }

        return $this->redirectToRoute('carrito');
    }

    #[Route('/pedido', name: 'pedido')]
    public function pedido(Request $request, ArticuloRepository $articuloRepository): Response
    {
        $session = $request->getSession();
        $carrito = $session->get('carrito', []);

        if (empty($carrito)) {
            return $this->redirectToRoute('carrito');
        }

        $articulos = [];
        $total = 0;

        foreach ($carrito as $id => $cantidad) {
            $articulo = $articuloRepository->find($id);
            if ($articulo) {
                $articulos[] = [
                    'articulo' => $articulo,
                    'cantidad' => $cantidad
                ];
                $total += $articulo->getPrecioFinal() * $cantidad;
            }
        }

        return $this->render('carrito/pedido.html.twig', [
            'articulos' => $articulos,
            'total' => $total
        ]);
    }

    #[Route('/fin_pedido', name: 'fin_pedido')]
    public function finPedido(
        Request $request,
        ArticuloRepository $articuloRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $session = $request->getSession();
        $carrito = $session->get('carrito', []);

        if (empty($carrito)) {
            return $this->redirectToRoute('carrito');
        }

        // 1️⃣ Validar stock suficiente
        foreach ($carrito as $id => $cantidad) {
            $articulo = $articuloRepository->find($id);

            if (!$articulo) {
                // el carrito tiene un id inválido
                $session->remove('carrito'); // opcional: lo limpias
                return $this->render('error/error.html.twig', [
                    'error' => 'Tu carrito tenía productos antiguos. Se ha reiniciado, vuelve a añadirlos.'
                ]);
            }

            if ($articulo->getStock() < $cantidad) {
                return $this->render('carrito/error_stock.html.twig', [
                    'articulo' => $articulo,
                    'cantidad' => $cantidad
                ]);
            }
        }

        // 2️⃣ Restar stock
        foreach ($carrito as $id => $cantidad) {
            $articulo = $articuloRepository->find($id);
            $articulo->setStock($articulo->getStock() - $cantidad);
        }

        $entityManager->flush();

        // 3️⃣ Vaciar carrito
        $session->remove('carrito');

        return $this->render('carrito/fin_pedido.html.twig');
    }

    #[Route('/carrito/update/{id}/{accion}', name: 'carrito_update')]
    public function update(
        int $id,
        string $accion,
        Request $request
    ): Response {

        $session = $request->getSession();
        $carrito = $session->get('carrito', []);

        if (!isset($carrito[$id])) {
            return $this->redirectToRoute('carrito');
        }

        if ($accion === 'sumar') {
            $carrito[$id]++;
        }

        if ($accion === 'restar') {
            $carrito[$id]--;

            if ($carrito[$id] <= 0) {
                unset($carrito[$id]);
            }
        }

        $session->set('carrito', $carrito);

        return $this->redirectToRoute('carrito');
    }
}