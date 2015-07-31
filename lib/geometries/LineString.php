<?php

namespace nanson\postgis\geometries;

/**
 * Class LineString
 * @package nanson\postgis\geometries
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class LineString implements IGeometry
{
	const GEOMETRY_NAME = 'LINESTRING';

	/**
	 * @inheritdoc
	 */
	public function arrayToWkt($coordinates)
	{

		$points = [];

		foreach ($coordinates as $coordinate) {
			$points[] = implode(' ', $coordinate);
		}

		$wkt = self::GEOMETRY_NAME . '(' . implode(',', $points) . ')';

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

		$points = explode(',', $coordinatesString);

		if (count($points) == 0) {
			return false;
		}

		foreach ($points as $key => $point) {
			$points[$key] = explode(' ', $point);
		}

		return $points;
	}
}