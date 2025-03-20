# API de Órdenes de la Cocina del Restaurante

Este microservicio gestiona las órdenes de comida en la cocina del restaurante, interactuando con otros microservicios para obtener recetas y verificar la disponibilidad de ingredientes.

## Endpoints

La API expone los siguientes endpoints:

* **`GET /api/v1/orders`**:
    * Devuelve un listado de todas las órdenes pendientes, en preparación y completadas.
    * Ejemplo de respuesta:
        ```json
        [
            {
                "id": 1,
                "recipe_name": "Ensalada Fresca",
                "ingredients": [
                    {
                        "ingredient": "tomato",
                        "quantity": 2
                    },
                    {
                        "ingredient": "lettuce",
                        "quantity": 1
                    }
                ],
                "status": "pending",
                "created_at": "...",
                "updated_at": "..."
            },
            // ... otras órdenes
        ]
        ```
* **`POST /api/v1/orders`**:
    * Crea una nueva orden de comida.
    * Obtiene una receta aleatoria del microservicio de recetas.
    * Crea la orden y la encola para su procesamiento.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "data": {
                "message": "Pedido creado correctamente, receta: Ensalada Fresca"
            },
            "message": "Peticion exitosa, todo salio bien!",
            "success": true
        }
        ```

## Proceso de Creación y Procesamiento de Órdenes

El endpoint `/api/v1/orders` maneja la creación y procesamiento de órdenes de la siguiente manera:

1.  Obtiene una receta aleatoria del microservicio de recetas (a través de `env('MS_RANDOM_RECIPES_URL')`).
2.  Crea una nueva orden con la receta obtenida y el estado "pending".
3.  Despacha un trabajo en segundo plano (`OrderJob`) para procesar la orden.
4.  El `OrderJob` realiza los siguientes pasos:
    * Cambia el estado de la orden a "preparing".
    * Envía una solicitud al microservicio de inventario para verificar la disponibilidad de los ingredientes (a través de `env('MS_REQUEST_INGREDIENTS_URL')`).
    * Si los ingredientes están disponibles, cambia el estado de la orden a "completed".
    * Si los ingredientes no están disponibles, re-encola el trabajo para intentarlo nuevamente después de un retraso de 10 segundos.

## Configuración y Ejecución con Docker

Para ejecutar este microservicio utilizando Docker, sigue estos pasos:

1.  **Copia el archivo `.env.example` a `.env`:**
    * `cp .env.example .env`
    * Asegúrate de configurar las variables de entorno en el archivo `.env` (especialmente las URLs de los otros microservicios y las credenciales de la base de datos).

2.  **Levanta los contenedores con Docker Compose:**
    * `docker-compose up --build -d`
    * Este comando construirá las imágenes de Docker y levantará los contenedores en modo "detached" (en segundo plano).

3.  **Ejecuta las migraciones y semillas en el contenedor `api`:**
    * `docker-compose exec api php artisan migrate --seed`
    * Este comando ejecutará las migraciones de la base de datos y los seeders para poblar la base de datos con datos iniciales.

4.  **Genera la clave de la aplicación Laravel:**
    * `docker-compose exec api php artisan key:generate`
    * Este comando generará una clave de aplicación única para tu instalación de Laravel.

5.  **Accede a la API:**
    * La API estará disponible en `http://localhost:8004/api/v1/`.

## Tecnologías Utilizadas

* PHP 8.1+
* Laravel 10
* MySQL
* PHPUnit (para los tests)
* Guzzle HTTP Client (Para las peticiones a otros microservicios)

## Docker Compose

El archivo `docker-compose.yml` se utiliza para la configuración de los contenedores Docker.

## Tests

Este microservicio incluye tests unitarios para verificar el correcto funcionamiento de los endpoints. Los tests cubren los siguientes escenarios:

* **`test_index_success_with_default_parameters`**: Verifica que el endpoint `/api/v1/orders` devuelve una lista de órdenes con la estructura correcta y un código de estado 200.
* **`test_index_success_with_custom_parameters`**: Verifica que el endpoint `/api/v1/orders` acepta parámetros personalizados (como `take`, `pending_orders` y `order_direction`) y devuelve la cantidad correcta de órdenes.
* **`test_store_success`**: Verifica que el endpoint `/api/v1/orders` crea una nueva orden correctamente y encola el `OrderJob`.
* **`test_store_failed_external_api_error`**: Verifica que el endpoint `/api/v1/orders` devuelve un error 400 cuando falla la llamada al microservicio de recetas.
* **`test_store_exception_handling`**: Verifica que el endpoint `/api/v1/orders` maneja correctamente las excepciones y devuelve un error 500.

Para ejecutar los tests, puedes utilizar el siguiente comando dentro del contenedor `api`:

```bash
docker-compose exec api php artisan test