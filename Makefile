.PHONY: all clean serve

all: clean init serve

clean:
	rm -rf _site/

init:
	bundle install

serve:
	bundle exec jekyll serve --baseurl ''

build:
	bundle exec jekyll build