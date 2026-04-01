#!/bin/bash

# MindPlay Automated Backup Script
# Description: Creates compressed backups of database and application files
# Usage: ./backup.sh
# Cron: 0 2 * * * /var/www/mindplay/scripts/backup.sh

# Configuration
APP_DIR="/var/www/mindplay"
BACKUP_DIR="/var/backups/mindplay"
DB_NAME="mindplay_production"
DB_USER="mindplay_user"
DB_PASS="your_password_here"  # Update with actual password or use .my.cnf
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR/database"
mkdir -p "$BACKUP_DIR/files"

# Backup database
echo "Starting database backup..."
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/database/mindplay_db_$DATE.sql.gz"

if [ $? -eq 0 ]; then
    echo "Database backup completed: mindplay_db_$DATE.sql.gz"
else
    echo "Database backup failed!"
    exit 1
fi

# Backup application files (excluding logs and vendor)
echo "Starting files backup..."
tar -czf "$BACKUP_DIR/files/mindplay_files_$DATE.tar.gz" \
    --exclude='logs/*' \
    --exclude='vendor/*' \
    --exclude='node_modules/*' \
    --exclude='.git/*' \
    -C /var/www mindplay

if [ $? -eq 0 ]; then
    echo "Files backup completed: mindplay_files_$DATE.tar.gz"
else
    echo "Files backup failed!"
    exit 1
fi

# Clean up old backups (keep only last N days)
echo "Cleaning up old backups (keeping last $RETENTION_DAYS days)..."
find "$BACKUP_DIR/database" -name "mindplay_db_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR/files" -name "mindplay_files_*.tar.gz" -mtime +$RETENTION_DAYS -delete

# Calculate backup sizes
DB_SIZE=$(du -h "$BACKUP_DIR/database/mindplay_db_$DATE.sql.gz" | cut -f1)
FILES_SIZE=$(du -h "$BACKUP_DIR/files/mindplay_files_$DATE.tar.gz" | cut -f1)

echo "===================="
echo "Backup Summary"
echo "===================="
echo "Date: $(date)"
echo "Database backup: $DB_SIZE"
echo "Files backup: $FILES_SIZE"
echo "Total backups: $(ls -l $BACKUP_DIR/database/*.sql.gz 2>/dev/null | wc -l) databases, $(ls -l $BACKUP_DIR/files/*.tar.gz 2>/dev/null | wc -l) file archives"
echo "===================="

# Optional: Send email notification (requires mailutils)
# echo "Backup completed successfully" | mail -s "MindPlay Backup - $DATE" admin@mindplay.ct.ws

exit 0
