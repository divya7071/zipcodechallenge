<?php

namespace App\Helpers;

class PolylineDecoder
{
    public static function decode(string $polyline): array
    {
        $points = [];
        $index = 0;
        $lat = 0;
        $lng = 0;

        while ($index < strlen($polyline)) {
            $result = 1;
            $shift = 0;

            do {
                $b = ord($polyline[$index++]) - 63 - 1;
                $result += $b << $shift;
                $shift += 5;
            } while ($b >= 0x1f);

            $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $result = 1;
            $shift = 0;

            do {
                $b = ord($polyline[$index++]) - 63 - 1;
                $result += $b << $shift;
                $shift += 5;
            } while ($b >= 0x1f);

            $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $points[] = [
                'lat' => $lat * 1e-5,
                'lng' => $lng * 1e-5,
            ];
        }

        return $points;
    }
}
