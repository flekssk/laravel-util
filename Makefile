ci:
	docker run --rm --interactive --tty --volume ${PWD}:/app composer install --ignore-platform-reqs
tst:
	docker run -it --rm -v ${PWD}:/src php:8.0 php /src/vendor/bin/phpunit --configuration=/src/phpunit.xml
