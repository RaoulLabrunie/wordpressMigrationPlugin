<?php
/*
Plugin Name: Backup WordPress Completo
Description: Realiza una copia de seguridad completa de WordPress (base de datos y archivos) para migración fácil con wget
Version: 1.0
Author: Plugin de Backup
*/

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Agregar menú en el panel de administración
add_action('admin_menu', 'backup_wp_completo_menu');
function backup_wp_completo_menu() {
    add_menu_page(
        'Backup WordPress Completo', 
        'Backup WordPress', 
        'manage_options', 
        'backup-wp-completo', 
        'backup_wp_completo_pagina',
        'dashicons-backup'
    );
}

// Página del plugin en el admin
function backup_wp_completo_pagina() {
    // Procesar la solicitud de backup
    if (isset($_POST['hacer_backup'])) {
        $resultado = backup_wp_completo_realizar();
        if ($resultado['exito']) {
            echo "<div class='notice notice-success is-dismissible'><p>¡Backup realizado con éxito! <a href='" . esc_url($resultado['url']) . "' target='_blank'>Descargar backup</a></p></div>";
        } else {
            echo "<div class='notice notice-error is-dismissible'><p>Error al realizar el backup: " . esc_html($resultado['mensaje']) . "</p></div>";
        }
    }
    
    // Mostrar la página del admin
    ?>
    <div class="wrap">
        <h1>Backup WordPress Completo</h1>
        <p>Este plugin genera un archivo ZIP con una copia completa de tu WordPress para migración:</p>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li>Base de datos completa (exportada en SQL)</li>
            <li>Carpeta wp-content (temas, plugins y uploads)</li>
        </ul>
        <p>El archivo resultante puede ser descargado directamente o mediante wget.</p>
        
        <form method="post">
            <?php wp_nonce_field('backup_wp_action', 'backup_wp_nonce'); ?>
            <input type="submit" name="hacer_backup" class="button button-primary" value="Crear Backup Completo">
        </form>
        
        <div style="margin-top: 20px;">
            <h2>Backups anteriores</h2>
            <?php mostrar_backups_anteriores(); ?>
        </div>
    </div>
    <?php
}

// Función para mostrar backups anteriores
function mostrar_backups_anteriores() {
    $upload_dir = wp_upload_dir();
    $backup_dir = $upload_dir['basedir'] . '/backups-wp';
    
    if (!file_exists($backup_dir)) {
        echo "<p>No hay backups disponibles.</p>";
        return;
    }
    
    $files = glob($backup_dir . '/backup_completo_*.zip');
    
    if (empty($files)) {
        echo "<p>No hay backups disponibles.</p>";
        return;
    }
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Fecha</th><th>Tamaño</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';
    
    rsort($files); // Ordenar por fecha (más reciente primero)
    
    foreach ($files as $file) {
        $filename = basename($file);
        $fecha_backup = preg_replace('/backup_completo_(\d{8}_\d{6})\.zip/', '$1', $filename);
        $fecha_formateada = date('d/m/Y H:i:s', strtotime(str_replace('_', ' ', $fecha_backup)));
        $filesize = size_format(filesize($file));
        $download_url = $upload_dir['baseurl'] . '/backups-wp/' . $filename;
        
        echo '<tr>';
        echo '<td>' . esc_html($fecha_formateada) . '</td>';
        echo '<td>' . esc_html($filesize) . '</td>';
        echo '<td>';
        echo '<a href="' . esc_url($download_url) . '" class="button button-secondary">Descargar</a> ';
        echo '<button class="button" onclick="navigator.clipboard.writeText(\'' . esc_url($download_url) . '\');alert(\'URL copiada al portapapeles\');">Copiar URL</button>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    
    // Mostrar comando wget
    echo '<div style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-left: 4px solid #0073aa;">';
    echo '<h3 style="margin-top: 0;">Comando wget para descarga</h3>';
    echo '<p>Para descargar el último backup mediante línea de comandos:</p>';
    echo '<code>wget ' . esc_url($download_url) . '</code>';
    echo '</div>';
}

// Función principal para realizar el backup
function backup_wp_completo_realizar() {
    // Verificar nonce por seguridad
    if (!isset($_POST['backup_wp_nonce']) || !wp_verify_nonce($_POST['backup_wp_nonce'], 'backup_wp_action')) {
        return array(
            'exito' => false,
            'mensaje' => 'Error de verificación de seguridad.'
        );
    }
    
    // Definir directorio de backup
    $upload_dir = wp_upload_dir();
    $backup_dir = $upload_dir['basedir'] . '/backups-wp';
    
    // Crear directorio de backups si no existe
    if (!file_exists($backup_dir)) {
        if (!wp_mkdir_p($backup_dir)) {
            return array(
                'exito' => false,
                'mensaje' => 'No se pudo crear el directorio de backups.'
            );
        }
        
        // Crear archivo .htaccess para proteger los backups
        file_put_contents($backup_dir . '/.htaccess', 'Options -Indexes' . PHP_EOL);
    }
    
    // Definir nombre de archivos con timestamp
    $fecha = date("Ymd_His");
    $archivo_sql = $backup_dir . "/db_backup_$fecha.sql";
    $archivo_zip_contenido = $backup_dir . "/wp_content_$fecha.zip";
    $archivo_zip_final = $backup_dir . "/backup_completo_$fecha.zip";
    
    // 1. Exportar base de datos (usando PHP puro, sin mysqldump)
    if (!backup_bd_a_sql($archivo_sql)) {
        return array(
            'exito' => false,
            'mensaje' => 'Error al exportar la base de datos.'
        );
    }
    
    // 2. Comprimir carpeta wp-content
    if (!comprimir_wp_content($archivo_zip_contenido)) {
        return array(
            'exito' => false,
            'mensaje' => 'Error al comprimir la carpeta wp-content.'
        );
    }
    
    // 3. Crear ZIP final con ambos archivos
    if (!crear_zip_final($archivo_zip_final, $archivo_sql, $archivo_zip_contenido)) {
        return array(
            'exito' => false,
            'mensaje' => 'Error al crear el archivo ZIP final.'
        );
    }
    
    // 4. Eliminar archivos temporales
    @unlink($archivo_sql);
    @unlink($archivo_zip_contenido);
    
    // Retornar éxito y URL de descarga
    return array(
        'exito' => true,
        'url' => $upload_dir['baseurl'] . '/backups-wp/' . basename($archivo_zip_final)
    );
}

// Función para exportar la base de datos a un archivo SQL
function backup_bd_a_sql($archivo_sql) {
    global $wpdb;
    
    $handle = fopen($archivo_sql, 'w');
    if (!$handle) {
        return false;
    }
    
    // Escribir cabecera del archivo SQL
    $header = "-- WordPress Database Backup\n";
    $header .= "-- Generado: " . date("Y-m-d H:i:s") . "\n";
    $header .= "-- Host: " . DB_HOST . "\n";
    $header .= "-- Base de datos: " . DB_NAME . "\n";
    $header .= "-- --------------------------------------------------------\n\n";
    $header .= "/*!40101 SET NAMES utf8 */;\n\n";
    
    fwrite($handle, $header);
    
    // Obtener todas las tablas
    $tablas = $wpdb->get_results("SHOW TABLES", ARRAY_N);
    
    // Procesar cada tabla
    foreach ($tablas as $tabla) {
        $nombre_tabla = $tabla[0];
        
        // Escribir estructura de la tabla
        fwrite($handle, "-- \n-- Estructura de la tabla `$nombre_tabla`\n-- \n\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$nombre_tabla`;\n");
        
        $crear_tabla = $wpdb->get_row("SHOW CREATE TABLE `$nombre_tabla`", ARRAY_N);
        fwrite($handle, $crear_tabla[1] . ";\n\n");
        
        // Escribir datos de la tabla
        fwrite($handle, "-- \n-- Volcado de datos para la tabla `$nombre_tabla`\n-- \n\n");
        
        // Obtener los datos en lotes para evitar problemas de memoria
        $offset = 0;
        $limit = 500;
        
        do {
            $rows = $wpdb->get_results("SELECT * FROM `$nombre_tabla` LIMIT $offset, $limit", ARRAY_A);
            $offset += $limit;
            
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $values = array();
                    
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . esc_sql($value) . "'";
                        }
                    }
                    
                    fwrite($handle, "INSERT INTO `$nombre_tabla` VALUES (" . implode(', ', $values) . ");\n");
                }
            }
        } while (!empty($rows));
        
        fwrite($handle, "\n\n");
    }
    
    fclose($handle);
    return true;
}

