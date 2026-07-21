RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NO_COLOR=\033[0m

help: ## Show the list of available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

stan: ## Run PHPStan
	@echo "$(YELLOW)Running PHPStan...$(NO_COLOR)"
	./vendor/bin/phpstan analyse -c phpstan.neon
	@echo "$(GREEN)PHPStan done$(NO_COLOR)"

cs: ## Run php-cs-fixer in dry-run mode
	@echo "$(YELLOW)Running php-cs-fixer...$(NO_COLOR)"
	composer run lint
	@echo "$(GREEN)php-cs-fixer done$(NO_COLOR)"

csf: ## Run php-cs-fixer and apply fixes
	@echo "$(YELLOW)Running php-cs-fixer with fixes...$(NO_COLOR)"
	composer run lint:fix
	@echo "$(GREEN)php-cs-fixer done$(NO_COLOR)"

rector: ## Apply Rector transformations
	@echo "$(YELLOW)Applying Rector transformations...$(NO_COLOR)"
	php ./vendor/bin/rector process
	@echo "$(GREEN)Rector transformations applied$(NO_COLOR)"

rector-check: ## Check Rector transformations without applying them
	@echo "$(YELLOW)Checking Rector transformations...$(NO_COLOR)"
	php ./vendor/bin/rector process --dry-run
	@echo "$(GREEN)Rector check done$(NO_COLOR)"
