<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FhirMiddleware
{
    /**
     * Handle an incoming FHIR request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set FHIR-specific headers
        $response = $next($request);
        
        // Ensure proper content type for FHIR responses
        if ($request->is('api/fhir/*')) {
            // If response is JSON and no content type is set, set FHIR content type
            if ($response->headers->get('Content-Type') === null && 
                $response instanceof \Illuminate\Http\JsonResponse) {
                $response->headers->set('Content-Type', 'application/fhir+json; charset=utf-8');
            }
            
            // Add FHIR-specific headers
            $response->headers->set('X-FHIR-Version', '4.0.1');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        }
        
        return $response;
    }
}