// Función para comprimir la carpeta wp-content
function comprimir_wp_content($archivo_zip) {
    if (!class_exists('ZipArchive')) {
        return false;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($archivo_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }
    
    $source = WP_CONTENT_DIR;
    $source_dir = realpath($source);
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        // Saltar directorios (solo añadir archivos)
        if ($file->isDir()) {
            continue;
        }
        
        // Obtener la ruta real del archivo
        $real_path = $file->getRealPath();
        
        // Saltar archivos grandes mayores a 50MB para evitar problemas
        if (filesize($real_path) > 50 * 1024 * 1024) {
            continue;
        }
        
        // Calcular la ruta relativa para mantener la estructura de directorios
        $relative_path = substr($real_path, strlen($source_dir) + 1);
        
        // Añadir archivo al ZIP
        $zip->addFile($real_path, 'wp-content/' . $relative_path);
    }
    
    $zip->close();
    return true;
}

// Función para crear el ZIP final que contiene la BD y wp-content
function crear_zip_final($archivo_zip_final, $archivo_sql, $archivo_zip_contenido) {
    if (!class_exists('ZipArchive')) {
        return false;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($archivo_zip_final, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }
    
    // Añadir archivo SQL
    $zip->addFile($archivo_sql, 'database.sql');
    
    // Añadir ZIP de wp-content (esto incluirá el ZIP como un archivo, no su contenido)
    $zip->addFile($archivo_zip_contenido, 'wp-content.zip');
    
    // Crear un archivo README con instrucciones
    $readme = "INSTRUCCIONES DE RESTAURACIÓN\n\n";
    $readme .= "1. Descomprime este archivo ZIP principal\n";
    $readme .= "2. Importa el archivo 'database.sql' a tu nueva base de datos\n";
    $readme .= "3. Descomprime 'wp-content.zip' en la raíz de tu nueva instalación WordPress\n";
    $readme .= "4. Actualiza el archivo wp-config.php con los nuevos datos de conexión a la BD\n";
    $readme .= "5. ¡Listo! Tu WordPress debería estar funcionando correctamente\n\n";
    $readme .= "Nota: Es posible que necesites actualizar las URLs de la base de datos si el dominio ha cambiado.\n";
    
    $zip->addFromString('README.txt', $readme);
    
    $zip->close();
    return true;
}