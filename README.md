# Symfony Videojuegos Tienda 🎮

Mini ecommerce desarrollado en Symfony.

## 🚀 Instalación

```bash
git clone https://github.com/jimeenx9/symfony-php-videojuegos-tienda.git
cd symfony-php-videojuegos-tienda
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
symfony server:start
```

## 👤 Usuarios de prueba

Admin:

- usuario: admin
- password: admin123

Usuario:

- usuario: user
- password: user123

## 🛠 Funcionalidades

- Registro y login
- Roles (admin / user)
- CRUD categorías
- CRUD videojuegos
- Carrito de compra
- Confirmación de pedido
- Control de stock
- Panel de stock (admin)

## 📦 Tecnologías

- Symfony
- Doctrine ORM
- Twig
- Bootstrap 5
- SQLite


---
