# POS Web Completo

Aplicación POS web desarrollada en PHP 8 con SQLite para gestionar ventas, stock y reportes de un comercio pequeño. Incluye autenticación segura, panel de control con indicadores y un diseño moderno responsivo.

## Funcionalidades
- Login y registro de usuarios con contraseñas cifradas.
- Dashboard con resumen de productos, stock bajo y ventas del día.
- Gestión completa de stock: alta, edición y baja de productos.
- Registro de ventas con control de stock y cálculo automático de IVA (21%).
- Reportes diarios con listado de ventas, total del día y alerta de stock crítico.
- Cierre Z rápido para obtener el resumen del día.

## Requisitos
- PHP 8 o superior.
- Extensión PDO SQLite habilitada.
- Servidor web incluido en XAMPP (Apache).

## Instalación
1. Clone o descargue este repositorio en el directorio `htdocs` de XAMPP.
2. Inicie Apache desde el panel de control de XAMPP.
3. Acceda desde el navegador a `http://localhost/pos-ar-fiscal/login.php`.
4. Inicie sesión con el usuario demo `admin` y contraseña `admin` o registre uno nuevo.

¡Listo! La base de datos `pos.db` se genera automáticamente con datos de ejemplo la primera vez que acceda.
