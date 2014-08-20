PushT
=========
Cloud based open source mobile push notification sending and tracking service for Android ans IOS.

  - Sending notification easily to your users via API
  - Track your notifications receiving and opening rates
  - This is where magic happens with Symfony2, MongoDB, RabbitMQ, Redis...

Version
-------------

0.1.0

Installation
-------------
### Step 1: Get Project

Via git clone:

``` bash
$ git clone https://github.com/mberberoglu/PushT.git
```
Or [Click to download][1]


### Step 2: Install Dependencies

If you don't have composer

``` bash
$ curl -sS https://getcomposer.org/installer | php
```

After:
``` bash
$ php composer.phar install
```
### Step 3: [Configure Web Server][2]

### Step 4: Check Requirements

``` bash
$ php app/check.php
```
### Step 5: Symfony2 Configuration

``` bash
$ php app/console cache:clear --env=prod
$ chmod -R 777 app/cache* app/logs/*
```

Tech
-------------

PushT uses a number of open source projects to work properly:

* [Symfony2] - Symfony2 is full-stack web framework.
* [MongoDB] - MongoDB is an open-source document database, and the leading NoSQL database. 
* [RabbitMQ] - RabbitMQ is open source message broker software that implements the AMQP.
* [Redis] - Redis is an open source, BSD licensed, advanced key-value cache and store.

[Symfony2]:https://github.com/symfony/symfony
[MongoDB]:http://www.mongodb.org/
[RabbitMQ]:http://www.rabbitmq.com/
[Redis]:http://redis.io/


  [1]: https://github.com/mberberoglu/PushT/archive/master.zip
  [2]: http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html