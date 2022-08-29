.PHONY: build
build:
	docker build . \
		-t json-rpc/server:dev \
		--build-arg GID=$$(id -u) \
		--build-arg UID=$$(id -u)
	cp .env.dist .env
.PHONY: sh
sh:
	docker run --rm -it --env-file=.env -v $(PWD):/opt/project --entrypoint=sh json-rpc/server:dev
.PHONY: test
test:
	./vendor/bin/phpunit
.PHONY: coverage
test-coverage:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html=./.coverage
.PHONY: analyse
analyse:
	./vendor/bin/phpstan
.PHONY: format
format:
	./vendor/bin/php-cs-fixer fix
.PHONY: preview-format
format-preview:
	./vendor/bin/php-cs-fixer fix --dry-run --diff -vvv
