<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\Storage;

$contents = Storage::disk('google')->listContents();
print_r($contents);
