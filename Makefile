.PHONY = all init clean build.intermediates build.images build

all: clean build

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