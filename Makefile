SYMFONY_CLI=php bin/console
COMPOSER_CLI=php ./composer.phar
YARN_CLI=yarn run encore

.PHONY: all install install-fe start stop clean composer-install composer-update doctrine-create build-fe-dev build-fe-prod smtp-debug-start

all:
	install

install:
	composer-install
	doctrine-create

install-fe:
	yarn install

start: smtp-debug-start
	./bin/start.sh

stop: smtp-debug-stop
	./bin/stop.sh

clean:
	$(SYMFONY_CLI) cache:clear

composer-install:
	$(COMPOSER_CLI) install

composer-update:
	$(COMPOSER_CLI) update

doctrine-create:
	$(SYMFONY_CLI) doctrine:database:create --force

build-fe-dev:
	$(YARN_CLI) dev

build-fe-prod:
	$(YARN_CLI) production

smtp-debug-start:
	./bin/start_mail.sh

smtp-debug-stop:
	./bin/stop_mail.sh
