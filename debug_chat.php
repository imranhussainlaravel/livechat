<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$chats = App\Models\Chat::with('visitor')->latest()->take(3)->get();
foreach ($chats as $c) {
    echo "Chat #{$c->id} | Status: {$c->status->value} | Visitor: " . ($c->visitor ? $c->visitor->session_token : 'NO VISITOR') . "\n";
}
