<?php

namespace nanson\postgis\components;

use yii\base\Component;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class StBuffer
 * Component for generation SQL expression for ST_Buffer
 * @property-read radiusUnits available radius units
 * @package nanson\postgis\components
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class StBuffer extends Component
{

	/**
	 * Radius units
	 */
	const RADIUS_UNIT_DEG = 'deg';
	const RADIUS_UNIT_KM = 'km';
	const RADIUS_UNIT_M = 'm';

	/**
	 * @var bool use Geography instead Geometry
	 */
	public $geography = false;

	/**
	 * @var string radius units
	 */
	public $radiusUnit;

	/**
	 * @var array additional parameters for ST_Buffer
	 */
	public $options = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ( is_null($this->radiusUnit) ) {
			$this->radiusUnit = $this->geography ? self::RADIUS_UNIT_M : self::RADIUS_UNIT_DEG;
		}

		if ( !in_array($this->radiusUnit, $this->radiusUnits) ) {
			throw new InvalidConfigException("Invalid radius unit");
		}

		parent::init();
	}

	/**
	 * Returns expression for ST_Buffer
	 * @param $geometry
	 * @param $radius
	 * @return Expression
	 */
	public function getBuffer($geometry, $radius, $options=[])
	{

		if ($geometry instanceof Expression) {
			$geometry = $geometry->expression;
		}

		if ($radius and !empty($geometry) and is_string($geometry)) {

			if ($this->geography) {

				$geometry = rtrim($geometry, '::geography').'::geography';
				$radius = $this->getGeographyBufferRadius($radius);
				$params = '';

			}
			else {

				$radius = $this->getGeometryBufferRadius($radius);

				$params = [];
				$options = ArrayHelper::merge($this->options, $options);

				foreach ($options as $key => $value) {
					$params[] = "$key=$value";
				}

				$params = !empty($params) ? ", '".implode(' ', $params)."'" : '';

			}

			$sql = "ST_Buffer($geometry, $radius $params)";

			return new Expression($sql);
		}

	}

	/**
	 * Returns buffer radius in metters
	 * @param $radius
	 * @return float
	 */
	public function getGeographyBufferRadius($radius)
	{

		if ($this->radiusUnit == self::RADIUS_UNIT_DEG) {
			// TODO correct conversion from degrees
			$radius = $radius*111;
		}

		if ( $this->radiusUnit != self::RADIUS_UNIT_M ) {
			$radius = $radius*1000;
		}

		return $radius;
	}

	/**
	 * Returns buffer radius in degrees
	 * @param $radius
	 * @return float
	 */
	public function getGeometryBufferRadius($radius)
	{

		if ( $this->radiusUnit == self::RADIUS_UNIT_M ) {
			$radius = $radius*1000;
		}

		if ($this->radiusUnit != self::RADIUS_UNIT_DEG) {
			// TODO correct conversion to degrees
			$radius = $radius/111;
		}

		return $radius;
	}

	/**
	 * Returns available radius units
	 * @return array
	 */
	public function getRadiusUnits()
	{
		return [
			self::RADIUS_UNIT_DEG,
			self::RADIUS_UNIT_KM,
			self::RADIUS_UNIT_M,
		];
	}

}