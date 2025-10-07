# 🏪 Sistema de Inventario Montoli's

Un sistema completo de gestión de inventario desarrollado en PHP con interfaz moderna y responsiva. Diseñado para pequeñas y medianas empresas que necesitan controlar su stock, ventas, movimientos de inventario y generar reportes financieros.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso del Sistema](#-uso-del-sistema)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Base de Datos](#-base-de-datos)
- [API y Clases](#-api-y-clases)
- [Características Avanzadas](#-características-avanzadas)
- [Reportes y Estadísticas](#-reportes-y-estadísticas)
- [Seguridad](#-seguridad)
- [Contribución](#-contribución)
- [Licencia](#-licencia)
- [Contacto](#-contacto)

## ✨ Características

### 🎯 Funcionalidades Principales

- **👤 Sistema de Autenticación**: Login seguro con sesiones de usuario
- **📦 Gestión de Productos**: CRUD completo para productos con imágenes
- **📊 Control de Inventario**: Seguimiento de entradas y salidas
- **💰 Sistema de Ventas**: Ventas en efectivo y a crédito
- **👥 Gestión de Clientes**: Información de clientes en movimientos
- **📈 Estadísticas Avanzadas**: Métricas financieras y reportes PDF
- **🔍 Búsqueda y Filtros**: Búsqueda avanzada en todas las secciones
- **📱 Interfaz Responsiva**: Diseño moderno con Tailwind CSS

### 🛠️ Operaciones CRUD

- **Productos**: Crear, leer, actualizar y eliminar productos
- **Movimientos**: Control completo de entradas y salidas de inventario
- **Ventas**: Registro de ventas con seguimiento de pagos
- **Usuarios**: Gestión de usuarios administradores

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.2+**: Lenguaje de servidor principal
- **MySQL 8.0+**: Base de datos relacional
- **PDO**: Extensión para acceso a bases de datos

### Frontend
- **HTML5**: Estructura semántica
- **Tailwind CSS**: Framework CSS utilitario
- **JavaScript ES6+**: Interactividad del lado cliente
- **Anime.js**: Animaciones suaves y modernas

### Librerías y Herramientas
- **TCPDF**: Generación de reportes PDF
- **Particles.js**: Efectos visuales de fondo
- **Font Awesome**: Iconografía moderna
- **Google Fonts**: Tipografía personalizada

## 💻 Requisitos del Sistema

### Servidor
- **PHP 8.2** o superior
- **MySQL 8.0** o superior
- **Apache/Nginx** con mod_rewrite
- **Composer** (opcional para dependencias)

### Navegadores Soportados
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Hardware Mínimo
- **RAM**: 512MB
- **Almacenamiento**: 100MB
- **Procesador**: 1GHz

## 🚀 Instalación

### 1. Clonar el Repositorio
```bash
git clone https://github.com/Salvaberticci/Montolis.git
cd Montolis
```

### 2. Configurar el Entorno
```bash
# Crear directorio para uploads
mkdir uploads
chmod 755 uploads

# Configurar permisos
chmod 755 config/
chmod 644 config/database.php
```

### 3. Configurar la Base de Datos

#### Opción A: Usar el Script de Migración
```sql
-- Ejecutar el archivo database_migration.sql en MySQL
source database_migration.sql;
```

#### Opción B: Configuración Manual
```sql
-- Crear base de datos
CREATE DATABASE montolis_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Ejecutar las migraciones en orden:
-- 1. montolis_inventory.sql (productos existentes)
-- 2. database_migration.sql (nuevas características)
```

### 4. Configurar Conexión a Base de Datos

Editar `config/database.php`:
```php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "montolis_inventory";
    private $username = "tu_usuario";
    private $password = "tu_contraseña";
    // ...
}
```

### 5. Acceder al Sistema
```
URL: http://localhost/Montolis/
Usuario por defecto: admin
Contraseña: admin123
```

## ⚙️ Configuración

### Variables de Entorno
```php
// config/database.php
private $host = "localhost";          // Host de la base de datos
private $db_name = "montolis_inventory"; // Nombre de la base de datos
private $username = "root";           // Usuario de MySQL
private $password = "";               // Contraseña de MySQL
```

### Configuración de Uploads
```php
// Tamaño máximo de archivos (en bytes)
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');
```

## 📖 Uso del Sistema

### 👤 Acceso al Sistema

1. **Login**: Accede con usuario `admin` y contraseña `admin123`
2. **Dashboard**: Vista general del sistema
3. **Navegación**: Menú lateral con todas las secciones

### 📦 Gestión de Productos

#### Agregar Producto
1. Ir a "Añadir Producto"
2. Completar formulario:
   - Nombre, descripción, cantidad
   - Costo, precio de venta
   - Precios para terceros
   - Imagen del producto

#### Editar/Eliminar Productos
- Usar botones en la tabla del dashboard
- Confirmación requerida para eliminación

### 📊 Movimientos de Inventario

#### Registrar Movimiento
1. Ir a "Movimientos"
2. Seleccionar producto y tipo (Entrada/Salida)
3. Especificar cantidad y razón
4. Agregar datos de cliente (opcional)

#### Gestionar Movimientos
- **Editar**: Botón azul con ícono de lápiz
- **Eliminar**: Botón rojo con ícono de papelera
- **Filtrar**: Por producto, tipo, fechas, cliente

### 💰 Sistema de Ventas

#### Registrar Venta
1. Ir a "Ventas" → "Añadir Venta"
2. Seleccionar producto y cantidad
3. Elegir tipo de pago (Efectivo/Crédito)
4. Para crédito: seguimiento automático del saldo

#### Seguimiento de Pagos
- Estado de pago visible en tablas
- Saldos pendientes para ventas a crédito
- Historial completo de transacciones

### 📈 Estadísticas y Reportes

#### Ver Estadísticas
1. Ir a "Estadísticas"
2. Ver métricas financieras:
   - Dinero invertido
   - Ganancias totales
   - Valor de ventas
   - Valor del stock

#### Generar Reportes PDF
1. En "Estadísticas" hacer clic en "Generar Reporte PDF"
2. Descargar reporte completo con:
   - Resumen financiero
   - Detalles de movimientos
   - Información de productos

## 📁 Estructura del Proyecto

```
Montolis/
├── 📁 config/              # Configuración del sistema
│   └── database.php       # Conexión a base de datos
├── 📁 css/                # Hojas de estilo
│   └── style.css         # Estilos personalizados
├── 📁 images/            # Recursos gráficos
│   └── logo.png          # Logo del sistema
├── 📁 libs/              # Librerías externas
│   └── TCPDF-main/       # Generador de PDFs
├── 📁 objects/           # Clases del sistema
│   ├── product.php       # Gestión de productos
│   ├── sale.php          # Gestión de ventas
│   ├── movement.php      # Gestión de movimientos
│   └── user.php          # Gestión de usuarios
├── 📁 uploads/           # Archivos subidos
├── 📄 *.php              # Páginas del sistema
├── database_migration.sql # Migración de BD
└── README.md            # Esta documentación
```

## 🗄️ Base de Datos

### Tablas Principales

#### `products` - Productos
```sql
- id: INT (Primary Key)
- name: VARCHAR(128)
- description: TEXT
- quantity: INT
- product_cost: DECIMAL(10,2)
- sale_price: DECIMAL(10,2)
- third_party_sale_price: DECIMAL(10,2)
- third_party_seller_percentage: DECIMAL(5,2)
- image: VARCHAR(512)
```

#### `inventory_movements` - Movimientos
```sql
- id: INT (Primary Key)
- product_id: INT (Foreign Key)
- type: ENUM('entry', 'exit')
- quantity: INT
- reason: TEXT
- client_name: VARCHAR(100)
- client_contact: VARCHAR(100)
- date: TIMESTAMP
```

#### `sales` - Ventas
```sql
- id: INT (Primary Key)
- product_id: INT (Foreign Key)
- quantity_sold: INT
- sale_price: DECIMAL(10,2)
- sale_type: ENUM('direct', 'third_party')
- payment_type: ENUM('cash', 'credit')
- payment_status: ENUM('paid', 'pending', 'partial')
- remaining_balance: DECIMAL(10,2)
- sale_date: TIMESTAMP
```

#### `users` - Usuarios
```sql
- id: INT (Primary Key)
- username: VARCHAR(50) (Unique)
- password_hash: VARCHAR(255)
- email: VARCHAR(100) (Unique)
- role: ENUM('admin', 'user')
- created_at: TIMESTAMP
```

## 🔧 API y Clases

### Clase Product
```php
$product = new Product($db);
$product->create();    // Crear producto
$product->read();      // Leer productos
$product->update();    // Actualizar producto
$product->delete();    // Eliminar producto
$product->search();    // Buscar productos
```

### Clase Movement
```php
$movement = new Movement($db);
$movement->create();   // Registrar movimiento
$movement->read();     // Leer movimientos con filtros
$movement->readOne();  // Leer movimiento específico
$movement->update();   // Actualizar movimiento
$movement->delete();   // Eliminar movimiento
```

### Clase Sale
```php
$sale = new Sale($db);
$sale->create();       // Registrar venta
$sale->read();         // Leer ventas
```

### Clase User
```php
$user = new User($db);
$user->authenticate(); // Autenticar usuario
$user->create();       // Crear usuario
$user->update();       // Actualizar usuario
$user->changePassword(); // Cambiar contraseña
```

## 🎯 Características Avanzadas

### 🔐 Sistema de Autenticación
- **Sesiones seguras** con PHP Sessions
- **Hash de contraseñas** con bcrypt
- **Protección CSRF** implícita
- **Timeouts de sesión** automáticos

### 💰 Sistema Financiero
- **Cálculo automático** de ganancias
- **Seguimiento de costos** vs precios de venta
- **Margen de ganancia** en tiempo real
- **Valor del inventario** actualizado

### 📊 Estadísticas Inteligentes
- **Métricas financieras** completas
- **Gráficos interactivos** con Chart.js
- **Filtros por fecha** y tipo
- **Exportación a PDF** profesional

### 🎨 Interfaz Moderna
- **Diseño responsivo** para móviles y desktop
- **Animaciones suaves** con Anime.js
- **Efectos visuales** con Particles.js
- **Paleta de colores** consistente

## 📊 Reportes y Estadísticas

### Métricas Disponibles
- **Dinero Invertido**: Costo total de productos en inventario
- **Ganancias Totales**: Beneficio neto de todas las ventas
- **Valor de Ventas**: Ingresos totales generados
- **Valor del Stock**: Valor potencial de venta del inventario
- **Total Movimientos**: Número de registros de entrada/salida

### Reportes PDF
- **Formato profesional** con encabezados
- **Resumen financiero** detallado
- **Tablas organizadas** con datos
- **Información de contacto** incluida

## 🔒 Seguridad

### Medidas Implementadas
- **Validación de entrada** en todos los formularios
- **Sanitización de datos** con htmlspecialchars
- **Prevención de SQL Injection** con PDO prepared statements
- **Protección XSS** en outputs
- **Autenticación requerida** para áreas administrativas
- **Sesiones seguras** con regeneración de IDs

### Mejores Prácticas
- **Contraseñas hasheadas** con password_hash()
- **Validación del lado servidor** para todos los datos
- **Manejo seguro de archivos** subidos
- **Logs de errores** (no expuestos al usuario)

## 🤝 Contribución

### Cómo Contribuir
1. **Fork** el proyecto
2. **Crear** una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. **Abrir** un Pull Request

### Guías de Contribución
- Seguir estándares PSR-12 para PHP
- Usar commits descriptivos en inglés
- Documentar nuevas funciones
- Probar cambios antes de enviar PR

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Contacto

**Desarrollador**: Salvador Berticci  
**Email**: salvaberticci@gmail.com  
**GitHub**: [@Salvaberticci](https://github.com/Salvaberticci)  
**LinkedIn**: [Tu LinkedIn]

### Soporte
Para soporte técnico o preguntas:
1. Revisar la documentación en este README
2. Abrir un issue en GitHub
3. Contactar directamente al desarrollador

---

⭐ **Si te gusta este proyecto, ¡dale una estrella en GitHub!**

Desarrollado con ❤️ para la comunidad de emprendedores y pequeños negocios.