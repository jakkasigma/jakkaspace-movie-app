#!/bin/bash
# Make sure this file has executable permissions, run `chmod +x railway/run-worker.sh`

# This command runs the queue worker.
php artisan queue:work --tries=3 --sleep=3 --max-time=3600
