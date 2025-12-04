SMA Invoice Admin PHP SDK

Descripción
- SDK en PHP para integrarse con el servicio SMA Invoice Admin.
- Maneja autenticación con bearer token a partir de credenciales de "source" (id y password).
- Renueva el token automáticamente cuando expira o ante respuesta 401 (reintento una sola vez).
- Framework-agnostic y extensible.

Requisitos
- PHP >= 7.4
- Extensión cURL habilitada

Instalación
1) Agrega el repositorio a tu proyecto (por ahora, copiar carpeta o via path repository en Composer):
   composer.json
   {
     "require": {
       "sma/sma-invoice-admin-php-sdk": "dev-main"
     },
     "repositories": [
       { "type": "path", "url": "./sma-invoice-service-php-sdk" }
     ]
   }
   Luego:
   composer require sma/sma-invoice-admin-php-sdk:dev-main

2) Autoload PSR-4: el SDK ya expone el namespace Sma\\InvoiceAdmin\\.

Uso rápido

Ejemplo básico
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Sma\InvoiceAdmin\Client;
use Sma\InvoiceAdmin\Config;

$config = new Config(
    'https://api-y744ss7wmq-uc.a.run.app',
    'SOURCE_ID',
    'SOURCE_PASSWORD'
);

$sdk = new Client($config);

// Health (sin auth)
$health = $sdk->health()->get();
var_dump($health);

// Listar clientes (con auth automática)
$clients = $sdk->clients()->all();
var_dump($clients);

// Crear factura
$newInvoice = $sdk->invoices()->create([
    // payload según tus necesidades del servicio
]);
var_dump($newInvoice);
```

Diseño
- Config: define baseUrl, sourceId, sourcePassword.
- HttpClient: requests HTTP JSON (GET/POST/PUT/DELETE) con cURL.
- TokenManager: realiza POST /sources/login y almacena token en memoria; infiere expiración desde el JWT (si existe campo `exp`) y refresca al expirar o ante 401 (un reintento).
- Client: inyecta Authorization Bearer en llamadas (excepto /health, /openapi.json, y /sources/login) y reintenta una vez en 401.
- Resources: Health, Sources, Clients, Invoices, Operations. Métodos ligeros que delegan en Client.

Autenticación
- Se asume que ya existe un "source" configurado en el servicio.
- Proveer `sourceId` y `sourcePassword` a `Config`.
- El SDK maneja internamente el bearer token.

Extensibilidad
- Puedes crear nuevos recursos/métodos bajo `src/Resources` siguiendo el patrón de `BaseResource`.
- O bien, usar `Client->request()` directamente para endpoints aún no cubiertos.

Notas
- El servicio está en desarrollo; la estructura del SDK favorece añadir endpoints rápidamente.
- Excepciones: se lanza `Sma\InvoiceAdmin\Exception\ApiException` en errores HTTP o de red.

OpenAPI del servicio
- https://api-y744ss7wmq-uc.a.run.app/v1/openapi.json
