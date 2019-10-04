# Open data makeup

```sh
docker-compose up --detach
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec phpfpm bin/console fos:user:create --super-admin super-admin@example.com super-admin@example.com password
echo http://127.0.0.1:$(docker-compose port nginx 80 | cut -d: -f2)
```

Load fixtures:

```sh
docker-compose exec phpfpm bin/console doctrine:fixtures:load --no-interaction
```

Run tests:

```sh
docker-compose exec -e APP_ENV=test phpfpm bin/console doctrine:database:drop --force
docker-compose exec -e APP_ENV=test phpfpm bin/console doctrine:database:create
docker-compose exec -e APP_ENV=test phpfpm bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec phpfpm bin/phpunit
```

Fixtures:

```sh
docker-compose exec phpfpm bin/console  doctrine:fixtures:load --no-interaction
```

## Production

```sh
composer install --classmap-authoritative
bin/console doctrine:migrations:migrate --no-interaction
```
