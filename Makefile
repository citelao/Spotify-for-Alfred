.phony: all clean serve

all: init serve

init:
	bundle install

serve:
	jekyll serve