
## Test Api Transaction

Api Transaction - Sergio Mufalo Junior <sergiojrm02@gmail.com>

---
### Prerequisities

In order to run this container you'll need docker installed.

* [Windows](https://docs.docker.com/windows/started)
* [OS X](https://docs.docker.com/mac/started/)
* [Linux](https://docs.docker.com/linux/started/)

I assume you have installed Docker and it is running.

### Requirements

For development, you will only need docker, but if you prefer not to use the docker you will need PHP >= 7.2, Composer and database MySQL

Developed with the lumen framework [Lumen website](https://lumen.laravel.com/docs)

---
### Commands (Build)

To clone the repository to your local machine, use the following command:

```
    cd ~/code
    git clone https://github.com/YOUR_USERNAME/test-back-end-php.git PROJECT_TITLE
    cd PROJECT_TITLE
```

### Running the project in Docker

First command to create the container through the **docker-compose.yml**

```
    cd project_base
    docker-compose up --build
```
_Note: It may take a while due to machine and php dependencies_

To verify that all containers have been created correctly

```
    docker ps
```

List three containers

```
    project_base_app-api-php
    project_base_webserver
    project_base_app-db-mysql
```

After all containers are running correctly, we will migrate the tables, that is in our php container using migrate.

```
    docker exec -it app-api-php composer install
    docker exec -it app-api-php composer run-script post-create-project-cmd
```

Once everything has started up, you should be able to access the webapp via http://localhost/ on your host machine or **app-test** (you will not need to map any host machine).

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
