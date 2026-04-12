<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Polyline;

trait ZipIntersectionTrait
{
    protected function findZipsFromPolyline(string $polyline): array
    {
        $route = $this->decodePolyline($polyline);

        if (count($route) < 2) return [];

        $route = $this->simplify($route, 10);
        $routeBox = $this->bbox($route);

        $matched = [];

        foreach ($this->zipFeatureStream() as $feature) {

            if (
                !is_array($feature) ||
                !isset($feature['geometry'], $feature['geometry']['type']) ||
                $feature['geometry']['type'] !== 'Polygon'
            ) {
                continue;
            }

            $zip = $feature['properties']['ZCTA5CE20']
                ?? $feature['properties']['GEOID20']
                ?? $feature['properties']['zcta5ce20']
                ?? null;

            if (!$zip) continue;

            $outerRing = $feature['geometry']['coordinates'][0];
            $polyBox = $this->bbox($outerRing);

            if (!$this->bboxIntersects($routeBox, $polyBox)) {
                continue;
            }

            if (
                $this->lineIntersectsPolygon($route, $outerRing) ||
                $this->pointInPolygon($route[0], $outerRing)
            ) {
                
                $matched[$zip] = $outerRing;
            }
        }

        return $matched; 
    }
    protected function getSingleZipFeature(string $zipCode): ?array
    {
        foreach ($this->zipFeatureStream() as $feature) {

            if (!isset($feature['properties'])) continue;

         
                $zip = $feature['properties']['ZCTA5CE20']
                ?? $feature['properties']['GEOID20']
                ?? $feature['properties']['zcta5ce20']
                ?? null;
           
          
            if ($zip == $zipCode) {
                return $feature;
            }
        }

        return null;
    }
    

    protected function zipFeatureStream(): \Generator
    {
       // $path = storage_path('app/zipcodes.json');
        $path=public_path('geo/us_zipcodes.json');
        $handle = fopen($path, 'r');
        $buffer = '';
        $depth = 0;
        $inFeature = false;

        while (($line = fgets($handle)) !== false) {

            // Detect feature start
            if (!$inFeature && str_contains($line, '"type":"Feature"')) {
                $buffer = $line;
                $depth  = substr_count($line, '{') - substr_count($line, '}');
                $inFeature = true;

                if ($depth === 0) {
                    yield json_decode(rtrim($buffer, ",\n"), true);
                    $buffer = '';
                    $inFeature = false;
                }
                continue;
            }

            if ($inFeature) {
                $buffer .= $line;
                $depth += substr_count($line, '{');
                $depth -= substr_count($line, '}');

                if ($depth === 0) {
                    yield json_decode(rtrim($buffer, ",\n"), true);
                    $buffer = '';
                    $inFeature = false;
                }
            }
        }

        fclose($handle);
    }

    protected function decodePolyline(string $polyline): array
    {
        return array_map(fn($p) => [$p[1], $p[0]], \Polyline::decode($polyline));
    }

    protected function simplify(array $coords, int $step): array
    {
        return array_values(
            array_filter($coords, fn($_, $i) => $i % $step === 0, ARRAY_FILTER_USE_BOTH)
        );
    }

    protected function bbox(array $coords): array
    {
        return [
            'minLng' => min(array_column($coords, 0)),
            'maxLng' => max(array_column($coords, 0)),
            'minLat' => min(array_column($coords, 1)),
            'maxLat' => max(array_column($coords, 1)),
        ];
    }

    protected function bboxIntersects(array $a, array $b): bool
    {
        return !(
            $a['maxLat'] < $b['minLat'] ||
            $a['minLat'] > $b['maxLat'] ||
            $a['maxLng'] < $b['minLng'] ||
            $a['minLng'] > $b['maxLng']
        );
    }
    protected function pointInPolygon(array $point, array $polygon): bool
    {
        [$x, $y] = $point;
        $inside = false;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            [$xi, $yi] = $polygon[$i];
            [$xj, $yj] = $polygon[$j];

            $intersect = (($yi > $y) !== ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    protected function lineIntersectsPolygon(array $line, array $poly): bool
    {
        for ($i = 0; $i < count($line) - 1; $i++) {
            for ($j = 0; $j < count($poly) - 1; $j++) {
                if ($this->segmentsIntersect(
                    $line[$i], $line[$i + 1],
                    $poly[$j], $poly[$j + 1]
                )) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function segmentsIntersect(array $a, array $b, array $c, array $d): bool
    {
        $ccw = fn($p1, $p2, $p3) =>
            ($p3[1] - $p1[1]) * ($p2[0] - $p1[0]) >
            ($p2[1] - $p1[1]) * ($p3[0] - $p1[0]);

        return (
            $ccw($a, $c, $d) !== $ccw($b, $c, $d) &&
            $ccw($a, $b, $c) !== $ccw($a, $b, $d)
        );
    }
 

}
