<?php

namespace nanson\postgis\helpers;

use yii\helpers\Json;

/**
 * Class GeoJsonHelper
 * Helper to convert coordinates to GeoJson and GeoJson to coordinates
 * @package nanson\postgis
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class GeoJsonHelper
{

    /**
     * Convert coordinates to GeoJson
     * @param $type geometry type
     * @param $coordinates array of coordinates
     * @param int $srid SRID
     * @return json
     */
    public static function toGeoJson($type, $coordinates, $srid = 4326)
    {

        $geoJson = [
            'type' => $type,
            'coordinates' => $coordinates,
        ];

        if (!is_null($srid)) {

            $geoJson['crs'] = [
                'type' => 'name',
                'properties' => ['name' => "EPSG:$srid"],
            ];

        }

        return Json::encode($geoJson);

    }

    /**
     * Convert coordinates to Geometry Expression
     * @param $type geometry type
     * @param $coordinates array of coordinates
     * @param int $srid SRID
     * @return string
     */
    public static function toGeometry($type, $coordinates, $srid = 4326)
    {

        $geoJson = self::toGeoJson($type, $coordinates, $srid);

        return "ST_GeomFromGeoJSON('$geoJson')";

    }

    /**
     * Convert GeoJson to coordinates
     * @param $geoJson GeoJson
     * @return mixed
     */
    public static function toArray($geoJson)
    {

        $geoJson = Json::decode($geoJson);

        return $geoJson['coordinates'];

    }

}