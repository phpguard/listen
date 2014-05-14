# PhpGuard

[![Latest Stable Version](https://poser.pugx.org/phpguard/listen/v/stable.png)](https://packagist.org/packages/phpguard/listen)
[![Master Build Status](https://secure.travis-ci.org/phpguard/listen.png?branch=master)](http://travis-ci.org/phpguard/listen)
[![Coverage Status](https://coveralls.io/repos/phpguard/listen/badge.png?branch=master)](https://coveralls.io/r/phpguard/listen?branch=master)

The `PhpGuard\Listen` listens to any filesystem events and notifies you about the changes.

## Installing
```php
$ cd /paths/to/project
$ composer require --dev phpguard/listen "dev-master"
```
## Basic Usage

```php
use PhpGuard\Listen\Listen;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Event\ChangeSetEvent;

$listen = new Listen();
$listener = $listen->to('/path/to/project') // returns the listener objects
    ->paths('/foobar') // add path to listen
    ->ignores('*.html') // ignore file
    ->patterns('#^src\/*.\.php#')
    ->patterns('#^spec\/*.\.php#')
    ->callbacks(function(ChangeSetEvent $event){
        foreach($event->getFilesystemEvents() as $fse){
            echo sprintf(
                'mode:"%s", paths: "%s",
                $fse->getHumanType,
                $fse->getTrack()->getRelativePath(),
            );
        }
    });
$listen->start();
```