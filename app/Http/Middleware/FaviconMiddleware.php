<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FaviconMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only modify HTML responses
        if (!$response instanceof \Illuminate\Http\Response || !$this->isHtmlResponse($response)) {
            return $response;
        }

        // Get the content
        $content = $response->getContent();
        
        // Check if there's already a favicon link
        if (strpos($content, 'rel="icon"') === false) {
            // Add favicon link before the first </head> tag
            $faviconTag = '<link rel="icon" type="image/png" href="' . asset('img/hospital_logo.png') . '">';
            $content = preg_replace('/(<\/head>)/i', $faviconTag . '$1', $content, 1);
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * Determine if the given response is an HTML response.
     *
     * @param  \Illuminate\Http\Response  $response
     * @return bool
     */
    protected function isHtmlResponse($response)
    {
        return stripos($response->headers->get('Content-Type') ?? '', 'text/html') !== false;
    }
}