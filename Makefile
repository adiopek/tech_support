DOCKER_COMPOSE = docker compose -f docker-compose.dev.yml
DOCKER_COMPOSE_PROD = docker compose -f docker-compose.prod.yml
PHP_CONT = $(DOCKER_COMPOSE) exec php
PHP_BIN = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
CONSOLE = $(PHP_BIN) bin/console

.DEFAULT_GOAL := help

.PHONY: help
help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

## Docker commands
.PHONY: build
build: ## Build the Docker images
	$(DOCKER_COMPOSE) build

.PHONY: up
up: ## Start the Docker containers
	$(DOCKER_COMPOSE) up -d

.PHONY: down
down: ## Stop and remove the Docker containers
	$(DOCKER_COMPOSE) down

.PHONY: restart
restart: down up ## Restart the Docker containers

.PHONY: logs
logs: ## Show the Docker logs
	$(DOCKER_COMPOSE) logs -f

.PHONY: sh
sh: ## Enter the PHP container
	$(PHP_CONT) sh

## Symfony and Composer commands
.PHONY: install
install: ## Install dependencies
	$(COMPOSER) install

.PHONY: cc
cc: ## Clear the Symfony cache
	$(CONSOLE) cache:clear

.PHONY: db-migrate
db-migrate: ## Run database migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

.PHONY: db-fixtures
db-fixtures: ## Load database fixtures
	$(CONSOLE) doctrine:fixtures:load --no-interaction

.PHONY: db-init
db-init: db-migrate db-fixtures ## Initialize the database (migrate and load fixtures)

## Quality and Testing
.PHONY: test
test: ## Run tests
	$(PHP_CONT) vendor/bin/phpunit

.PHONY: stan
stan: ## Run PHPStan
	$(PHP_CONT) vendor/bin/phpstan analyse src tests --level=5 --memory-limit=512M

.PHONY: lint
lint: ## Lint YAML and Twig files
	$(CONSOLE) lint:yaml config
	$(CONSOLE) lint:twig templates

## Full project setup
.PHONY: init
init: build up install db-init ## Fully initialize the project

.PHONY: deploy
deploy: ## Deploy the project to production
	$(DOCKER_COMPOSE_PROD) build
	$(DOCKER_COMPOSE_PROD) up -d
	$(DOCKER_COMPOSE_PROD) exec php composer install --no-dev --optimize-autoloader
	$(DOCKER_COMPOSE_PROD) exec php bin/console doctrine:migrations:migrate --no-interaction
	$(DOCKER_COMPOSE_PROD) exec php bin/console cache:clear
