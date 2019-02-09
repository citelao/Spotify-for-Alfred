.PHONY: all
all: clean build

.PHONY: help
help:
	@echo "Make Spotifious!"
	@echo ""
	@echo "Available commands:"
	@$(MAKE) -f $(lastword $(MAKEFILE_LIST)) list

# https://stackoverflow.com/questions/4219255/how-do-you-get-the-list-of-targets-in-a-makefile
.PHONY: list
list:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

.PHONY: clean
clean:
	-rm -r dist/
	-rm -r build/

.PHONY: init
init: vendor

# https://stackoverflow.com/questions/5618615/check-if-a-program-exists-from-a-makefile
COMPOSER_INSTALLED :=  $(shell command -v composer 2> /dev/null)
vendor:
ifndef COMPOSER_INSTALLED
	$(error Please install composer globally. https://getcomposer.org/download/)
endif
	composer install

.PHONY: build.intermediates
build.intermediates:
	mkdir build/
	@echo "TODO BUILD IN BUILD DIRECTORY"

.PHONY: build.images
build.images:
	@echo "TODO COMPILE IMAGES"

.PHONY: build
build: init check_version build.intermediates build.images
	mkdir dist/
	zip -q -x '*.git*' '*include/images/psd*' '*include/screenshots*' '*.psd' -r dist/Spotifious.alfredworkflow ./

.PHONY: check_version
check_version:
	php script/check_version.php
