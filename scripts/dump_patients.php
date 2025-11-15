<?php
$projectRoot = dirname(__DIR__);
require $projectRoot . '/vendor/autoload.php';
$app = require_once $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Patient;

$rows = Patient::limit(10)->get(['id','patient_no','barangay','city','province','nationality','sex','date_of_birth']);
echo json_encode($rows->map(function($p){ return $p->toArray(); }));
