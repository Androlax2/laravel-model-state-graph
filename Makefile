# PHP Docker image to use
PHP_IMAGE = php:8.3-cli
COMPOSER_IMAGE = composer:latest
DOCKER_RUN = docker run --rm -v $(PWD):/app -w /app

.PHONY: help test-coverage copy-git-diff
.DEFAULT_GOAL := help

help: ## Display this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

docker-test-coverage: ## Run tests with coverage in Docker (with Xdebug)
	docker build -t laravel-model-state-graph-test -f Dockerfile .
	$(DOCKER_RUN) -e XDEBUG_MODE=coverage laravel-model-state-graph-test bash -c "\
		composer install && \
		./vendor/bin/pest --coverage"

copy-git-diff: ## Copy git diff
	git diff --staged | pbcopy
