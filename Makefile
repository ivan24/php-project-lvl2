SHELL := bash# we want bash behaviour in all shell invocations

# And add help text after each target name starting with '\#\#'
.DEFAULT_GOAL:=help

help:
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## install dependencies
	composer install

phpstan: ## run phpstan checker
	composer exec -v phpstan analyse -- -c /app/phpstan.neon --ansi

validate: ## run composer validate
	composer validate

lint: ## run linter
	composer exec --verbose phpcs -- --standard=PSR12 src bin
	composer exec --verbose phpstan analyse -- -c phpstan.neon --ansi

start: ## run application
	docker-compose run --rm --service-ports php /bin/bash

.PHONY: install start help phpstan validate lint