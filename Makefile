.PHONY = all init clean build

all: clean build

init:
	composer install

clean:
	-rm -r dist/

build:
	mkdir dist/
	zip -x '*.git*' '*include/images/psd*' '*.psd' -r dist/Spotifious.alfredworkflow ./