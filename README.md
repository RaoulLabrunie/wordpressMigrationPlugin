# WordPress Complete Backup

![WordPress Version](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.2%2B-green)
![License](https://img.shields.io/badge/License-GPL%20v2-orange)

A simple but powerful WordPress plugin to create complete backups of your site for migration or backup purposes. Designed to be used even in environments with server restrictions. NOT READY FOR PRODUCTION.

## 🌟 Features

- ✅ **Complete backup** - Backs up the database and all `wp-content` files
- ✅ **No external dependencies** - Doesn't require mysqldump or external tools
- ✅ **Compatible with all environments** - Works even on shared hosts with limitations
- ✅ **Easy migration** - Ideal for migrating WordPress between servers
- ✅ **Direct download URL** - Compatible with wget for automation
- ✅ **Intuitive interface** - Simple and easy-to-use admin panel
- ✅ **Backup listing** - Manage all your backups from a single interface
- ✅ **File protection** - Generates .htaccess files to protect your backups

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- ZipArchive extension enabled in PHP
- Write permissions on the wp-content/uploads folder

## 🚀 Installation

1. Download the zip file from this repository
2. Go to WordPress Admin > Plugins > Add new > Upload plugin
3. Select the downloaded zip file and install it
4. Activate the plugin

Alternatively, you can install the plugin manually:

1. Download and unzip the zip file
2. Copy the `backup-wordpress-completo` folder to your `/wp-content/plugins/` directory
3. Activate the plugin from the 'Plugins' menu in WordPress

## 📝 Usage

### Creating a backup

1. Go to WordPress Admin > Backup WordPress
2. Click the "Create Complete Backup" button
3. Wait for the process to complete
4. Download the backup file or copy the URL to use with wget

### Restoring a backup

1. Unzip the main downloaded zip file
2. Import the `database.sql` file to your new database:
   ```bash
   mysql -u username -p database_name < database.sql
   ```
   Or use phpMyAdmin to import it
3. Unzip `wp-content.zip` in the root of your WordPress:
   ```bash
   unzip wp-content.zip -d /path/to/your/wordpress/
   ```
4. Update the wp-config.php file with the new connection data if necessary

### Automated migration

You can use the following bash script to automate the migration to a new server:

```bash
#!/bin/bash

# Configuration
BACKUP_URL="https://your-site.com/wp-content/uploads/backups-wp/backup_completo_YYYYMMDD_HHMMSS.zip"
LOCAL_ZIP="wordpress_backup.zip"

# Download backup
wget -O "$LOCAL_ZIP" "$BACKUP_URL"

# Extract files
unzip "$LOCAL_ZIP"

# Restore database
mysql -u username -p'password' database_name < database.sql

# Restore files
unzip wp-content.zip -d /path/to/your/wordpress/

echo "Migration completed successfully!"
```

## 📊 What the backup contains

### Database (database.sql)

- All pages and posts
- All site configuration
- Users and roles
- Plugin settings
- Menus, widgets, and settings

### Files (wp-content.zip)

- All installed themes
- All installed plugins
- All media files
- Any custom files in wp-content

## 🛠️ Troubleshooting

### Error during database restoration

If you encounter an error related to default values during database import:

```
ERROR 1067 (42000): Invalid default value for 'comment_date'
```

Use this command to import with modified SQL_MODE configuration:

```bash
mysql -u username -p --init-command="SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';" database < database.sql
```

### "ZipArchive not available" error

This error occurs when the ZipArchive extension is not enabled on your server. Contact your hosting provider to enable this extension.

## 🔄 Changelog

### 1.0.0

- Initial release

## 📜 License

This plugin is licensed under [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).