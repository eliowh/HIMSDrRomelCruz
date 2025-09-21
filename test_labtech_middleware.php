<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Run the application
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Testing lab tech authentication and role middleware...\n\n";

// Check if the route /labtech/patients exists
echo "Route: '/labtech/patients'\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $found = false;
    foreach ($routes as $route) {
        if ($route->uri() === 'labtech/patients') {
            $found = true;
            echo "  - Route exists: YES\n";
            echo "  - Methods: " . implode(', ', $route->methods()) . "\n";
            echo "  - Name: " . $route->getName() . "\n";
            echo "  - Action: " . $route->getActionName() . "\n";
            
            // Check middleware
            $middleware = $route->middleware();
            echo "  - Middleware: " . implode(', ', $middleware) . "\n";
            
            // Check if role middleware is correctly configured
            $hasRoleMiddleware = false;
            foreach ($middleware as $m) {
                if (strpos($m, 'role:') === 0) {
                    $hasRoleMiddleware = true;
                    $role = str_replace('role:', '', $m);
                    echo "  - Required role: " . $role . "\n";
                }
            }
            
            if (!$hasRoleMiddleware) {
                echo "  - No role middleware found!\n";
            }
        }
    }
    
    if (!$found) {
        echo "  - Route does not exist!\n";
    }
} catch (\Exception $e) {
    echo "Error checking route: " . $e->getMessage() . "\n";
}

// Check if any users with lab_technician role exist
echo "\nChecking lab technician users:\n";
try {
    $users = App\Models\User::where('role', 'lab_technician')->get();
    echo "  - Found " . $users->count() . " lab technician users\n";
    
    foreach ($users as $index => $user) {
        echo "  - Lab Tech #" . ($index + 1) . ": " . $user->name . " (" . $user->email . ")\n";
    }
} catch (\Exception $e) {
    echo "Error checking users: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";