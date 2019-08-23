# a2way/docker-base-laravel

A Docker Image specialized for running Laravel PHP framework with Nginx on an Alpine platform.

This Docker Image contains following:

 - Alpine Linux base.
 - Nginx.
 - PHP-FPM.
 - Supervisor to keep Nginx and PHP-FPM running.
 - Composer.
 - A script to auto build the `.env` file based on environment variables provided into the container.

The instructions were tested on Ubuntu. They should work on other Linux OSes as well. You should be able to follow the same general steps in other OSes too.

You must have installed `docker` and `docker-compose` to follow the instructions.

To use, either `COPY` or mount Laravel root directory into `/app` directory. You can use Composer to bootstrap a Laravel project right inside the Docker container\'s `/app` directory. By having that directory mounted to the host file system, you can persist the Laravel files.

To auto build the `.env` file, inject an environment variable named `LARAVEL_VARS`, and list all Laravel environment variable names as `LARAVEL_VARS`'s value. Make the Laravel environment variable names space-separated (Eg: `LARAVEL_VARS=APP_NAME APP_ENV APP_KEY APP_DEBUG ...`.). Then inject each of those environment variables with their values.

## Model Development Setup

Open your shell's "rc file" (Eg: `.bashrc` or `.zshrc`.). At the end of the file, add following two lines:

```
export UID
export GID
```

Make a directory for your new project (Eg: `my-proj`.). In it, create a `Dockerfile` like this:

```
FROM a2way/docker-base-laravel:v_._._
RUN apk --update add shadow
ARG UID
ARG GID
RUN usermod -u $UID app && groupmod -g $GID app
```

Replace `v_._._` with the version you're going to use. Always try to use the latest one.

In `my-proj` directory, make a `docker-compose.yml` file like this:

```
version: '3'
services:
  my-proj:
    build:
      context: .
      args:
        UID: ${UID}
        GID: ${GID}
    ports:
     - 8000:80
    env_file:
     - ./env/my-proj.env
    volumes:
    # - ./vols/vendor:/app/vendor/
     - ./src/:/app/
```

Note that `# - ./vols/vendor:/app/vendor/` is commented out, until we make it active in a later step.

Create following sub directories inside `my-proj`:

 - `vols/vendor`.
 - `src`.
 - `env`.

Create a `.gitignore` file to ignore files and directories we don't need tracked in the Git repo:

```
src/.env
vols/
```

Inside the `env` directory, make a file `.gitignore` file like this:

```
*
!tmp.*
!.gitignore
```

It will cause Git to ignore any file inside the `env` directory, except the `.gitignore` file itself, and anything that has a file name starting with `tmp.`. We can use that behavior to ignore actual environment variable files but track templates of them, that has "tmp." at the start of their names.

Inside the `env` directory, make two files: `tmp.my-proj.env` and `my-proj.env`. Use the following as the content of `tmp.my-proj.env`:

```
LARAVEL_VARS=APP_NAME APP_ENV APP_KEY ... // Fill in complete list of Laravel environment variable names.

APP_NAME=my-proj
APP_ENV=local
APP_KEY=
.
.
. //Fill in complete list of Laravel environment variable names. Keep default values in, when they are okay to be tracked in Git.
```

Next, copy the content of `tmp.my-proj.env` into `my-proj.env`, and fill in all required values.

Go back to the project root.

Make a `Makefile` to make it easy to access the shell of the Docker container as the `app` user inside the Docker Container, which is mapped to your host user's `UID`, which also shares `GID` with your host user:

```
shell-my-proj:
	docker-compose exec -u ${UID}:${GID} my-proj sh
```

To access it as `root`, you can run `docker-compose exec my-proj sh`.

Now you can start the Docker container:

```
docker-compose up --build -d
```

It should run without a problem, and `docker ps` should show you the container running. If you go to http://localhost:8000/ in your browser, you should see a 404 page from Nginx.

Now go inside the container.

```
make shell-my-proj
```

Inside the `/app` directory, you should see that an `.env` file is already created with the values you provided. Delete it for now, as otherwise `composer` won't create a Laravel project inside this directory as it's not empty. Don't be afraid, as that file would be auto created next time you turn the Docker container on.

Then, create the Laravel project:

```
composer create-project --prefer-dist laravel/laravel .
```

Then, `exit` the container, and turn it off:

```
docker-compose down
```

After that, delete the `vendor` directory inside `src` directory.

Now uncomment the line we had commented out in `docker-compose.yml` file, and re run the Docker container.

```
docker-compose up --build -d
```

Then go inside the container again with `make shell-my-proj`, and reinstall Composer packages:

```
composer install
```

This time, the content of the Docker container's `/app/vendor` directory will be persisted in the `vols/vendor` directory in the project root in the host machine.

Go to http://localhost:8000/, and you should be greeted with Laravel welcome page.

## Produce Production Docker Images

In the `my-proj` directory, make a file named `prod.Dockerfile`, and have the following as its content:

```
FROM a2way/docker-base-laravel:v_._._
WORKDIR /app
RUN chown -R app:app .
USER app:app
COPY --chown=app:app ./src/composer.json ./src/composer.lock /app/
RUN composer install --no-autoloader --no-dev 
COPY --chown=app:app ./src /app
RUN composer dump-autoload
```

Make a file named `prod.docker-compose.yml`, and have the following as its content:

```
version: '3'
services:
  my-proj:
    image: my-docker-username/my-proj
    build:
      context: .
      dockerfile: prod.Dockerfile
    ports:
     - 9000:80
    env_file:
     - ./env/my-proj.env
```

Then build and run it:

```
docker-compose -f prod.docker-compose.yml up --build -d
```

You should be able to see your production Docker container running in http://localhost:9000/. You should also be able to see your production Docker Image tagged with `my-docker-username/my-proj:latest`.
