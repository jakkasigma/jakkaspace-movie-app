#!/bin/bash
# Make sure this file has executable permissions, run `chmod +x railway/run-reverb.sh`

# This command runs the Reverb WebSocket server.
php artisan reverb:start --host=0.0.0.0 --port="${PORT:-8080}" --no-interaction
