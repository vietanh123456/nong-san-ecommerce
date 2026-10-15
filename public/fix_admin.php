<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

DB::table("users")->where("email", "admin@gmail.com")->delete();

DB::table("users")->insert([
    "name" => "Admin Fast",
    "email" => "admin@gmail.com",
    "password" => Hash::make("admin123"),
    "role" => "admin",
    "status" => "approved",
    "created_at" => now(),
    "updated_at" => now(),
]);

echo "SUCCESS! User admin@gmail.com created with pass admin123";

