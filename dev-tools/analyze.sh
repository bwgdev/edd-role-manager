#!/bin/bash
#
# Run PHPCS and PHPStan analysis
#
# Usage: ./dev-tools/analyze.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Change to project root
cd "$(dirname "$0")/.."

echo -e "${YELLOW}Running code analysis...${NC}\n"

# Check if vendor directory exists
if [ ! -d "plugin/vendor" ]; then
    echo -e "${RED}Error: vendor directory not found. Run 'composer install' first.${NC}"
    exit 1
fi

# Run PHPCS
echo -e "${YELLOW}=== PHPCS (WordPress Coding Standards) ===${NC}\n"
./plugin/vendor/bin/phpcs --standard=WordPress plugin/*.php plugin/includes/*.php plugin/admin/*.php --ignore=plugin/vendor || true

echo ""

# Run PHPStan
echo -e "${YELLOW}=== PHPStan (Static Analysis) ===${NC}\n"
./plugin/vendor/bin/phpstan analyse -c dev-tools/phpstan.neon || true

echo ""
echo -e "${GREEN}Analysis complete.${NC}"
