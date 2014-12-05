<?php
require_once(dirname(__DIR__) . '/vendor/autoload.php');

/*
 * This is bootstrap file for non-Laravel projects
 */

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

$container = new Container;
$container->bind('events', 'Illuminate\Events\Dispatcher');
Facade::setFacadeApplication($container);
class_alias('Illuminate\Support\Facades\Event', 'Event');
