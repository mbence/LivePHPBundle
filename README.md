LivePHPBundle for Symfony
=========================

LivePHP will save developers time by automatically refreshing the browser when any file is changed in the working directory.

## Description 

This bundle was written to make developers' life easier.
Inspired by the brilliant live.js (written by Martin Kool), 
this script will auto refresh your browser if you change any files in your working directory. No need for Alt-Tab and manual refresh anymore.

With this script, it is also very easy to check your work in many browsers simultaneously. 
Just load the site in all your browsers or devices and the rest goes automatically.

WARNING!
You should never activate this on a live server! It is meant for developer environment only!

## Prerequisites

This version of the bundle requires Symfony2.

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

Composer will install the bundle to your project's `vendor/mbence/livephpbundle` directory.

### Step 2: Enable the bundle in your AppKernel

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new MBence\LivePHPBundle(),
    );
}
```