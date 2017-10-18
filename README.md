Animus Apartments
======

# Motivation
This project was built for animus.de

# Project structure
## Entity

| Field | Type | Length | Description |
|-------|------|--------|-------------| 
| ID | int | 4 | Autoincrement field |
| move-in-date | date | - | Date for move |
| street | string | 255 | Street of apartment |
| post code | string | 10 | Postal code |
| town | string | 255 | City of apartment |
| country | string | 255 | Country of apartment |
| contact e-mail | email | 255 | Contact email |
| security token | string | 32 | Authorization token for edit or delete entity |
| posted at | datetime | - |  Date and time when entity was created |
| edited at | datetime | - | Date and time when entity was edited last time or deleted |

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

## ToDo
- [x] Build sckeleton for application
- [x] Build apartment entity 
- [x] Build simple controllers 
- [ ] Image upload
- [x] Custom validation for some fields (eg. email)
- [ ] Custom error pages
- [ ] Pagination for apartments list
- [ ] Add shell scripts for automatic build and run application
- [ ] Add Makefile
- [ ] README.md
- [ ] Extract symfony service from controller
- [x] RESTapi
- [ ] Unit tests
- [ ] Migrations
- [ ] Create react.js app
- [ ] Nginx
- [ ] Dockerize app
- [ ] Docker compose