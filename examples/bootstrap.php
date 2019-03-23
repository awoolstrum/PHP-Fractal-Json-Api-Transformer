<?php

declare(strict_types=1);

/*
 * This is a quick and dirty bootstrap so you should be able to drop this directory into any 
 * PHP enabled environment to run a proof of concept. 
 */
 
namespace App;

spl_autoload_register(function ($class) {
    $prefix = 'League\\Fractal\\';
    $base_dir = __DIR__ . '/vendor/fractal/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

include("./Infrastructure/JsonApi.php");
include("./Infrastructure/JsonApiTransformerInterface.php");
include("./Infrastructure/JsonApiEntityInterface.php");
include("./Entity/Franchise.php");
include("./Entity/CoffeeShop.php");
include("./Entity/Region.php");
include("./Transform/RegionTransformer.php");
include("./Transform/CoffeeShopTransformer.php");
include("./Transform/FranchiseTransformer.php");
include("./Responder/Responder.php");
