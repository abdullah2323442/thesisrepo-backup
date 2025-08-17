# Qodo Configuration Backups

This directory contains backup copies of your Qodo configuration files to ensure permanent initialization.

## Backup Strategy

- **Frequency**: Weekly automatic backups
- **Retention**: Keep last 5 backups
- **Files Backed Up**:
  - config.json
  - rules.json
  - templates.json
  - ignore.txt
  - permanent-config.json

## Restoration

If any configuration files are lost or corrupted, you can restore from the most recent backup:

1. Copy the backup files from this directory
2. Replace the corrupted files in the `.qodo/` directory
3. Run the initialization verification script

## Manual Backup

To create a manual backup:
```bash
cp .qodo/config.json .qodo/backups/config-$(date +%Y%m%d).json
cp .qodo/rules.json .qodo/backups/rules-$(date +%Y%m%d).json
cp .qodo/templates.json .qodo/backups/templates-$(date +%Y%m%d).json
```

## Backup Verification

Each backup includes a timestamp and checksum to verify integrity.