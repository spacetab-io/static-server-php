<?php

require __DIR__ . '/vendor/autoload.php';

$conf = \Spacetab\Configuration\Configuration::auto('local')->load();
$ri = new \StaticServer\Modifier\Iterator\RecursiveIterator(new \Amp\File\Filesystem(new \Amp\File\Driver\BlockingDriver()));
$ri->setConfiguration($conf);
$ri->setLogger(Spacetab\Logger\Logger::default('asd'));

\Amp\Loop::run(function () use ($ri) {
    foreach (yield $ri->iterate() as $item) {
        dump($item->realpath);
    }
});

