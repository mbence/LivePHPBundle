LivePHPBundle for Symfony
=========================

LivePHP will save developers time by automatically refreshing the browser when a file is changed in the working directory.

## Description 

This bundle was written to make developers' life easier.
Inspired by the brilliant live.js (written by Martin Kool), 
this script will auto refresh your browser if you change any files in your working directory. No need for Alt-Tab and manual refresh anymore.

With this script, it is also very easy to check your work in many browsers simultaneously. 
Just load the site in all your browsers or devices and the rest goes automatically.

**WARNING!**
> You should never activate this on a live server! It is meant for developer environment only!

## Prerequisites

This bundle requires Symfony2.

## Installation 

### Step 1: Download the bundle using composer

Add the following in your composer.json:

```js
{
    "require": {
        "mbence/livephp-bundle": "*"
    }
}
```

Then download / update by running the command:

``` bash
$ php composer.phar update mbence/livephp-bundle
```

Composer will install the bundle to your project's `vendor/mbence/livephp-bundle` directory.

### Step 2: Enable the bundle in your AppKernel

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new MBence\LivePHPBundle\LivePHPBundle(),
    );
}
```

### Step 3: Import LivePHP routing files

Add this to your routing.yml file:
``` yaml
# app/config/routing.yml
livephp_monitor:
    resource: "@LivePHPBundle/Resources/config/routing.yml"    
```

### Step 4: Include the livephp.js in your templates

Livephp.js must be loaded on every page you want to work with, so a layout template file is a good place to add the following:
``` twig
    {% block javascripts %}
        <script src="{{ asset('/bundles/livephp/js/livephp.js') }}" type="text/javascript"></script>
    {% endblock %}
```

#### Now LivePHP is working. You can open one of your pages in a browser and if you edit and save a file in your project, the browser will automatically refresh and show the new contents.


### Optional Step 5: Configure LivePHP

You can set many options in LivePHP, including what directories to monitor or ignore, by adding the following configuration to your `config.yml`:
``` yaml
# app/config/config.yml
live_php:
    dirs: [., ../src, ../web]
    ignore: [logs, cache]
```
By default LivePHP will monitor the `app`, `src` and `web` directories and it will ignore the `logs` and `cache` directories.


