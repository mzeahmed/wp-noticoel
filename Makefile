RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NO_COLOR=\033[0m

help: ## Affiche la liste des commandes disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

stan: ## Lance PHPStan
	@echo "$(YELLOW)Lancement de PHPStan...$(NO_COLOR)"
	./vendor/bin/phpstan analyse -c phpstan.neon
	@echo "$(GREEN)PHPStan terminé$(NO_COLOR)"

cs: ## Lance php-cs-fixer en mode test
	@echo "$(YELLOW)Lancement de php-cs-fixer...$(NO_COLOR)"
	composer run lint
	@echo "$(GREEN)php-cs-fixer terminé$(NO_COLOR)"

csf: ## Lance php-cs-fixer avec correction
	@echo "$(YELLOW)Lancement de php-cs-fixer avec correction...$(NO_COLOR)"
	composer run lint:fix
	@echo "$(GREEN)php-cs-fixer terminé$(NO_COLOR)"

rector: ## Appliquer les transformations de Rector
	@echo "$(YELLOW)Application des transformations de Rector...$(NO_COLOR)"
	php ./vendor/bin/rector process
	@echo "$(GREEN)Transformations de Rector appliquées$(NO_COLOR)"

rector-check: ## Vérifie les transformations de Rector sans les appliquer
	@echo "$(YELLOW)Vérification des transformations de Rector...$(NO_COLOR)"
	php ./vendor/bin/rector process --dry-run
	@echo "$(GREEN)Vérification des transformations de Rector terminée$(NO_COLOR)"
