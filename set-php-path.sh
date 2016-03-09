#!/bin/bash

# Add Homebrew to the path as a convenience for the user
export PATH="/usr/local/bin:${PATH}"

php_version_line="$(php --version | head -n 1)"
# Shell-agnostic string contains
if [ "${php_version_line#*PHP 5.3}" != "${php_version_line}" ]; then
    echo "Error: PHP version must be >= 5.4" >&2
    exit 1
fi
