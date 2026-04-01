#!/bin/bash

# MindPlay Restore Script
# Description: Restores database and files from backup
# Usage: ./restore.sh YYYYMMDD_HHMMSS

if [ -z "$1" ]; then
    echo "Usage: ./restore.sh YYYYMMDD_HHMMSS"
    echo "Example: ./restore.sh 20240115_020000"
    echo ""
    echo "Available backups:"
    ls -lh /var/backups/mindplay/database/*.sql.gz 2>/dev/null | awk '{print $9}' | xargs -n1 basename
    exit 1
fi

BACKUP_DATE=$1
BACKUP_DIR="/var/backups/mindplay"
APP_DIR="/var/www/mindplay"
DB_NAME="mindplay_production"
DB_USER="mindplay_user"
DB_PASS="your_password_here"

DB_BACKUP="$BACKUP_DIR/database/mindplay_db_$BACKUP_DATE.sql.gz"
FILES_BACKUP="$BACKUP_DIR/files/mindplay_files_$BACKUP_DATE.tar.gz"

# Check if backups exist
if [ ! -f "$DB_BACKUP" ]; then
    echo "Error: Database backup not found: $DB_BACKUP"
    exit 1
fi

if [ ! -f "$FILES_BACKUP" ]; then
    echo "Error: Files backup not found: $FILES_BACKUP"
    exit 1
fi

# Confirmation prompt
echo "WARNING: This will restore database and files from backup dated $BACKUP_DATE"
echo "Current data will be overwritten!"
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

# Restore database
echo "Restoring database..."
gunzip < "$DB_BACKUP" | mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"

if [ $? -eq 0 ]; then
    echo "Database restored successfully"
else
    echo "Database restore failed!"
    exit 1
fi

# Restore files
echo "Restoring application files..."
cd /var/www
tar -xzf "$FILES_BACKUP"

if [ $? -eq 0 ]; then
    echo "Files restored successfully"
else
    echo "Files restore failed!"
    exit 1
fi

# Set proper permissions
echo "Setting file permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod 755 "$APP_DIR"
chmod 600 "$APP_DIR/.env"

echo "===================="
echo "Restore completed successfully!"
echo "Date: $(date)"
echo "Restored from: $BACKUP_DATE"
echo "===================="

exit 0
