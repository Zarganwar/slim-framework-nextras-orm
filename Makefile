docker-exec-t=docker-compose exec -T app
docker-exec=docker-compose exec app

.PHONY: app-run
.SILENT: app-run
app-run:
	make docker-start
	# cp -n -v ./config/config.local.example.php ./config/config.local.php || true
	${docker-exec-t} composer install

.PHONY: phpstan
.SILENT: phpstan
phpstan:
	${docker-exec-t} vendor/bin/phpstan analyse -c phpstan.neon --ansi

.PHONY: docker-start
.SILENT: app-run
docker-start:
	cp -n -v docker-compose.override.example.yml docker-compose.override.yml || true
	docker-compose up -d --build