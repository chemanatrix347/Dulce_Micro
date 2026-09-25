# Dulce_Micro

Sistema de gestión para una micro-empresa de postres, construido con PHP y MySQL siguiendo el patrón Modelo-Controlador-Vista (MVC). Incluye autenticación segura, control de acceso por roles (RBAC), CRUD completo sobre 23 módulos del negocio, dashboard con indicadores en tiempo real, generación de reportes en PDF/CSV, un punto de venta (POS) interno y un portal web para clientes.

## Tecnologías

- PHP 8+ (PDO para acceso a base de datos)
- MySQL / MariaDB
- Bootstrap 5.3.8 + Font Awesome
- Chart.js (gráficos del dashboard)
- FPDF (generación de reportes e informes en PDF)
- PHPMailer (envío de notificaciones/facturas por correo)

## Estructura de carpetas

```
Dulce_Micro/
├── config/           # Configuración de conexión a base de datos (conexion.php)
├── controlador/      # Controladores: reciben peticiones, validan y coordinan modelo/vista
├── modelo/           # Modelos: acceso a datos con PDO y sentencias preparadas
├── vista/            # Vistas del panel interno (administración, ventas, reportes)
│   └── partials/     # Componentes reutilizables: header, footer, control de roles
├── tienda/           # Portal web para clientes (catálogo, carrito, checkout, pedidos)
├── lib/
│   ├── fpdf/         # Librería FPDF para generación de reportes en PDF
│   └── PHPMailer/    # Librería para envío de correos
├── img/              # Imágenes de perfil por defecto
├── img_logos/        # Logo del sistema
├── img_clientes/     # Fotos de perfil subidas por clientes
├── sql/              # Scripts SQL: estructura, datos de prueba y vistas (pendiente)
└── index.php         # Punto de entrada / login
```

## Instalación local (XAMPP)

1. Clona este repositorio dentro de la carpeta `htdocs` de tu instalación de XAMPP:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/chemanatrix347/Dulce_Micro.git
   ```
2. Inicia Apache y MySQL desde el panel de control de XAMPP.
3. Crea la base de datos `login_db` en phpMyAdmin.
4. Importa los scripts SQL ubicados en `sql/` (estructura y datos de prueba).
5. Copia `config/conexion.example.php` como `config/conexion.php` y ajusta las credenciales de tu base de datos local si es necesario (por defecto: host `localhost`, usuario `root`, sin contraseña).
6. Abre `http://localhost/Dulce_Micro` en el navegador.

## Credenciales de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | d.jcc.juan.velasco@gmail.com | 1107845023 |
| Gerente General | *(pendiente)* | *(pendiente)* |
| Vendedor | *(pendiente)* | *(pendiente)* |

## Roles y permisos

- **Administrador:** acceso total, incluidos los módulos de Roles y Usuarios.
- **Gerente General:** acceso a todos los módulos operativos y de gestión (Contabilidad, Pagos, Materia Prima, Recetas, Tabla Maestra, Informes), sin acceso a Roles/Usuarios.
- **Vendedor:** acceso limitado a los módulos operativos básicos y al punto de venta (POS); sin acceso a módulos administrativos ni de gestión financiera.

## Módulos principales

Roles, Usuarios, Categorías de Postre, Sabores, Tamaños, Productos, Recetas, Materia Prima, Inventario, Clientes, Pedidos, Producción, Reposteras, Decoración, Gastos de Decoración, Métodos de Pago, Pagos, Estados de Pago/Pedido, Contabilidad, Tabla Maestra, Informes (PDF/CSV), Punto de Venta (POS) y Portal Web de Clientes (`tienda/`).

## Seguridad implementada

- Contraseñas con `password_hash()` / `password_verify()` (nunca en texto plano).
- Protección CSRF con tokens generados con `random_bytes()`.
- Sentencias preparadas (PDO) en todas las consultas.
- Control de acceso por sesión y por rol, validado en servidor.
- Borrado lógico (soft delete) en lugar de eliminación física de registros.
- Escape de salida con `htmlspecialchars()` para prevenir XSS.
