# Sistema de gestion de tareas
Proyecto para gestionar tareas con usuarios múltiples, roles y asignación colaborativa.
- **Administrador**: puede gestionar todas las tareas, registrar, eliminar y asignar usuarios a tareas.
- **Usuario estándar**: solo puede ver y editar determinadas cosas de tareas en las que está asignado.

> Todos los usuarios nuevos se crean por defecto como `standard`.
> Para asignar un admin, registrarlo como administrador desde una cuenta admin o modificar el campo `role` a `admin` en la tabla `users` o crearlo desde la terminal con php artisan tinker.

- Backend: Laravel
- Frontend: React
- Base de datos: MySQL (Laragon)
- Autenticación: JWT
- Estilos: Tailwind CSS
- Librerias adicionales: react-router-dom, react-select, react-icons, Sweetalert2

- PHP >= 8.x
- Composer
- Node.js / npm
- MySQL (Laragon recomendado)
- Git

## Backend

1. Clonar repositorio backend:
    ```
   git clone <URL-del-repo-back> abmBack

2. Entrar al proyecto:
    ```
   cd abmBackend

3. Instalar dependencias:
    ```
   composer install

4. Configurar variables de entorno:
    ```
    cp .env.example .env

5. En el archivo .env    
    ```
    APP_URL=http://localhost:8000

    DB_DATABASE=abmbackend
	DB_USERNAME=root
	DB_PASSWORD=

6. Crear la base de datos en phpMyAdmin 
    ```
    CREATE DATABASE abmbackend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

7. Ejecutar las migrtaciones:
    ```
    php artisan migrate

8. Crear usuario admin:
    ```
    php artisan db:seed

9. Ejecutar el comando
    ```
    php artisan jwt:secret

10. Iniciar el servidor 
    ```
    php artisan serve

## Frontend

1. Clonar el proyecto y acceder
    ```
    git clone <URL-del-repo-front> abmFront
    cd abmFront

2. Instalar dependencias y correr
    ```
    npm install
    npm run dev

### Notas importantes

Se utiliza JWT para autenticación, por lo que todos los endpoints protegidos requieren el token en el header Authorization: Bearer < token >.

La base de datos necesita un usuario administrador para poder manejar usuarios y tareas correctamente.

Es recomendable usar Laragon para simplificar la configuración de PHP, MySQL y la ejecución del backend.
