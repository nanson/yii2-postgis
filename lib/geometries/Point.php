<?php
namespace nanson\postgis\geometries;

/**
 * Class Point
 * @package nanson\postgis\geometries
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class Point implements IGeometry
{
	const GEOMETRY_NAME = 'POINT';

	/**
	 * @inheritdoc
	 */
	public function arrayToWkt($coordinates)
	{

		$wkt = self::GEOMETRY_NAME . '(' . implode(' ', $coordinates) . ')';

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

		$coordinatesArray = explode(' ', $coordinatesString);

		if (count($coordinatesArray) == 0) {
			return false;
		}

		return $coordinatesArray;
	}

}