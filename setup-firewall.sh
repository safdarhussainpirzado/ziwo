#!/bin/bash
# 🛡️ Firewall Hardening Script (Phase 6)
# This script configures UFW to only allow necessary HA traffic.

set -e

echo "🔒 Configuring UFW Firewall..."

# Default policy
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (Critical!)
sudo ufw allow ssh

# Allow HAProxy Traffic
sudo ufw allow 7085/tcp
sudo ufw allow 8085/tcp

# Allow Monitoring (Internal only - adjust as needed)
sudo ufw allow 3000/tcp # Grafana
sudo ufw allow 9090/tcp # Prometheus

# Allow HAProxy Stats (Internal)
sudo ufw allow 8404/tcp

echo "🚀 Enabling Firewall..."
sudo ufw --force enable

echo "✅ Firewall is active and hardened."
sudo ufw status verbose
