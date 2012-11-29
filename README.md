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

It is strongly recommended that you never use this script on a live site, so it's best to add the bundle only for the dev environment.

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
    );

    if ('dev' == $this->getEnvironment()) {
        $bundles[] = new MBence\LivePHPBundle\LivePHPBundle();
    }
}
```

### Step 3: Import LivePHP routing files

Add this to your routing_dev.yml file:
``` yaml
# app/config/routing_dev.yml
livephp_monitor:
    resource: "@LivePHPBundle/Resources/config/routing.yml"    
```

### Step 4: Include the livephp.js in your templates

Livephp.js must be loaded on every page you want to work with, so a layout template file is a good place to add the following:
``` twig
    {% block javascripts %}
        {% if app.environment == 'dev' %} 
            <script src="{{ asset('/bundles/livephp/js/livephp.js') }}" type="text/javascript"></script>
        {% endif %}
    {% endblock %}
```

#### Now LivePHP is ready. You can open one of your pages in a browser and if you edit and save a file in your project, the browser will automatically refresh and show the new contents.


## Optional Steps

### Configure LivePHP

You can set many options in LivePHP, including what directories to monitor or ignore, by adding the following configuration to your `config_dev.yml`:
``` yaml
# app/config/config_dev.yml
live_php:
    dirs: [., ../src, ../web]
    ignore: [logs, cache]
    timelimit: 125
```
By default LivePHP will monitor the `app`, `src` and `web` directories and it will ignore the `logs` and `cache` directories.
You can also set the time limit - how long a monitor worker should run in the background. Default is 125 sec. LivePHP will try to use this value, 
but if it cant set the timeout for some reason it will fall back to your servers settings, and work just fine.

### If your app requires authentication, you should add LivePHP to your firewall rules:

``` yaml
# app/config/security.yml
    access_control:
        - { path: ^/livephpmonitor, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

### The front controller name in js
The `app_dev.php` front controller is hardcoded in `livephp.js`. If you use a different filename, you should update it in the line 17:
``` javascript
// Resources/public/js/livephp.js
    init: function() {
        // the url for the monitor (with the front controller)
        LivePhp.url = '/app_dev.php/livephpmonitor';
    //...
```
