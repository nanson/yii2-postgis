<?php

namespace nanson\postgis\geometries;
use yii\helpers\Json;

/**
 * Class Polygon
 * @package nanson\postgis\geometries
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class Polygon implements IGeometry
{
	const GEOMETRY_NAME = 'POLYGON';

	/**
	 * @inheritdoc
	 */
	public function arrayToWkt($coordinates)
	{

		$latLngs = [];

		foreach ($coordinates as $latLng) {
			$latLngs[] = implode(' ', $latLng);
		}

		if ($latLngs[0] != $latLngs[count($latLngs)-1]) {
			$latLngs[] = $latLngs[0];
		}


		$wkt = self::GEOMETRY_NAME.'(('.implode(' ', $latLngs).'))';

		return $wkt;

	}

	/**
	 * @inheritdoc
	 */
	public function wktToArray($wkt)
	{
		if(strstr($wkt, self::GEOMETRY_NAME) === false) {
			return false;
		}

		$coordinatesString = str_replace([self::GEOMETRY_NAME, '(', ')'], '', $wkt);

		$latLngs = explode(',', $coordinatesString);

		if (count($latLngs) == 0) {
			return false;
		}

		foreach ($latLngs as $latLng) {
			$coordinatesArray = explode(' ', $latLng);
		}

		return $coordinatesArray;
	}
}