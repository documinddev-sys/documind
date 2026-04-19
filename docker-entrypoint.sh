#!/bin/bash
set -e

# Use PORT environment variable if set, otherwise default to 8080
PORT=${PORT:-8080}

# Update Apache configuration to listen on the specified port
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf

# Start Apache in foreground
apache2-foreground
