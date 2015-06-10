<?php
namespace nanson\postgis\geometries;

/**
 * Interface IGeometry
 * @package nanson\postgis\geometries
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
interface IGeometry
{

	/**
	 * Convert array to wkt
	 * @param $coordinates
	 * @return string;
	 */
	public function arrayToWkt($coordinates);

	/**
	 * Convert wkt to array
	 * @param $wkt
	 * @return array|bool
	 */
	public  function wktToArray($wkt);

}