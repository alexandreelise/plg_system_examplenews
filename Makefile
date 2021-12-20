.PHONY: help show fix gen install doc all

.DEFAULT_GOAL=help
APP_CURRENT_DIR=$$(pwd)
APP_CURRENT_DATETIME=$$(date +%Y%m%d%H%M%S)
APP_BUILD_DIR=$(HOME)/.joomla-extensions/alexandreelise/build
APP_PHP=/usr/bin/php8.1
APP_COMPOSER=$(HOME)/bin/composer
APP_VERSION_SHOW=$$($(HOME)/bin/extension_version.php $(APP_CURRENT_DIR))
APP_VERSION=$$($(HOME)/bin/extension_version.php $$(dirname $(APP_CURRENT_DIR)))
APP_VCS_SHORT_ID=$$(git rev-parse --short HEAD)

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

show: ./src                                ## show the build file name
	cd $(APP_CURRENT_DIR) \
    && echo $(APP_BUILD_DIR)/$$(basename $(APP_CURRENT_DIR))_$(APP_VERSION_SHOW)_$(APP_VCS_SHORT_ID).zip

fix: ./src ./composer.json ./composer.lock     ## Auto-Fix coding standards issues
	$(APP_COMPOSER) qa-fix-all

gen: ./src show 		## Create extension zip file
	mkdir -p $(APP_BUILD_DIR) \
	&& cd $(APP_CURRENT_DIR)/src \
	&& find . -type f -name "*.php" -exec $(APP_PHP) -lf "{}" \; \
	&& zip -9 -r $(APP_BUILD_DIR)/$$(basename $$(dirname $(APP_CURRENT_DIR)))_$(APP_VERSION)_$(APP_VCS_SHORT_ID).zip . \
	&& cd ..

install:  ./composer.json        ## Install composer packages dependencies
	$(APP_COMPOSER) install

./composer.lock: install		## Generate composer.lock file

./docs:				## Create initial docs directory
	mkdir -p ./docs

./tests:			## Create initial tests directory
	mkdir -p ./tests

doc: ./src ./docs ./tests ./composer.lock		## Generate documentation
	$(HOME)/bin/phpdoc

all: gen doc      ## Generate everything
