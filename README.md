[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)
Symfony Angular TodoMVC 
========================

This project is a combination of the [Symfony REST edition](https://github.com/gimler/symfony-rest-edition) project and [AngularJS](http://angularjs.org/)+[Restangular](https://github.com/mgonto/restangular) to create an implementation
of [TodoMVC](http://todomvc.com/). The majority of the AngularJS code is adapted from the existing TodoMVC implementation with [AngularJS](http://todomvc.com/architecture-examples/angularjs/#/)

Install
----------------------------------

Follow the same instructions as found on the Github page for the [symfony-rest-edition](https://github.com/gimler/symfony-rest-edition)

Essentially:

```bash
$ composer.phar install
```

Then:

```bash
$ cd web 
$ bower install
```

Or click this:

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

Usage
--------------------------------

Start a webserver for the Symfony backend

```bash
$ php app/console server:run localhost:8080
```

Navigate your browser to the TodoMVC client

```bash
http://localhost:8080/todo
```

Or browse through the Rest API

```
http://localhost:8080/app_dev.php
```

All features from the [symfony-rest-edition](https://github.com/gimler/symfony-rest-edition) should be found in this project also.

Screenshots
---------------------------------

![TodoMVC Screenshot](http://i.imgur.com/P0flyyF.png "TodoMVC")

![Symfony2 Backend Screenshot](http://i.imgur.com/gybF8IS.png "Symfony2")

![API Documentation](http://i.imgur.com/XsFnJUY.png "API Docs")
