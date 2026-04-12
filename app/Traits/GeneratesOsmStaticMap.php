<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait GeneratesOsmStaticMap
{
    protected int $mapWidth  = 800;
    protected int $mapHeight = 400;
    protected int $mapZoom   = 13;

    public function generateOsmMap($activity): void
    {
        $polyline=$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
        if (empty($polyline) || $activity->map_image) {
            return;
        }

        $points = $this->decodePolyline($polyline);
        if (count($points) < 2) return;

        [$minLat, $maxLat, $minLng, $maxLng] = $this->getBounds($points);

        $centerLat = ($minLat + $maxLat) / 2;
        $centerLng = ($minLng + $maxLng) / 2;

        $canvas = imagecreatetruecolor($this->mapWidth, $this->mapHeight);

        $bg = imagecolorallocate($canvas, 245, 245, 245);
        imagefill($canvas, 0, 0, $bg);

        $tile = $this->fetchTile($centerLat, $centerLng);
        imagecopyresampled(
            $canvas,
            $tile,
            0,
            0,
            0,
            0,
            $this->mapWidth,
            $this->mapHeight,
            imagesx($tile),
            imagesy($tile)
        );

        $routeColor = imagecolorallocate($canvas, 252, 76, 2);
        imagesetthickness($canvas, 4);

        for ($i = 1; $i < count($points); $i++) {
            [$x1, $y1] = $this->project($points[$i - 1], $centerLat, $centerLng);
            [$x2, $y2] = $this->project($points[$i], $centerLat, $centerLng);
            imageline($canvas, $x1, $y1, $x2, $y2, $routeColor);
        }

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();

        $fileName = "activity_{$activity->id}.png";
        Storage::disk('public')->put("maps/{$fileName}", $png);

        imagedestroy($canvas);
        imagedestroy($tile);

        $activity->update(['map_image' => $fileName]);
        Log::info($fileName);
    }

    // ---------------- HELPERS ----------------

 protected function fetchTile($lat, $lng)
{
    $z = $this->mapZoom;
    $n = pow(2, $z);

    $x = (int)(($lng + 180) / 360 * $n);
    $y = (int)((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / M_PI) / 2 * $n);

    $url = "https://tiles.stadiamaps.com/tiles/alidade_smooth/{$z}/{$x}/{$y}.png";

    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: YourAppName/1.0 (contact@yourdomain.com)\r\n",
            'timeout' => 5
        ]
    ]);

    $imageData = file_get_contents($url, false, $context);

    if ($imageData === false) {
        throw new \Exception("Failed to download map tile");
    }

    return imagecreatefromstring($imageData);
}


    protected function project(array $point, $centerLat, $centerLng): array
    {
        $scale = 10000;
        $x = ($point[1] - $centerLng) * $scale + $this->mapWidth / 2;
        $y = ($centerLat - $point[0]) * $scale + $this->mapHeight / 2;
        return [$x, $y];
    }

    protected function getBounds(array $points): array
    {
        $lats = array_column($points, 0);
        $lngs = array_column($points, 1);
        return [min($lats), max($lats), min($lngs), max($lngs)];
    }

    protected function decodePolyline(string $polyline): array
    {
        $points = [];
        $index = 0; $lat = 0; $lng = 0;

        while ($index < strlen($polyline)) {
            $shift = 0; $result = 0;
            do {
                $b = ord($polyline[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $shift = 0; $result = 0;
            do {
                $b = ord($polyline[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $points[] = [$lat / 1e5, $lng / 1e5];
        }

        return $points;
    }
}
