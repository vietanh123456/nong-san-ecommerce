<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$credentials = ["email" => "admin@gmail.com", "password" => "admin123"];
$result = Illuminate\Support\Facades\Auth::attempt($credentials);

echo "AUTH_TEST_RESULT: " . ($result ? "SUCCESS" : "FAILED") . "\n";

