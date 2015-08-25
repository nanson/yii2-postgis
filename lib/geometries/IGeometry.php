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
	 * @param array $coordinates
	 * @return string;
	 */
	public function arrayToWkt($coordinates);

	/**
	 * Convert wkt to array
	 * @param string $wkt
	 * @return array|bool
	 */
	public  function wktToArray($wkt);

}