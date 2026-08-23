# 🛒 E-Commerce API (Laravel + Docker + Stripe Integration)

API RESTful profesional construida con Laravel, desplegada mediante Docker y conectada con Stripe para el procesamiento de pagos en tiempo real.

---

## 📋 Tabla de Contenidos

1. [Arquitectura y Estructura del Proyecto](#-arquitectura-y-estructura-del-proyecto)
2. [Requisitos Previos](#-requisitos-previos)
3. [Variables de Entorno (.env)](#-variables-de-entorno-env)
4. [Instalación y Despliegue con Docker](#-instalación-y-despliegue-con-docker)
5. [Modelos y Base de Datos](#-modelos-y-base-de-datos)
6. [Flujo de Autenticación y Consumo de Endpoints](#-flujo-de-autenticación-y-consumo-de-endpoints)
7. [Documentación de Endpoints y Ejemplos Postman](#-documentación-de-endpoints-y-ejemplos-postman)
8. [Pasarela de Pagos con Stripe](#-pasarela-de-pagos-con-stripe)
9. [Solución de Problemas Frecuentes](#-solución-de-problemas-frecuentes)

---

## 📁 Estructura del Proyecto

* **app/**
  * **Http/**
    * **Controllers/**
      * `AuthController.php` (Registro, Login, Refresh, Logout con JWT)
      * `ProductController.php` (CRUD de productos y gestión de stock)
      * `OrderController.php` (Proceso de Checkout y Consulta de Órdenes)
    * **Requests/**
      * `CheckoutRequest.php` (Validación de payload para compras)
      * `RegisterRequest.php` (Validación de registro de usuario)
  * **Models/**
    * `User.php` (Modelo de Usuarios)
    * `Product.php` (Modelo de Productos)
    * `Order.php` (Modelo de Órdenes de Compra)
    * `OrderItem.php` (Detalle/Líneas de la Orden)
    * `Payment.php` (Registro de Transacciones Stripe)
* **config/**
  * `services.php` (Configuración de servicios externos como Stripe)
  * `jwt.php` (Configuración de JWT Auth)
* **database/**
  * **migrations/** (Migraciones de usuarios, productos, órdenes, items y pagos)
  * **seeders/** (Datos iniciales para pruebas)
* **docker/** (Archivos de configuración de Docker para PHP y Nginx)
* **routes/**
  * `api.php` (Definición de endpoints protegidos y públicos)
* `docker-compose.yml` (Orquestación de servicios: App, MySQL y Nginx)
* `.env.example` (Plantilla de variables de entorno)
* `README.md` (Documentación oficial)

---

## 🧰 Requisitos Previos

* **Docker Desktop** (v20.10+)
* **Docker Compose** (v2.0+)
* **Postman** / **Insomnia** (para pruebas de API)
* Cuenta de prueba en **Stripe** (https://dashboard.stripe.com/)

---

## ⚙️ Variables de Entorno (.env)

Crea o edita el archivo `.env` en la raíz del proyecto con la siguiente configuración requerida:

APP_NAME=EcommerceAPI
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=root

JWT_SECRET=

# Configuración de Stripe (Test Mode)
STRIPE_KEY=sk_test_your_stripe_key_here
STRIPE_SECRET=sk_test_your_stripe_secret_key_here

L5_SWAGGER_GENERATE_ALWAYS=true

---

## 🚀 Instalación y Despliegue con Docker

Sigue estos pasos en orden para levantar la aplicación desde cero:

### 1. Clonar el repositorio y configurar variables
cp .env.example .env

### 2. Levantar los contenedores de Docker
docker compose up -d --build

### 3. Generar la clave de aplicación y Secreto JWT
docker exec -it api_ecommerce_app php artisan key:generate
docker exec -it api_ecommerce_app php artisan jwt:secret

### 4. Ejecutar migraciones y datos de prueba (Seeders)
docker exec -it api_ecommerce_app php artisan migrate:fresh --seed

### 5. Limpiar cachés de optimización
docker exec -it api_ecommerce_app php artisan config:clear
docker exec -it api_ecommerce_app php artisan cache:clear
docker exec -it api_ecommerce_app php artisan optimize:clear

---

## 🗄 Modelos y Base de Datos

* **`users`**: Almacena clientes y administradores.
* **`products`**: Almacena el inventario (`name`, `price`, `stock`, `description`).
* **`orders`**: Registra las compras (`user_id`, `total_amount`, `status: pending|paid|cancelled`).
* **`order_items`**: Detalle de productos comprados (`order_id`, `product_id`, `quantity`, `price`).
* **`payments`**: Transacciones procesadas (`order_id`, `stripe_payment_intent_id`, `amount`, `status: succeeded`).

---

## 🔐 Flujo de Autenticación y Consumo de Endpoints

1. Todos los endpoints de la API (excepto Login/Register y Listado de Productos) requieren autenticación mediante **Bearer Token (JWT)**.
2. Cabeceras obligatorias en **todas** las peticiones:
   * `Content-Type: application/json`
   * `Accept: application/json`
   * `Authorization: Bearer <TU_TOKEN_JWT>` (Para rutas protegidas)

---

## 📑 Documentación de Endpoints y Ejemplos Postman

### 1. Autenticación (Auth)

#### 🔑 Iniciar Sesión (Login)
* **HTTP Method:** `POST`
* **URL:** `http://localhost:8000/api/auth/login`
* **Body (JSON):**
{
  "email": "cliente@example.com",
  "password": "password123"
}

* **Respuesta Exitosa (200 OK):**
{
  "access_token": "eyJhbGciOiJIUzI1Ni...",
  "token_type": "bearer",
  "expires_in": 3600
}

---

### 2. Inventario (Products)

#### 📦 Listar Productos
* **HTTP Method:** `GET`
* **URL:** `http://localhost:8000/api/products`
* **Respuesta Exitosa (200 OK):**
[
  {
    "id": 1,
    "name": "Laptop Pro 15",
    "price": "49.99",
    "stock": 10,
    "created_at": "2026-08-23T00:00:00.000000Z"
  }
]

---

### 3. Pasarela de Pagos y Checkout (Orders)

#### 💳 Procesar Compra (Checkout con Stripe)
* **HTTP Method:** `POST`
* **URL:** `http://localhost:8000/api/checkout`
* **Headers:**
  * `Authorization: Bearer <TU_TOKEN_JWT>`
  * `Content-Type: application/json`
* **Body (JSON):**
{
  "payment_method_id": "pm_card_visa",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}

* **Respuesta Exitosa (201 Created):**
{
  "message": "Compra realizada exitosamente",
  "order": {
    "user_id": 3,
    "total_amount": 99.98,
    "status": "paid",
    "updated_at": "2026-08-23T04:19:19.000000Z",
    "created_at": "2026-08-23T04:19:16.000000Z",
    "id": 6,
    "items": [
      {
        "id": 6,
        "order_id": 6,
        "product_id": 1,
        "quantity": 2,
        "price": "49.99",
        "created_at": "2026-08-23T04:19:16.000000Z",
        "updated_at": "2026-08-23T04:19:16.000000Z"
      }
    ],
    "payment": {
      "id": 1,
      "order_id": 6,
      "stripe_payment_intent_id": "pi_3U7SsLJvN6pTk6uV1bHHYbSf",
      "amount": "99.98",
      "status": "succeeded",
      "created_at": "2026-08-23T04:19:19.000000Z",
      "updated_at": "2026-08-23T04:19:19.000000Z"
    }
  }
}

#### 📜 Consultar Mis Órdenes
* **HTTP Method:** `GET`
* **URL:** `http://localhost:8000/api/orders/my-orders`
* **Headers:** `Authorization: Bearer <TU_TOKEN_JWT>`
* **Respuesta Exitosa (200 OK):** Muestra el historial completo de órdenes del usuario autenticado con sus items y detalles de pago Stripe.

---

## 💳 Pasarela de Pagos con Stripe

El sistema utiliza **Stripe PaymentIntents API**. Para realizar pruebas en entorno local sin tarjetas reales, puedes enviar cualquiera de los siguientes tokens oficiales de prueba en el campo `payment_method_id`:

| Tarjeta / Método | Token `payment_method_id` | Resultado Esperado |
| :--- | :--- | :--- |
| **Visa (Éxito)** | `pm_card_visa` | Transacción aprobada (`status: paid`) |
| **Mastercard (Éxito)** | `pm_card_mastercard` | Transacción aprobada (`status: paid`) |
| **Tarjeta Declinada** | `pm_card_chargeDeclined` | Error de pago (HTTP 500 / Card Declined) |

---

## 🛠 Solución de Problemas Frecuentes

### 1. Error: `No API key provided` o `La clave STRIPE_SECRET no se encuentra cargada`
* **Causa:** Las variables del `.env` no han sido cargadas en el contenedor Docker.
* **Solución:**
  docker compose down
  docker compose up -d
  docker exec -it api_ecommerce_app php artisan config:clear

### 2. Error: `This action is unauthorized`
* **Causa:** El método `authorize()` en `CheckoutRequest.php` retorna `false`.
* **Solución:** Asegúrate de que `public function authorize()` en `CheckoutRequest.php` retorne `true`.

### 3. Error: `Field 'user_id' doesn't have a default value`
* **Causa:** Falta el atributo `$fillable` en `Order.php`.
* **Solución:** Agrega `protected $fillable = ['user_id', 'total_amount', 'status'];` al modelo `Order`.