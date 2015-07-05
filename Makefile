.PHONY = all init clean build

all: clean build

init:
	composer install

clean:
	-rm -r build/

build:
	mkdir build/
	zip -x '*.git*' '*include/images/psd*' '*.psd' -r build/Spotifious.alfredworkflow ./