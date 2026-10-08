# Miami Luxury Media Agency - Sistema de Operaciones, Flujo Audiovisual y Contratos con Realtors

Sistema web integral de gestión operativa y contratos inmobiliarios desarrollado con arquitectura **MVC puro**, esquema de base de datos normalizado en **3NF**, **Control de Acceso Basado en Roles (RBAC)** y permisos granulares por especialidad para agencias de producción audiovisual en Miami, Florida.

---

##  Arquitectura y Tecnologías
- **Patrón Arquitectónico:** MVC (Modelo - Vista - Controlador) estricto.
- **Backend:** PHP 8.2+ con Composer (Autoloading PSR-4).
- **Frontend:** HTML5 semántico, Bootstrap 5.3.3 (CSS & JS), Bootstrap Icons y JavaScript Vanilla para llamadas asíncronas (Fetch/AJAX) y reactividad.
- **Base de Datos:** PostgreSQL y MariaDB/MySQL con DDL normalizado en 3NF. Incluye driver SQLite integrado para ejecución y pruebas inmediatas *zero-config*.
- **Seguridad y Criptografía:** Hashing de contraseñas mediante **bcrypt**, sesiones protegidas contra fijación de sesión y cookies seguras (HttpOnly, SameSite), protección CSRF y sanitización de entradas.

---

##  Inicio Rápido (Ejecución Inmediata)

Desde la terminal en el directorio del proyecto:

```bash
# 1. Regenerar el autoloader de Composer si es necesario
composer dump-autoload

# 2. Iniciar el servidor web local integrado
php -S localhost:8000 -t public
```

Luego abre en tu navegador: **`http://localhost:8000`**

---

##  Credenciales de Prueba Preconfiguradas

| Perfil / Empleado | Rol del Sistema | Especialidad Granular | Correo de Acceso | Contraseña |
| :--- | :--- | :--- | :--- | :--- |
| **Carlos Mendoza** | `JEFE_ADMIN` | Dirección General (Super-Admin) | `jefe@miamiagency.com` | `password123` |
| **Valentina Rossi** | `EMPLEADO` | `CREADOR_CONTRATOS` | `contratos@miamiagency.com` | `password123` |
| **Mateo Silva** | `EMPLEADO` | `EDITOR_VIDEO` | `editor@miamiagency.com` | `password123` |
| **Camila Duarte** | `EMPLEADO` | `DISENADOR_CARRUSELES`, `CAMAROGRAFO` | `creativo@miamiagency.com` | `password123` |

*(La pantalla de inicio de sesión incluye botones directos para autocompletar credenciales con un solo clic).*

---

## Estructura del Proyecto (MVC)

