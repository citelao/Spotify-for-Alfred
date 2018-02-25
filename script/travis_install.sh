#!/bin/bash

# https://docs.travis-ci.com/user/multi-os/
if [[ $TRAVIS_OS_NAME == 'osx' ]]; then
	# update & tap PHP!
	brew update
	brew tap homebrew/php

	case "${PHPENV}" in 
		php55)
			brew install php55
			;;
		php56)
			brew install php56
			;;
		php70)
			brew install php70
			;;
	esac

	# install Composer
	# https://github.com/phpmyadmin/phpmyadmin/commit/9ecda4175b6c19c781cf23254da151bfa15eb81a#diff-354f30a63fb0907d4ad57269548329e3
	curl https://getcomposer.org/installer | php
	ln -s $PWD/composer.phar /usr/local/bin/composer
fi
