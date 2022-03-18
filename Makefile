# Executables (local)
DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php
NODE_CONT = $(DOCKER_COMP) run --rm node-js

# Executables
PHP      = $(PHP_CONT) php

COMPOSER = $(PHP_CONT) composer

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc composer_install run_lint

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) -f docker-compose.yml -f docker-compose.debug.yml build

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) -f docker-compose.yml -f docker-compose.debug.yml up --detach

start: build up ## Build and start the containers

stop: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

run_with_debug: ## run cli php script with xdebug. Pass the parameter "c=" to run a given command, example: make composer c='bin/select.php'
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e XDEBUG_SESSION=PHPSTORM php $(c)

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

validate: ## run composer validate
	@$(COMPOSER) validate

lint: ## run linter
lint: c=exec --verbose phpcs -- --standard=PSR12 src bin
lint: c=exec --verbose phpstan analyse -- -c phpstan.neon --ansi
lint: composer

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist
vendor: composer

phpstan: ## run phpstan checker
phpstan: c=exec -v phpstan analyse -- -c /srv/app/phpstan.neon --ansi
phpstan: composer

test: ## run phpunit tests
test: c=exec -v phpunit tests
test: composer

## —— PHP without docker  🐘 ——————————————————————————————————————————————————————————————
composer_install: ## install  dependencies
	composer install

run_lint: ## run linter
	composer exec --verbose phpcs -- --standard=PSR12 src bin
	composer exec --verbose phpstan analyse -- -c phpstan.neon --ansi

run_test: ## run test without docker
	composer exec --verbose phpunit tests