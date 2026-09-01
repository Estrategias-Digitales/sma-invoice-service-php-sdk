SMA Invoice Admin PHP SDK — Guidelines

Servicio
- Base URL: https://api-y744ss7wmq-uc.a.run.app
- OpenAPI JSON: https://api-y744ss7wmq-uc.a.run.app/v1/openapi.json
- Versión API actual: v1 (paths bajo /v1)

Objetivo del SDK
- Ofrecer una librería PHP ligera y extensible para interactuar con el servicio SMA Invoice Admin.
- Debe funcionar en cualquier framework (framework-agnostic) y en proyectos PHP tradicionales.

Autenticación
- El servicio utiliza Bearer JWT.
- Se asume que el integrador ya tiene un "source" creado.
- El SDK solicita al usuario `sourceId` y `sourcePassword` para autenticarse (POST /sources/login).
- El token se gestiona internamente: se almacena en memoria, se infiere expiración (campo `exp` del JWT) y se refresca cuando está por expirar o si una llamada responde 401 (reintento único).

Manejo del token
- TokenManager es responsable de:
  - Obtener token con /sources/login.
  - Inferir expiración desde el JWT (si `exp` disponible). Si no hay `exp`, se mantiene hasta un 401.
  - Refrescar proactivamente si quedan <30s al expirar, o reintentar una vez ante 401.
- El token NO se persiste en disco por defecto (decisión consciente para mantener el SDK simple y seguro). Puede extenderse en el futuro para agregar persistencia.

HTTP / Cliente base
- HttpClient implementa GET/POST/PUT/DELETE con cURL.
- Envia y recibe JSON por defecto (headers `Content-Type: application/json`, `Accept: application/json`).
- Las respuestas son normalizadas a: `{ status, headers, body }` donde `body` es JSON decodificado (array) si es posible; si no, es string.
- Errores HTTP (>=400) o de red lanzan `Sma\InvoiceService\Exception\ApiException` con `statusCode` y `responseBody` (cuando aplica).

Estructura del SDK
- Namespace raíz: `Sma\InvoiceService\`
- Clases principales:
  - `Config`: baseUrl, sourceId, sourcePassword.
  - `HttpClient`: capa HTTP.
  - `Auth\TokenManager`: gestiona el bearer token.
  - `Client`: fachada que inyecta Authorization y reintenta una vez ante 401.
  - `Resources\*`: endpoints agrupados por dominio (Health, Sources, Clients, Invoices, Operations).
- PSR-4: `src/` mapea a `Sma\InvoiceService\`.

Política de reintentos
- Reintento automático solo en caso de 401 y únicamente una vez, efectuando refresh de token.
- No se realizan reintentos automáticos para otros códigos (429, 5xx). Se pueden agregar más adelante si el servicio lo requiere (p. ej., backoff exponencial configurable).

Extensibilidad
- Para agregar endpoints nuevos:
  1. Crear una clase en `src/Resources` o añadir método en la clase existente más adecuada.
  2. Exponer acceso desde `Client` si se trata de un recurso nuevo (similar a `clients(), invoices(), ...`).
  3. Reutilizar `Client->request()` para endpoints no cubiertos o casos especiales.
- Mantener nombres de métodos sencillos y consistentes (`all`, `create`, `getById`, `update`, `delete`, acciones específicas como `seal`, `stamp`, etc.).
- Evitar acoplamiento a frameworks. No usar helpers de terceros dentro del core.

Compatibilidad y requisitos
- PHP >= 7.4.
- Extensión cURL habilitada.
- Sin dependencias externas obligatorias (se puede evaluar Guzzle en el futuro como opción adicional).

Estilo y contribución
- Seguir PSR-12 para estilo y PSR-4 para autoload.
- Documentar métodos públicos con PHPDoc cuando aporte claridad.
- Mantener el manejo de errores con `ApiException`.
- Preferir arrays asociativos para payload/response (sin modelos rígidos mientras el servicio evoluciona).

Versionado
- Dado que el servicio está en desarrollo, usar versionado semántico a partir de v0.x hasta estabilizar la API.
- Adoptar cambios no retrocompatibles en minor/major según semver.

Ejemplos de uso
- Ver `README.md` en la raíz del proyecto para ejemplos de autenticación, listado de clientes e interacción con facturas.

Notas adicionales
- Endpoints que no requieren auth: `/health` y `/openapi.json`.
- Login: `/sources/login`.
- Todos los demás endpoints usan bearer.
