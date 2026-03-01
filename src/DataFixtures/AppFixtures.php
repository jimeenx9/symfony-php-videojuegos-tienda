<?php

namespace App\DataFixtures;

use App\Entity\Categoria;
use App\Entity\Articulo;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        /*
        =========================
        USUARIOS
        =========================
        */

        $admin = new Usuario();
        $admin->setUsername('admin');
        $admin->setNombre('Administrador');
        $admin->setEmail('admin@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        $user = new Usuario();
        $user->setUsername('user');
        $user->setNombre('Usuario Normal');
        $user->setEmail('user@user.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'user123')
        );
        $manager->persist($user);


        /*
        =========================
        CATEGORÍAS
        =========================
        */

        $deportes = new Categoria();
        $deportes->setNombre('Deportes');
        $manager->persist($deportes);

        $arcade = new Categoria();
        $arcade->setNombre('Arcade');
        $manager->persist($arcade);

        $carreras = new Categoria();
        $carreras->setNombre('Carreras');
        $manager->persist($carreras);

        $accion = new Categoria();
        $accion->setNombre('Acción');
        $manager->persist($accion);


        /*
        =========================
        ARTÍCULOS
        =========================
        */

        $this->crearJuego($manager, 'Fernando Martín Basket', 12, 'Baloncesto 1 contra 1', 'basket.jpeg', 5, $deportes);
        $this->crearJuego($manager, 'Hyper Soccer', 10, 'Fútbol Konami', 'soccer.jpeg', 5, $deportes);
        $this->crearJuego($manager, 'Arkanoid', 15, 'Arcade clásico', 'arkanoid.jpeg', 7, $arcade);
        $this->crearJuego($manager, 'Tetris', 6, 'Puzzle clásico', 'tetris.jpeg', 5, $arcade);
        $this->crearJuego($manager, 'Road Fighter', 15, 'Carreras arcade', 'road.jpeg', 10, $carreras);
        $this->crearJuego($manager, 'Out Run', 10, 'Carreras Sega', 'outrun.jpeg', 3, $carreras);
        $this->crearJuego($manager, 'Army Moves', 8, 'Acción clásica', 'army.jpeg', 8, $accion);
        $this->crearJuego($manager, 'La Abadía del Crimen', 4, 'Aventura española', 'abadia.jpeg', 10, $accion);

        $manager->flush();
    }

    private function crearJuego($manager, $nombre, $precio, $descripcion, $imagen, $stock, $categoria)
    {
        $juego = new Articulo();
        $juego->setNombre($nombre);
        $juego->setPrecio($precio);
        $juego->setIva(21);
        $juego->setDescripcion($descripcion);
        $juego->setImagen($imagen);
        $juego->setStock($stock);
        $juego->setCategoria($categoria);

        $manager->persist($juego);
    }
}