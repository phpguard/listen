#PhpGuard

- [![Master Build Status](https://secure.travis-ci.org/phpguard/listen.png?branch=master)](http://travis-ci.org/phpguard/listen)

The `PhpGuard\Listen` listens to any filesystem events and notifies you about the changes.

##Installing
    $ cd /path/to/project
    $ composer require --dev phpguard/listen "dev-master"

##Usage

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
                    'mode:"%s", path: "%s",
                    $fse->getHumanType,
                    $fse->getResource()->getRelativePath(),
                );
            }
        }
    );
    $listen->initialize('/foo/bar',$options,FilesystemEvent::All);
    $listen->start();