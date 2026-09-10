SHELL := /bin/bash
COMPOSE := docker compose -f /root/nginx-certbot/ymls/candash.yml --env-file /root/nginx-certbot/ymls/candash.env
EXEC_API := $(COMPOSE) exec -T api
EXEC_FRONTEND := $(COMPOSE) exec -T frontend

.DEFAULT_GOAL := help

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Build and start all containers
	$(COMPOSE) up -d --build

down: ## Stop and remove containers
	$(COMPOSE) down

restart: ## Restart all containers
	$(COMPOSE) restart

logs: ## Tail logs from all services
	$(COMPOSE) logs -f

logs-api: ## Tail api logs
	$(COMPOSE) logs -f api

ps: ## Show running containers
	$(COMPOSE) ps

migrate: ## Run database migrations
	$(EXEC_API) php artisan migrate --force

seed: ## Seed the database
	$(EXEC_API) php artisan db:seed --force

fresh: ## Fresh migration + seed
	$(EXEC_API) php artisan migrate:fresh --seed --force

test: ## Run API tests
	$(EXEC_API) php artisan test

bash-api: ## Open bash in api container
	$(COMPOSE) exec api bash

bash-frontend: ## Open bash in frontend container
	$(COMPOSE) exec frontend sh

key: ## Generate APP_KEY
	$(EXEC_API) php artisan key:generate

cache-clear: ## Clear application cache
	$(EXEC_API) php artisan optimize:clear

composer-install: ## Install php dependencies
	$(EXEC_API) composer install --no-interaction

npm-install: ## Install frontend dependencies
	$(EXEC_FRONTEND) npm install

dev: ## Start frontend dev server inside container
	$(COMPOSE) up -d frontend
	$(EXEC_FRONTEND) npm run dev

init: ## Initial setup (up, key, migrate, seed)
	$(MAKE) up
	$(EXEC_API) php artisan key:generate --force || true
	$(EXEC_API) php artisan migrate --force
	$(EXEC_API) php artisan db:seed --force
	@echo "TalentMatch is ready at http://localhost:3000"