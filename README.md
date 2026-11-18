# Dulce Micro — Repostería Artesanal

Sistema de gestión para una microempresa de postres, desarrollado con PHP y MySQL siguiendo el patrón Modelo-Vista-Controlador (MVC).

Incluye un panel administrativo con acceso por roles, módulos de gestión del negocio, reportes PDF/CSV, un punto de venta (POS) interno y un portal web para clientes.

## Tecnologías

- PHP 8+.
- MySQL / MariaDB.
- PDO para acceso a la base de datos.
- Bootstrap 5.3.8 y Font Awesome.
- Chart.js para gráficos del dashboard.
- FPDF para generación de documentos PDF.
- PHPMailer para envío de correos y facturas.

## Funcionalidades principales

- Autenticación y control de acceso por roles.
- Gestión de productos, sabores, tamaños y categorías.
- Gestión de clientes, pedidos, pagos y producción.
- Control de inventario, recetas y materias primas.
- Dashboard con indicadores del negocio.
- Reportes en PDF y CSV.
- Portal web para clientes.
- Punto de venta con interfaz en tonos rosa y lila.
- Envío de factura PDF por correo al finalizar una venta.
- Generación de tirillas para papel térmico de 57 mm.
- Registro del efectivo recibido y los vueltos.
- Consulta e impresión de comprobantes de ventas registradas.

## Punto de venta (POS)

El registro de una venta se organiza en cinco pasos:

1. Selección del producto.
2. Selección de presentación y cantidad.
3. Selección o registro del cliente.
4. Método y estado del pago.
5. Confirmación de la venta.

El POS permite consultar la disponibilidad de los productos y calcular los vueltos cuando el cliente paga en efectivo.

### Factura digital

Al finalizar la venta, el sistema intenta enviar la factura PDF al correo registrado del cliente.

- El PDF también puede abrirse desde el sistema.
- Si el cliente no tiene un correo válido, no se realiza el envío.
- Un fallo del envío no revierte una venta ya registrada.
- Abrir o reimprimir el comprobante no vuelve a enviar el correo.

### Tirilla POS de 57 mm

La tirilla incluye:

- Nombre del negocio.
- Número y fecha del pedido.
- Datos del cliente.
- Producto, cantidad y precio unitario.
- Subtotal, envío cuando corresponda y total.
- Método de pago.
- Efectivo recibido y vueltos, cuando existen datos guardados.
- Notas del pedido, cuando corresponda.
- Mensaje de agradecimiento.

Al terminar una venta, el sistema intenta abrir el diálogo de impresión del navegador. También dispone de una opción para imprimir la tirilla manualmente.

**La impresión requiere confirmación en el navegador. No se ha configurado impresión silenciosa.**

Para imprimir:

1. Instala y selecciona la impresora térmica.
2. Configura en su controlador el formato compatible con el rollo de 57 mm.
3. Usa escala del 100 %.
4. Desactiva márgenes, encabezados y pies de página.

El contenido utiliza un ancho de 48 mm dentro del papel de 57 mm. El ajuste final depende del área imprimible de la impresora.

### Pagos en efectivo

El sistema guarda estos campos en `pedido_completo`:

| Campo | Descripción |
|---|---|
| `efectivo_recibido` | Dinero entregado por el cliente. |
| `cambio` | Vueltos calculados para la venta. |

Los valores se muestran en la tirilla y en el PDF cuando el método de pago es efectivo y los datos están guardados.

Las ventas anteriores a esta actualización pueden tener ambos campos en `NULL`; en ese caso, los comprobantes no muestran esos importes.

## Estructura de carpetas

```text
Dulce_Micro/
├── config/           # Conexión y configuración
├── controlador/      # Procesamiento de peticiones
├── modelo/           # Acceso a datos mediante PDO
├── vista/            # Panel interno
│   └── partials/     # Componentes reutilizables
├── tienda/           # Portal web para clientes
├── lib/
│   ├── fpdf/         # Generación de PDF
│   └── PHPMailer/    # Envío de correos
├── img/              # Imágenes generales
├── img_logos/        # Identidad gráfica
├── img_clientes/     # Imágenes de clientes
├── sql/              # Scripts de base de datos, si están disponibles
└── index.php         # Entrada del sistema
```

### Archivos relacionados con el POS

| Archivo | Función |
|---|---|
| `vista/nuevaventa.php` | Interfaz de venta y apertura del diálogo de impresión. |
| `controlador/VentaController.php` | Registro de la venta y coordinación del envío de factura. |
| `modelo/pedidocompleto.php` | Creación, consulta y actualización de pedidos. |
| `controlador/factura_pos_lib.php` | Consulta de datos, generación del PDF y envío por correo. |
| `controlador/FacturaPDF.php` | Visualización del PDF de una venta. |
| `controlador/FacturaTicket.php` | Visualización e impresión de la tirilla de 57 mm. |

