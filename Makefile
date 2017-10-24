SYMFONY_CLI=php bin/console
COMPOSER_CLI=php ./composer.phar

.PHONY: all install install-fe start stop clean composer-install composer-update doctrine-create build-fe-dev build-fe-prod smtp-debug

all:
	install

install:
	composer-install
	doctrine-create

install-fe:
	yarn install

start:
	$(SYMFONY_CLI) server:start
	smpt-debug

stop:
	$(SYMFONY_CLI) server:stop

clean:
	$(SYMFONY_CLI) cache:clear

composer-install:
	$(COMPOSER_CLI) install

composer-update:
	$(COMPOSER_CLI) update

doctrine-create:
	$(SYMFONY_CLI) doctrine:database:create --force

build-fe-dev:
	yarn run encore dev

build-fe-prod:
	yarn run encore production

smtp-debug:
	python -m smtpd -n -c DebuggingServer localhost:1025