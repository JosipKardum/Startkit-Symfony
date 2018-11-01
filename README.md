# Startkit Symfony + Docker with API platform and Admin bundle

[Symfony](https://symfony.com/) startkit + docker with API platform and admin bundle

## Getting Started

### Requirements
1. Install docker [documentation](https://docs.docker.com/install/#supported-platforms)
2. Install docker-compose [documentation](https://docs.docker.com/compose/install/)

### Prepare project and start project

Clone repository
```
git clone https://github.com/JosipKardum/Startkit-Symfony.git
cd Startkit-Symfony
```
Build server
```angular2html
docker-compose build
```

Run server
```
docker-compose up -d
```
Open docker container
```
docker exec -it -u root project_php bash
```
Go to web directory
```
cd project
```
Composer command
```angular2html
composer install
```

### Go to...

* **Website** - ```http://localhost:8000```
* **API platform** - ```http://localhost:8000/api```
* **Phpmyadmin** - ```http://pma.localhost:8000```
* **Mailhog** - ```http://mailhog.localhost:8000```
* **Traefik Dashboard** - ```http://localhost:8080```
* **Admin bundle** - ```http://localhost:8000/admin```

## Accessories
Stop server
```
docker-compose stop
```

Remove server
```
docker-compose down
```

If you want to change the name, username, password etc. of database, update ```.env``` file.

## Setup xdebug
Go to 
```
File > PHP > Debug > Server
```
Create new server
```angularjs
Name: "docker"
Host: "172.17.0.1"
```
Enable "Use path mappings" and set absolute path on the server
```angularjs
/home/wwwroot/project
```

## Author
* **Josip Kardum**
