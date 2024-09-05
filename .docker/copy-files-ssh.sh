echo Copy to container: $1

echo 'make folders'
docker exec $1 mkdir -p ../html-ssh/var
docker exec $1 chmod -R 777 ../html-ssh/var
echo 'copy project files'
docker cp bin $1:/var/www/html-ssh
docker cp config $1:/var/www/html-ssh
docker cp migrations $1:/var/www/html-ssh
docker cp public $1:/var/www/html-ssh
docker cp src $1:/var/www/html-ssh
docker cp templates $1:/var/www/html-ssh
docker cp tests $1:/var/www/html-ssh
docker cp .env $1:/var/www/html-ssh
docker cp .env.local $1:/var/www/html-ssh
docker cp .env.test $1:/var/www/html-ssh
docker cp composer.json $1:/var/www/html-ssh
docker cp composer.lock $1:/var/www/html-ssh
docker cp phpstan.dist.neon $1:/var/www/html-ssh
docker cp phpstan-object-manager.php $1:/var/www/html-ssh
docker cp phpunit.xml.dist $1:/var/www/html-ssh
docker cp symfony.lock $1:/var/www/html-ssh
echo 'copy vendor files'
docker cp vendor $1:/var/www/html-ssh
echo 'done'
sleep 3
