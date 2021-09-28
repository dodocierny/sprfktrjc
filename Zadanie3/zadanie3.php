<?php declare(strict_types=1);

use app\core\Exception\AresException;
use app\core\Exception\IcoException;
use app\core\Exception\InvalidResponse;
use app\core\Exception\NotFound;
use app\core\Reader\Ares;

require_once __DIR__ . "/vendor/autoload.php";

$reader = new Ares();

if(array_key_exists(1, $argv)) {
    $ico = $argv[1];
} else {
    echo "Please specify ICO as the first script argument";
    exit(1);
}

try {
    $company = $reader->getData($ico);
    echo $company;
} catch (IcoException $e) {
    echo "ICO has invalid format.";
} catch (NotFound $e) {
    echo $e->getMessage();
} catch (AresException $e) {
    echo "ARES is not available: " . $e->getMessage();
} catch (InvalidResponse $e) {
    echo "Invalid response: " . $e->getMessage();
} catch (Exception $e) {
    echo "Unknown error: " . $e->getMessage();
}


