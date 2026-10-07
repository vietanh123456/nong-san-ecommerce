<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = "admin@gmail.com";
$password = "admin123";

$user = App\Models\User::where("email", $email)->first();
if (!$user) {
echo "DIE: User không t?n t?i trong DB!\n";
exit;
}

echo "User ID: " . $user->id . " | Role: " . $user->role . " | Status: " . $user->status . "\n";

if (!Illuminate\Support\Facades\Hash::check($password, $user->password)) {
echo "DIE: M?t kh?u Hash không kh?p!\n";
exit;
}

$attempt = Illuminate\Support\Facades\Auth::attempt(["email" => $email, "password" => $password]);
echo "Auth::attempt: " . ($attempt ? "SUCCESS" : "FAILED") . "\n";

