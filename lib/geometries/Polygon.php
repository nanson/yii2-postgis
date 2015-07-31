<?php

namespace nanson\postgis\geometries;

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

		$linestrings = [];

		foreach ($coordinates as $linestring) {

			foreach ($linestring as $key => $point) {
				$linestring[$key] = implode(' ', $point);
			}

			if ( $linestring[0] != $linestring[count($linestring) -1] ) {
				$linestring[] = $linestring[0];
			}

			$linestrings[] = '('.implode(',', $linestring).')';

		}

		$wkt = self::GEOMETRY_NAME.'('.implode(',', $linestrings).')';

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

		$wkt = str_replace(', ', ',', $wkt);
		$wkt = str_replace(self::GEOMETRY_NAME, '', $wkt);

		$points = [];
		$linestrings = explode('),(', $wkt);

		foreach ($linestrings as $linestring) {
			$linestring = ltrim($linestring, '(');

			$linestringPoints = explode(',', $linestring);

			foreach($linestringPoints as $key => $point) {
				$linestringPoints[$key] = explode(' ', $point);
			}

			$points[] = $linestringPoints;
		}

		if (count($points) == 0) {
			return false;
		}

		return $points;
	}
}