#!/bin/bash

PAYLOAD='{"commit": "HEAD", "branch": "master", "message": "Triggered From omise-woocommerce Plugin"}'
curl -H "Authorization: Bearer $1" -X POST "https://api.buildkite.com/v2/organizations/omise/pipelines/docker-omise-woocommerce/builds" -d "$PAYLOAD"
