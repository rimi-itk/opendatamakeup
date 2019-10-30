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

Insert actual CKAN values:

```sh
docker-compose exec phpfpm bin/console itk-dev:database:cli <<< 'update data_target set data_target_options = replace(data_target_options, "%CKAN_URL", "your-ckan-url")'
docker-compose exec phpfpm bin/console itk-dev:database:cli <<< 'update data_target set data_target_options = replace(data_target_options, "%CKAN_API_KEY%", "your-ckan-api-key")'
docker-compose exec phpfpm bin/console itk-dev:database:cli <<< 'update data_target set data_target_options = replace(data_target_options, "%CKAN_DATA_SET_ID%", "datatidy")'
```

Run a wrangler:

```sh
bin/console app:data-wrangler:run b3eeb0ff-c8a8-4824-99d6-e0a3747c8b0d --publish -vv
```

Important: Create a data target table before running the wrangler. See
[`src/DataFixtures/Data/DataWrangler.yaml`](src/DataFixtures/Data/DataWrangler.yaml)
for details.

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