```text
miami-agency-ops/
├── app/
│   ├── Controllers/
│   │   ├── Controller.php              # Clase base (render, json, flash messages, redirects)
│   │   ├── AuthController.php          # Login, logout y autenticación
│   │   ├── DashboardController.php     # Despacho de vista por rol (Jefe vs Empleado)
│   │   ├── ContractController.php      # Contratos finales, aprobaciones y generador
│   │   ├── ActivityController.php      # Tablero general y panel "Mis Tareas"
│   │   ├── RealtorController.php       # CRUD de clientes Realtors en Miami
│   │   ├── TeamController.php          # CRUD de personal y asignación de roles/especialidades
│   │   └── ReportController.php        # Reportes ejecutivos y exportación CSV/Excel
│   ├── Models/
│   │   ├── Model.php                   # Clase base PDO con transacciones atómicas
│   │   ├── UserModel.php               # Usuarios, empleados, roles y especialidades (M:N)
│   │   ├── ContractModel.php           # Compilador dinámico, borradores, aprobaciones y archivo
│   │   ├── RealtorModel.php            # Inmobiliarias y brokerages de Miami
│   │   ├── ActivityModel.php           # Tareas audiovisuales, entregables (Frame.io/Drive) y feedback
│   │   └── ReportModel.php             # Métricas semanales, quincenales y mensuales
│   └── Views/
│       ├── layouts/
│       │   └── main.php                # Layout responsivo con Bootstrap 5 y navbar por roles
│       ├── auth/
│       │   └── login.php               # Login con selector rápido por perfil
│       ├── dashboard/
│       │   ├── jefe.php                # Pipeline de KPIs, contratos y tareas del Jefe
│       │   └── empleado.php            # Panel contextual según especialidades del empleado
│       ├── contracts/
│       │   ├── final_repository.php    # Repositorio exclusivo de Contratos Finales
│       │   ├── approvals.php           # Aprobación de borradores con opción de feedback
│       │   ├── create.php              # Generador dinámico con previsualización en vivo
│       │   └── view.php                # Documento legal imprimible / Exportable a PDF
│       ├── activities/
│       │   ├── index.php               # Tablero general de producción audiovisual (Jefe)
│       │   └── my_tasks.php            # "Mis Tareas Audiovisuales" (Creativos)
│       ├── realtors/
│       │   └── index.php               # CRUD de clientes Realtors en Miami
│       ├── team/
│       │   └── index.php               # CRUD de equipo y especialidades granulares
│       └── reports/
│           └── index.php               # Reportes Ejecutivos (Semanal, Quincenal, Mensual)
├── config/
│   ├── config.php                      # Constantes de zonas de Miami, paquetes y configuración
│   ├── Database.php                    # Singleton PDO con inicialización y seed automático
│   └── Auth.php                        # Middleware RBAC, sesiones seguras y guards
├── database/
│   ├── schema_mysql.sql                # Script DDL normalizado 3NF para MySQL / MariaDB
│   └── schema_postgres.sql             # Script DDL normalizado 3NF para PostgreSQL
├── public/
│   ├── index.php                       # Front Controller y enrutador principal
│   └── assets/
│       ├── css/style.css               # Estilos personalizados y reglas de impresión PDF
│       └── js/app.js                   # JavaScript Vanilla auxiliar
├── storage/
│   └── database.sqlite                 # Base de datos pre-cargada con datos de prueba
├── test_system.php                     # Script de pruebas automatizadas por consola
└── composer.json                       # Definición de dependencias y PSR-4
```

---

##  Flujos de Trabajo Implementados

### 1. Flujo de Contratos Dinámicos con Realtors
1. **Redacción:** El empleado con especialidad `CREADOR_CONTRATOS` accede a `/contracts/create`.
2. **Parametrización:** Selecciona plantilla base, cliente Realtor (Brokerage de Miami: Brickell, Miami Beach, Sunny Isles, etc.), paquete audiovisual y honorarios.
3. **Previsualización en tiempo real:** JavaScript Vanilla consulta `/contracts/preview-ajax` e interpola instantáneamente las variables en el documento HTML.
4. **Envío y Bloqueo:** Al hacer clic en **"Enviar a Revisión"**, el contrato cambia de `BORRADOR` a `EN_REVISION`. *El contrato deja de ser editable por el empleado y se transfiere a la bandeja del Jefe.*
5. **Revisión del Jefe:** El Jefe revisa el documento en `/contracts/approvals`:
   - **Aprobar y Archivar:** Pasa a `APROBADO` y entra al **Repositorio Oficial de Contratos Finales** (`/contracts/final`) con cálculo de MRR y opción de descarga PDF limpia.
   - **Devolver con Observaciones:** Pasa a `RECHAZADO` con notas específicas de corrección, regresando a la bandeja del empleado.

### 2. Flujo de Tareas Audiovisuales
1. **Canalización (Jefe):** Crea la tarea y asigna al creativo (ej: Edición Reel, Rodaje Dron, Carrusel) vinculado al Realtor.
2. **Producción (Creativo):** En `/activities/my-tasks`, el creativo inicia la tarea (`EN_PROCESO`) y sube el enlace del entregable (Frame.io, Google Drive, Dropbox) con notas.
3. **Pase a Revisión:** El estado se actualiza automáticamente a `ENTREGADA_REVISION`.
4. **Calificación (Jefe):** Desde el tablero (`/activities`), el Jefe inspecciona el enlace y puede:
   - **Aprobar:** Tarea culminada (`APROBADA`).
   - **Solicitar Correcciones:** Tarea en estado `CORRECCION` con feedback detallado visible inmediatamente para el creativo.

### 3. Reportes Ejecutivos
- Filtros por períodos: **Semanal**, **Quincenal** y **Mensual**.
- Métricas: Total facturación mensual recurrente, contratos aprobados, ingresos por zona de Miami (Brickell, South Beach, Coral Gables, etc.), volumen de producción por formato y efectividad por especialista creativo.
- Exportación en un clic a **Excel (.csv)** e **Impresión / Guardar como PDF**.
