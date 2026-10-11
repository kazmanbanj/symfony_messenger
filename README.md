# Messenger! Queue work for Later

Well hi there! This repository holds the code and script
for the [Messenger! Queue work for Later](https://symfonycasts.com/screencast/messenger) course on SymfonyCasts.

## Setup

If you've just downloaded the code, congratulations!!

To get it working, follow these steps:

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```

You may alternatively need to run `php composer.phar install`, depending
on how you installed Composer.

**Configure the .env (or .env.local) File**

Open the `.env` file and make any adjustments you need - specifically
`DATABASE_URL`. Or, if you want, you can create a `.env.local` file
and *override* any configuration you need there (instead of changing
`.env` directly).

**Setup the Database**

Again, make sure `.env` is setup for your computer. Then, create
the database & tables!

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

If you get an error that the database exists, that should
be ok. But if you have problems, completely drop the
database (`doctrine:database:drop --force`) and try again.

**Compiling Webpack Encore Assets**

This tutorial uses [Webpack Encore](https://symfonycasts.com/encore),
which isn't important to understand what's going on, but *is* important
to get our app running. Make sure to install Node and also
[yarn](https://yarnpkg.com). Then run:

```
yarn install
yarn dev
```

**Start the built-in web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve
```

(If this is your first time using this command, you may see an
error that you need to run `symfony server:ca:install` first).

Now check out the site at `https://localhost:8000`

Have fun!

## steps
- composer require messenger
- create messgae
- create message handler
- uncomment transport config in env
- composer require symfony/doctrine-messenger (from symfony 5.1)
- ./bin/console messenger:consume -vv async_priority_high async (to run worker)
- php bin/console config:dump framework messenger (to view all avaialble options under messenger.yaml)
- php bin/console debug:config framework messenger (to view current and default config in messenger.yaml)
- ./bin/console debug:container async

- php bin/console cache:clear
- ./vendor/bin/phpunit // run phpunit

You’re using Homebrew on Apple Silicon, so Supervisor is under `/opt/homebrew`, not `/usr/local`.

Your relevant paths are:

```sh
/opt/homebrew/etc/supervisord.conf
/opt/homebrew/etc/supervisor.d/
```

Your Symfony worker config is already linked there:

```sh
/opt/homebrew/etc/supervisor.d/messenger-worker.ini
```

It points to:

```sh
config/messenger-worker.ini
```

To list managed processes, use:

```sh
supervisorctl status
```

```sh
supervisorctl -c /opt/homebrew/etc/supervisord.conf start 'messenger-consume:*'
supervisorctl -c /opt/homebrew/etc/supervisord.conf stop 'messenger-consume:*'
```

to stop supervisor
```sh
supervisorctl -c /opt/homebrew/etc/supervisord.conf stop 'messenger-consume:*'
supervisorctl -c /opt/homebrew/etc/supervisord.conf reread
supervisorctl -c /opt/homebrew/etc/supervisord.conf update messenger-consume
supervisorctl -c /opt/homebrew/etc/supervisord.conf status
```

It currently can’t connect because access to Supervisor’s socket is denied. Try it directly in your Terminal (outside the IDE sandbox), or inspect whether the service is running:

```sh
brew services list | rg supervisor
```

To consume all processes
```sh
ps -A | grep messenger:consume
```
from this, we can stop a process with the below command
```sh
kill process_id
```