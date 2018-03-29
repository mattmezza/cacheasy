<?php

require '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();
$dotenv->required(["CACHE_PATH"]);