## Instalación local con XAMPP

1. Clona el repositorio dentro de `htdocs`:

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/chemanatrix347/Dulce_Micro.git
   ```

2. Inicia Apache y MySQL desde el panel de XAMPP.

3. Crea la base de datos `login_db` en phpMyAdmin.

4. Importa la estructura y los datos necesarios:
   - Utiliza los scripts de `sql/` si están incluidos.
   - Si no están disponibles, solicita una exportación de desarrollo al responsable del proyecto.

5. Configura la conexión:
   - Si existe `config/conexion.example.php`, cópialo como `config/conexion.php`.
   - Ajusta el host, nombre de base de datos, usuario y contraseña utilizados por `config/conexion.php`.

6. Comprueba que la base de datos incluya los campos de efectivo descritos en la siguiente sección.

7. Abre:

   ```text
   http://localhost/Dulce_Micro/
   ```

## Actualización de la base de datos: efectivo y vueltos

Para una instalación existente, revisa en phpMyAdmin la estructura de la tabla `pedido_completo`.

Si **ninguna de las dos columnas existe**, ejecuta una sola vez:

```sql
ALTER TABLE pedido_completo
    ADD COLUMN efectivo_recibido DECIMAL(12,2) NULL DEFAULT NULL,
    ADD COLUMN cambio DECIMAL(12,2) NULL DEFAULT NULL;
```

Si ambas columnas ya existen, omite este paso. Si existe solamente una, agrega únicamente la faltante.

Los cambios realizados directamente en phpMyAdmin no se transfieren al subir el código a GitHub. Esta actualización debe aplicarse también en las demás instalaciones que utilicen el código nuevo.

## Configuración del correo

Para enviar facturas, configura los datos SMTP utilizados por la integración con PHPMailer:

- Servidor SMTP.
- Puerto.
- Tipo de cifrado.
- Usuario y contraseña o credencial de aplicación.
- Dirección y nombre del remitente.

Revisa la configuración utilizada por `tienda/factura_lib.php` y sus archivos asociados.

No publiques credenciales SMTP ni contraseñas personales en el repositorio.

## Acceso de prueba

Las cuentas de prueba deben crearse o proporcionarse mediante el procedimiento de administración del proyecto.

Este README no incluye credenciales personales. Para una demostración, utiliza cuentas dedicadas con datos ficticios.

## Roles y permisos

- **Administrador:** acceso total, incluidos Roles y Usuarios.
- **Gerente General:** acceso a los módulos operativos y de gestión, sin administración de Roles y Usuarios.
- **Vendedor:** acceso a las funciones operativas autorizadas y al POS, con restricciones sobre administración y gestión financiera.

## Módulos del sistema

Roles, Usuarios, Categorías de Postre, Sabores, Tamaños, Productos, Recetas, Materia Prima, Inventario, Clientes, Pedidos, Producción, Reposteras, Decoración, Gastos de Decoración, Métodos de Pago, Pagos, Estados de Pago y Pedido, Contabilidad, Tabla Maestra, Informes, Punto de Venta y Portal Web de Clientes.

## Medidas de seguridad

El proyecto incorpora mecanismos de:

- Hash y verificación de contraseñas.
- Validación de sesiones y permisos por rol.
- Protección CSRF en formularios.
- Consultas preparadas con PDO.
- Escape de datos mostrados en HTML.
- Borrado lógico en los módulos que lo implementan.
- Validación del efectivo recibido antes de registrar la venta.

Estas medidas deben mantenerse y revisarse al agregar nuevas funcionalidades.

## Comprobaciones después de instalar o actualizar

- Registrar una venta en efectivo y comprobar el total, el recibido y los vueltos.
- Reimprimir esa venta y verificar que conserve los importes.
- Revisar que el PDF y la tirilla coincidan.
- Comprobar la recepción del PDF por correo.
- Registrar una venta con otro método y verificar que no muestre campos de efectivo.
- Confirmar que los comprobantes antiguos sin datos de efectivo se abran correctamente.

Las ventas de prueba quedan registradas y afectan el inventario; utiliza una base de datos de desarrollo.

## Alcance de los comprobantes

El sistema genera comprobantes de venta y los envía en formato PDF. Las funcionalidades descritas aquí no incluyen una integración de facturación electrónica con la DIAN.
