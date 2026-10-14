<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::updateOrCreate(
["email" => "admin@gmail.com"],
[
    "name" => "Super Admin",
    "password" => Hash::make("admin123"),
    "role" => "admin",
    "status" => "approved",
]
);

echo "DONE_USER_ID: " . $user->id . "\n";

