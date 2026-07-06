#!/bin/bash
# SSL Certificate Combination Script for HAProxy

# Place this script in the same directory as your SSL files and run it.
# It will generate the 130_nhmp_gov_pk.pem file required by HAProxy.

echo "Combining SSL certificates into HAProxy PEM format..."

# Ensure we are combining them in the exact required order:
# 1. Domain Certificate
# 2. Intermediate CA
# 3. Root CAs
# 4. Private Key

cat 130_nhmp_gov_pk.crt \
    GoGetSSL_RSA_DV_CA.crt \
    USERTrust_RSA_Certification_Authority.crt \
    AAA_Certificate_Services.crt \
    130.nhmp.gov.pk.key > 130_nhmp_gov_pk.pem

echo "Successfully created 130_nhmp_gov_pk.pem!"
echo "Now, move this file to your HAProxy certs directory:"
echo "mv 130_nhmp_gov_pk.pem /home/mrpirzado/projects/130/haproxy/certs/"
echo "Then, restart your docker containers:"
echo "cd /home/mrpirzado/projects/130 && make restart"
