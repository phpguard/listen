# PhpGuard

- [![Master Build Status](https://secure.travis-ci.org/phpguard/listen.png?branch=master)](http://travis-ci.org/phpguard/listen)

The `PhpGuard\Listen` listens to any filesystem events and notifies you about the changes.

## Installing
    $ cd /paths/to/project
    $ composer require --dev phpguard/listen "dev-master"

## Usage

    use PhpGuard\Listen\Listen;
    use PhpGuard\Listen\Event\FilesystemEvent;
    use PhpGuard\Listen\Event\ChangeSetEvent;

    $listen = new Listen();
    $options = array(
        "ignore" => array("*.html"),
        "pattern" => "#^/src\/*.\.php#",
        "callback" => function(ChangeSetEvent $event){
            foreach($event->getFilesystemEvents() as $fse){
                echo sprintf(
                    'mode:"%s", paths: "%s",
                    $fse->getHumanType,
                    $fse->getResource()->getRelativePath(),
                );
            }
        }
    );
    $listen
        ->to('/foo/bar') // returns the listener objects
        ->paths('/hello') // add path
        ->ignores('*.html') // ignore file
        ->patterns('#^/src\/*.\.php#')
        ->patterns('#^/src\/*.\.php#')
        ->callbacks(function(ChangeSetEvent $event){
            foreach($event->getFilesystemEvents() as $fse){
                echo sprintf(
                    'mode:"%s", paths: "%s",
                    $fse->getHumanType,
                    $fse->getResource()->getRelativePath(),
                );
            }
        })
    ;
    $listen->start();