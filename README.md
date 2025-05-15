### NO USAR EN PRODUCCION

# WordPress Complete Backup

![WordPress Version](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.2%2B-green)
![License](https://img.shields.io/badge/License-GPL%20v2-orange)

Un plugin de WordPress simple pero potente para realizar copias de seguridad completas de tu sitio para migración o respaldo. Diseñado para ser utilizado incluso en entornos con restricciones de servidor.

## 🌟 Características

- ✅ **Copia de seguridad completa** - Respalda la base de datos y todos los archivos de `wp-content`
- ✅ **Sin dependencias externas** - No requiere mysqldump ni herramientas externas
- ✅ **Compatible con all entornos** - Funciona incluso en hosts compartidos con limitaciones
- ✅ **Migración fácil** - Ideal para migrar WordPress entre servidores
- ✅ **URL de descarga directa** - Compatible con wget para automatización
- ✅ **Interfaz intuitiva** - Panel de administración simple y fácil de usar
- ✅ **Listado de backups** - Gestiona todos tus respaldos desde una única interfaz
- ✅ **Protección de archivos** - Genera archivos .htaccess para proteger tus backups

## 📋 Requisitos

- WordPress 5.0 o superior
- PHP 7.2 o superior
- Extensión ZipArchive habilitada en PHP
- Permisos de escritura en la carpeta wp-content/uploads

## 🚀 Instalación

1. Descarga el archivo zip de este repositorio
2. Ve a tu WordPress Admin > Plugins > Añadir nuevo > Subir plugin
3. Selecciona el archivo zip descargado e instálalo
4. Activa el plugin

Alternativamente, puedes instalar el plugin manualmente:

1. Descarga y descomprime el archivo zip
2. Copia la carpeta `backup-wordpress-completo` a tu directorio `/wp-content/plugins/`
3. Activa el plugin desde el menú 'Plugins' en WordPress

## 📝 Uso

### Crear una copia de seguridad

1. Ve a WordPress Admin > Backup WordPress
2. Haz clic en el botón "Crear Backup Completo"
3. Espera a que se complete el proceso
4. Descarga el archivo de respaldo o copia la URL para usar con wget

### Restaurar una copia de seguridad

1. Descomprime el archivo zip principal descargado
2. Importa el archivo `database.sql` a tu nueva base de datos:
   ```bash
   mysql -u usuario -p nombre_base_de_datos < database.sql
   ```
   O usa phpMyAdmin para importarlo
3. Descomprime `wp-content.zip` en la raíz de tu WordPress:
   ```bash
   unzip wp-content.zip -d /ruta/a/tu/wordpress/
   ```
4. Actualiza el archivo wp-config.php con los nuevos datos de conexión si es necesario

### Migración automatizada

Puedes utilizar el siguiente script bash para automatizar la migración a un nuevo servidor:

```bash
#!/bin/bash

# Configuración
BACKUP_URL="https://tu-sitio.com/wp-content/uploads/backups-wp/backup_completo_YYYYMMDD_HHMMSS.zip"
LOCAL_ZIP="wordpress_backup.zip"

# Descargar backup
wget -O "$LOCAL_ZIP" "$BACKUP_URL"

# Extraer archivos
unzip "$LOCAL_ZIP"

# Restaurar base de datos
mysql -u usuario -p'contraseña' nombre_base_de_datos < database.sql

# Restaurar archivos
unzip wp-content.zip -d /ruta/a/tu/wordpress/

echo "Migración completada con éxito!"
```

## 📊 Qué contiene el backup

### Base de datos (database.sql)

- Todas las páginas y entradas
- Toda la configuración del sitio
- Usuarios y roles
- Configuración de plugins
- Menús, widgets y ajustes

### Archivos (wp-content.zip)

- Todos los temas instalados
- Todos los plugins instalados
- Todos los archivos multimedia
- Cualquier archivo personalizado en wp-content

## 🛠️ Solución de problemas

### Error durante la restauración de la base de datos

Si encuentras un error relacionado con valores por defecto durante la importación de la base de datos:

```
ERROR 1067 (42000): Invalid default value for 'comment_date'
```

Usa este comando para importar con configuración SQL_MODE modificada:

```bash
mysql -u usuario -p --init-command="SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';" base_de_datos < database.sql
```

### Error "ZipArchive no disponible"

Este error ocurre cuando la extensión ZipArchive no está habilitada en tu servidor. Contacta a tu proveedor de hosting para habilitar esta extensión.

## 🔄 Changelog

### 1.0.0

- Lanzamiento inicial

## 📜 Licencia

Este plugin está licenciado bajo [GPL v2 o posterior](https://www.gnu.org/licenses/gpl-2.0.html).
