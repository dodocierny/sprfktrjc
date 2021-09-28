<?php declare(strict_types=1);

use app\SuperFaktura;

require_once __DIR__ . "/vendor/autoload.php";

$superFaktura = new SuperFaktura();
$data = $superFaktura->computeData();
$superFaktura->print($data);