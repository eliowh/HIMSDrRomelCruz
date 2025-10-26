<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    protected $psgcBase = 'https://psgc.gitlab.io/api';

    public function provinces()
    {
        // Try to fetch provinces; if a request exception occurs, retry without verification
        try {
            $res = Http::timeout(10)->get($this->psgcBase . '/provinces.json');
            $json = $res->json();
            // some PSGC endpoints wrap the array under 'value' with a Count; unwrap if needed
            if (is_array($json) && array_key_exists('value', $json) && is_array($json['value'])) {
                return response()->json($json['value']);
            }
            return response()->json($json);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // retry without verification
            logger()->warning('Provinces fetch failed, retrying without verification', ['err' => $e->getMessage()]);
            try {
                $res = Http::withoutVerifying()->timeout(10)->get($this->psgcBase . '/provinces.json');
                $json = $res->json();
                if (is_array($json) && array_key_exists('value', $json) && is_array($json['value'])) {
                    return response()->json($json['value']);
                }
                return response()->json($json);
            } catch (\Throwable $e2) {
                logger()->error('LocationController::provinces fetch failed (no-verify)', ['err' => $e2->getMessage()]);
                return response()->json(['error' => 'Unable to fetch provinces', 'message' => $e2->getMessage()], 502);
            }
        } catch (\Throwable $e) {
            logger()->error('LocationController::provinces fetch failed', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Exception fetching provinces', 'message' => $e->getMessage()], 502);
        }
    }

    public function cities(Request $request)
    {
        // Proxy the cities JSON and optionally filter by province code or name
        try {
            $res = Http::timeout(10)->get($this->psgcBase . '/cities.json');
            $json = $res->json();
            $list = (is_array($json) && array_key_exists('value', $json) && is_array($json['value'])) ? $json['value'] : $json;

            // attempt to also fetch municipalities (these contain towns like Pulilan/Baliuag/Plaridel)
            try {
                $mres = Http::timeout(10)->get($this->psgcBase . '/municipalities.json');
                $mjson = $mres->json();
                $munis = (is_array($mjson) && array_key_exists('value', $mjson) && is_array($mjson['value'])) ? $mjson['value'] : $mjson;
                if (is_array($munis) && count($munis)) {
                    $list = array_merge($list, $munis);
                }
            } catch (\Throwable $ee) {
                // non-fatal: log and continue with cities only
                logger()->warning('Municipalities fetch failed (cities flow)', ['err' => $ee->getMessage()]);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            logger()->warning('Cities fetch failed, retrying without verification', ['err' => $e->getMessage()]);
            try {
                $res = Http::withoutVerifying()->timeout(10)->get($this->psgcBase . '/cities.json');
                $json = $res->json();
                $list = (is_array($json) && array_key_exists('value', $json) && is_array($json['value'])) ? $json['value'] : $json;

                // attempt municipalities with no-verify as fallback
                try {
                    $mres = Http::withoutVerifying()->timeout(10)->get($this->psgcBase . '/municipalities.json');
                    $mjson = $mres->json();
                    $munis = (is_array($mjson) && array_key_exists('value', $mjson) && is_array($mjson['value'])) ? $mjson['value'] : $mjson;
                    if (is_array($munis) && count($munis)) {
                        $list = array_merge($list, $munis);
                    }
                } catch (\Throwable $ee) {
                    logger()->warning('Municipalities fetch failed (no-verify flow)', ['err' => $ee->getMessage()]);
                }
            } catch (\Throwable $e2) {
                logger()->error('LocationController::cities fetch failed (no-verify)', ['err' => $e2->getMessage()]);
                return response()->json(['error' => 'Unable to fetch cities', 'message' => $e2->getMessage()], 502);
            }
        } catch (\Throwable $e) {
            logger()->error('LocationController::cities fetch failed', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Exception fetching cities', 'message' => $e->getMessage()], 502);
        }
        $provinceCode = $request->query('province_code');
        $provinceName = $request->query('province');
        $debug = $request->query('debug');

        // If debug requested, return a small sample of the raw list for inspection
        if ($debug) {
            return response()->json(['count' => is_array($list) ? count($list) : 0, 'sample' => array_values(array_slice($list, 0, 6))]);
        }

        if (! $provinceCode && ! $provinceName) {
            return response()->json($list);
        }

        $filtered = array_filter($list, function ($c) use ($provinceCode, $provinceName) {
            if ($provinceCode) {
                $cc = $c['provinceCode'] ?? $c['provCode'] ?? $c['province_code'] ?? $c['provinceId'] ?? $c['province_id'] ?? $c['prov_code'] ?? $c['code'] ?? $c['id'] ?? $c['psgc10DigitCode'] ?? $c['psgc10digitcode'] ?? null;
                if ($cc && (string)$cc === (string)$provinceCode) return true;
            }
            if ($provinceName) {
                $prov = $c['province_name'] ?? $c['provDesc'] ?? $c['prov_name'] ?? $c['province'] ?? $c['region'] ?? '';
                if ($this->normalize($prov) === $this->normalize($provinceName)) return true;
                if (strpos($this->normalize($prov), $this->normalize($provinceName)) !== false) return true;
                // also check city name contains province name as last resort
                $cname = $c['name'] ?? $c['city_name'] ?? $c['citymunDesc'] ?? $c['municipality'] ?? $c['city'] ?? '';
                if (strpos($this->normalize($cname), $this->normalize($provinceName)) !== false) return true;
            }
            return false;
        });

        // If strict filter found nothing but a provinceName was provided, try a relaxed normalized substring match
        $debug = $request->query('debug');
        $filteredCount = count($filtered);
        if (empty($filtered) && $provinceName) {
            $normTarget = $this->normalize($provinceName);
            $relaxed = array_filter($list, function ($c) use ($normTarget) {
                $prov = $c['province_name'] ?? $c['provDesc'] ?? $c['prov_name'] ?? $c['province'] ?? $c['region'] ?? '';
                $cname = $c['name'] ?? $c['city_name'] ?? $c['citymunDesc'] ?? $c['municipality'] ?? $c['city'] ?? '';
                if (strpos($this->normalize($prov), $normTarget) !== false) return true;
                if (strpos($this->normalize($cname), $normTarget) !== false) return true;
                // also check psqc10digit or other numeric fields as strings
                $other = $c['code'] ?? $c['city_code'] ?? $c['id'] ?? '';
                if ($other && strpos((string)$other, $normTarget) !== false) return true;
                return false;
            });
            $relaxedCount = count($relaxed);
            if (!empty($relaxed)) {
                if ($debug) return response()->json(['meta' => ['filtered' => $filteredCount, 'relaxed' => $relaxedCount], 'data' => array_values($relaxed)]);
                return response()->json(array_values($relaxed));
            }
        }

        // Fallback map: when upstream data has no matches, provide a small local fallback set for common provinces
        $fallbacks = [
            'bulacan' => [
                ['name' => 'City of Malolos'],
                ['name' => 'Meycauayan'],
                ['name' => 'San Jose del Monte'],
                ['name' => 'Baliuag'],
                ['name' => 'Calumpit'],
                ['name' => 'Pulilan'],
                ['name' => 'Plaridel'],
                ['name' => 'Guiguinto'],
                ['name' => 'Hagonoy'],
                ['name' => 'Marilao'],
                ['name' => 'Pandi'],
                ['name' => 'Obando'],
                ['name' => 'Paombong'],
                ['name' => 'San Miguel'],
                ['name' => 'San Rafael'],
                ['name' => 'Santa Maria'],
                ['name' => 'Bulakan'],
                ['name' => 'Norzagaray'],
                ['name' => 'Do\'a Remedios Trinidad']
            ],
        ];

        if ($provinceName) {
            $norm = $this->normalize($provinceName);
            if (array_key_exists($norm, $fallbacks)) {
                $fb = $fallbacks[$norm];
                if ($debug) return response()->json(['meta' => ['filtered' => $filteredCount ?? 0, 'relaxed' => 0, 'fallback' => true], 'data' => $fb]);
                return response()->json($fb);
            }
        }

        if ($debug) return response()->json(['meta' => ['filtered' => $filteredCount, 'relaxed' => 0], 'data' => array_values($filtered)]);
        return response()->json(array_values($filtered));
    }

    public function barangays(Request $request)
    {
        // Proxy the barangays JSON and optionally filter by city code or city name
        try {
            $res = Http::timeout(10)->get($this->psgcBase . '/barangays.json');
            $json = $res->json();
            $list = (is_array($json) && array_key_exists('value', $json) && is_array($json['value'])) ? $json['value'] : $json;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            logger()->warning('Barangays fetch failed, retrying without verification', ['err' => $e->getMessage()]);
            try {
                $res = Http::withoutVerifying()->timeout(10)->get($this->psgcBase . '/barangays.json');
                $json = $res->json();
                $list = (is_array($json) && array_key_exists('value', $json) && is_array($json['value'])) ? $json['value'] : $json;
            } catch (\Throwable $e2) {
                logger()->error('LocationController::barangays fetch failed (no-verify)', ['err' => $e2->getMessage()]);
                return response()->json(['error' => 'Unable to fetch barangays', 'message' => $e2->getMessage()], 502);
            }
        } catch (\Throwable $e) {
            logger()->error('LocationController::barangays fetch failed', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Exception fetching barangays', 'message' => $e->getMessage()], 502);
        }

        $cityCode = $request->query('city_code');
        $cityName = $request->query('city');

        $filtered = array_filter($list, function ($b) use ($cityCode, $cityName) {
            if ($cityCode) {
                $cc = $b['citymunCode'] ?? $b['citymun_code'] ?? $b['municipalityCode'] ?? $b['code'] ?? $b['id'] ?? null;
                if ($cc && (string)$cc === (string)$cityCode) return true;
            }
            if ($cityName) {
                $prov = $b['citymunDesc'] ?? $b['city_name'] ?? $b['city'] ?? '';
                if ($this->normalize($prov) === $this->normalize($cityName)) return true;
                if (strpos($this->normalize($prov), $this->normalize($cityName)) !== false) return true;
            }
            return false;
        });

        if (empty($filtered) && $cityName) {
            $normTarget = $this->normalize($cityName);
            $relaxed = array_filter($list, function ($b) use ($normTarget) {
                $cname = $b['barangayDesc'] ?? $b['name'] ?? '';
                $other = $b['citymunDesc'] ?? $b['city_name'] ?? '';
                if (strpos($this->normalize($other), $normTarget) !== false) return true;
                if (strpos($this->normalize($cname), $normTarget) !== false) return true;
                return false;
            });
            if (!empty($relaxed)) return response()->json(array_values($relaxed));
        }

        return response()->json(array_values($filtered));
    }

    protected function normalize($s)
    {
        if (! $s) return '';
        // remove diacritics and non-word chars, lowercase
        $ascii = Str::ascii($s);
        return strtolower(preg_replace('/[^\w\s]/u', '', $ascii));
    }
}
