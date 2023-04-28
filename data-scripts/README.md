# Generate All Language Resources

```shell
docker build -f data-scripts/docker/Dockerfile -t zxcvbn-php .
docker run -ti --name zxcvbn-php -v .:/usr/src/app --rm zxcvbn-php bash
php -d memory_limit=-1 data-scripts/generate.php
```
