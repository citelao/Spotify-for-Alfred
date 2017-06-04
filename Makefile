.PHONY = all help list init clean build.intermediates build.images build

all: clean build

help:
	@echo "Make Spotifious!"
	@echo ""
	@echo "Available commands:"
	@$(MAKE) -f $(lastword $(MAKEFILE_LIST)) list

list:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

init:
	composer install

clean:
	-rm -r dist/
	-rm -r build/

build.intermediates:
	mkdir build/
	@echo "TODO BUILD IN BUILD DIRECTORY"

build.images:
	@echo "TODO COMPILE IMAGES"

build: build.intermediates build.images
	mkdir dist/
	zip -q -x '*.git*' '*include/images/psd*' '*include/screenshots*' '*.psd' -r dist/Spotifious.alfredworkflow ./