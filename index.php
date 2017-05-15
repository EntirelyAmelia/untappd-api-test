<?php

require __DIR__ . '/vendor/autoload.php';

$app = new App();

try {
    $app->actionGetCheckins();
} catch (\Exception $e) {
    echo $e->getMessage();
}
