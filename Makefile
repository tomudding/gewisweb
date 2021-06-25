.PHONY: help runprod rundev updatecomposer updatepackage build buildprod builddev login push pushprod pushdev update all prod dev

help:
		@echo "Makefile commands:"
		@echo "runprod"
		@echo "rundev"
		@echo "updatecomposer"
		@echo "updatepackage"
		@echo "updatecss"
		@echo "updateglide"
		@echo "getvendordir"
		@echo "replenish"
		@echo "build"
		@echo "buildprod"
		@echo "builddev"
		@echo "login"
		@echo "push"
		@echo "pushprod"
		@echo "pushdev"
		@echo "update = updatecomposer updatepackage updatecss updateglide"
		@echo "all = build login push"
		@echo "prod = buildprod login pushprod"
		@echo "dev = builddev login pushdev"

.DEFAULT_GOAL := all

runprod:
		@docker-compose -f docker-compose.yml up -d --force-recreate --remove-orphans

runprodtest: buildprod
		@docker-compose -f docker-compose.yml up -d --force-recreate --remove-orphans

rundev: builddev
		@docker-compose up -d --force-recreate --remove-orphans

getvendordir: rundev
		@rm -Rf vendor
		@docker cp "$(shell docker-compose ps -q web)":/code/vendor ./vendor
		@docker-compose down

replenish: rundev
		@docker cp ./public "$(shell docker-compose ps -q web)":/code
		@docker-compose exec web chown -R www-data:www-data /code/public
		@docker cp ./data "$(shell docker-compose ps -q web)":/code
		@docker-compose exec web chown -R www-data:www-data /code/data
		@docker-compose down

update: rundev updatecomposer updatepackage updatecss updateglide
		@docker-compose down

updatecomposer:
		@docker-compose exec web php composer.phar selfupdate
		@docker-compose exec -T web cat composer.phar > composer.phar
		@docker-compose exec web php composer.phar update
		@docker-compose exec -T web cat composer.lock > composer.lock

updatepackage:
		@docker-compose exec web npm update
		@docker-compose exec -T web cat package-lock.json > package-lock.json

updatecss:
		@docker-compose exec web npm run scss
		@docker-compose exec -T web cat public/css/gewis-theme.css > public/css/gewis-theme.css

updateglide:
		@docker-compose exec glide php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
		@docker-compose exec glide php composer-setup.php
		@docker-compose exec glide php -r "unlink('composer-setup.php');"
		@docker-compose exec web php composer.phar selfupdate
		@docker-compose exec -T web cat composer.phar > composer.phar
		@docker-compose exec glide php composer.phar update
		@docker-compose exec -T glide cat composer.lock > docker/glide/composer.lock

all: build login push

prod: buildprod login pushprod

dev: builddev login pushdev

build: buildweb buildglide buildnginx

buildprod: buildwebprod buildglide buildnginx

builddev: buildwebdev buildglide buildnginx

buildweb: buildwebprod buildwebdev

buildwebprod:
		@docker build -t web.docker-registry.gewis.nl/gewisweb_web:production -f docker/web/production/Dockerfile .

buildwebdev:
		@docker build -t web.docker-registry.gewis.nl/gewisweb_web:development -f docker/web/development/Dockerfile .

buildglide:
		@docker build -t web.docker-registry.gewis.nl/gewisweb_glide:latest -f docker/glide/Dockerfile docker/glide

buildnginx:
		@docker build -t web.docker-registry.gewis.nl/gewisweb_nginx:latest -f docker/nginx/Dockerfile docker/nginx

login:
		@docker login web.docker-registry.gewis.nl

push: pushweb pushglide pushnginx

pushprod: pushwebprod pushglide pushnginx

pushdev: pushwebdev pushglide pushnginx

pushweb: pushwebprod pushwebdev

pushwebprod:
		@docker push web.docker-registry.gewis.nl/gewisweb_web:production

pushwebdev:
		@docker push web.docker-registry.gewis.nl/gewisweb_web:development

pushglide:
		@docker push web.docker-registry.gewis.nl/gewisweb_glide:latest

pushnginx:
		@docker push web.docker-registry.gewis.nl/gewisweb_nginx:latest