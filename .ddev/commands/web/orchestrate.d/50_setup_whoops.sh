#!/bin/bash

pushd "${DDEV_DOCROOT}"

flags="--activate"
if [ "${WP_MULTISITE}" = "true" ]; then
  flags+=" --network"
fi


wp plugin install https://github.com/Rarst/wps/releases/latest/download/wps.zip $flags
