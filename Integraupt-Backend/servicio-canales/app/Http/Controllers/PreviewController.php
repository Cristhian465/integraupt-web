<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function fetch(Request $request)
    {
        $url = trim((string) $request->query('url', ''));

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'URL inválida.'], 422);
        }

        $parsed = parse_url($url);
        if (! in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
            return response()->json(['error' => 'Solo se permiten URLs http/https.'], 422);
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "User-Agent: Mozilla/5.0 (compatible; IntegraUPT/1.0)\r\n",
                'follow_location' => true,
            ],
            'ssl' => ['verify_peer' => false],
        ]);

        $html = @file_get_contents($url, false, $ctx);

        if ($html === false) {
            return response()->json(['error' => 'No se pudo obtener la URL.'], 422);
        }

        $html = mb_convert_encoding(substr($html, 0, 60000), 'UTF-8', 'auto');

        return response()->json([
            'title' => $this->meta($html, 'og:title') ?? $this->htmlTitle($html),
            'description' => $this->meta($html, 'og:description') ?? $this->meta($html, 'description'),
            'image' => $this->meta($html, 'og:image'),
            'url' => $url,
            'siteName' => $this->meta($html, 'og:site_name') ?? ($parsed['host'] ?? ''),
        ]);
    }

    private function meta(string $html, string $property): ?string
    {
        // og: tags
        if (preg_match('/<meta[^>]+property=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\'](.*?)["\']/si', $html, $m)) {
            return trim($m[1]) ?: null;
        }
        // name= tags (for description)
        if (preg_match('/<meta[^>]+name=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\'](.*?)["\']/si', $html, $m)) {
            return trim($m[1]) ?: null;
        }
        // content= first
        if (preg_match('/<meta[^>]+content=["\'](.*?)["\'[^>]+(?:property|name)=["\']' . preg_quote($property, '/') . '["\'][^>]*>/si', $html, $m)) {
            return trim($m[1]) ?: null;
        }
        return null;
    }

    private function htmlTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]))) ?: null;
        }
        return null;
    }
}
