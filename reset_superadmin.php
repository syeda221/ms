<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Show current users
$users = App\Models\User::all(['id', 'name', 'email', 'usertype']);
echo "\n=== Current Users ===\n";
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Type: {$u->usertype}\n";
}

// Reset Super Admin password (ID=1)
$newPass = 'admin123';
App\Models\User::where('id', 1)->update([
    'password' => Illuminate\Support\Facades\Hash::make($newPass)
]);

echo "\n✅ Super Admin (ID=1) password reset kiya gaya!\n";
echo "   Email   : superadmin@example.com\n";
echo "   Password: {$newPass}\n\n";

// Also reset Admin ID=2
$newPass2 = 'admin123';
App\Models\User::where('id', 2)->update([
    'password' => Illuminate\Support\Facades\Hash::make($newPass2)
]);
echo "✅ Admin (ID=2) password reset kiya gaya!\n";
echo "   Email   : admin@admin.com\n";
echo "   Password: {$newPass2}\n\n";
