## Installation
```bash
composer install
yarn install
```

## Build frontend for demo app
```bash
yarn run encore dev
yarn run encore dev --watch
yarn run encore production
```

## Run application
```bash 
php bin/console server:start
```

## Debug sending emails
```bash
python -m smtpd -n -c DebuggingServer localhost:1025
```
