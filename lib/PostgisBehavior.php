<?php
namespace nanson\postgis;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Command;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use nanson\postgis\geometries;

/**
 * Class PostgisBehavior
 * Handle model attribute stored in postgis format
 * @package nanson\postgis
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 *
 * @property string $type postgis geometry name
 * @property-read mixed $geomtry object implements IGeometry
 */
class PostgisBehavior extends Behavior
{

	/**
	 * @var string attribute name that is to be automatically handled
	 */
	public $attribute;

	/**
	 * @var array list of class names for geometries
	 *
	 * ```php
	 * [
	 *      'POINT' => '\nanson\postgis\geometries\Point',
	 *      'POLYGON' => '\nanson\postgis\geometries\Polygon',
	 * ]
	 * ```
	 */
	public $geometriesClassNames = [
		geometries\Point::GEOMETRY_NAME => '\nanson\postgis\geometries\Point',
		geometries\LineString::GEOMETRY_NAME => '\nanson\postgis\geometries\LineString',
		geometries\Polygon::GEOMETRY_NAME => '\nanson\postgis\geometries\Polygon',
		geometries\MultiPoint::GEOMETRY_NAME => '\nanson\postgis\geometries\MultiPoint',
	];

	/**
	 * @var string geometry name
	 */
	protected $_type;

	/**
	 * @var mixed object implements IGeometry
	 */
	protected $_geometry;

	/**
	 * @inheritdoc
	 */
	public function events()
	{

		return [
			ActiveRecord::EVENT_BEFORE_INSERT => "beforeSave",
			ActiveRecord::EVENT_BEFORE_UPDATE => "beforeSave",
			ActiveRecord::EVENT_AFTER_INSERT => "afterSave",
			ActiveRecord::EVENT_AFTER_UPDATE => "afterSave",
			ActiveRecord::EVENT_AFTER_FIND => "afterFind",
		];

	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function init()
	{

		if(empty($this->attribute)) {
			throw new InvalidConfigException("Class property 'attribute' does`t set");
		}

		if(empty($this->type)) {
			throw new InvalidConfigException("Class property 'geometry' does`t set");
		}

		if ( !isset($this->geometriesClassNames[$this->type]) ) {
			throw new InvalidConfigException('Unknow geometry');
		}

		parent::init();
	}

	/**
	 * Convert array to WKT expression before save
	 * @return bool
	 */
	public function beforeSave()
	{
		$this->arrayToWkt();

		return true;
	}

	/**
	 * Convert WKT to array after save
	 * @return bool
	 */
	public function afterSave()
	{
		$this->wktToArray();

		return true;
	}

	/**
	 * Convert postgis format to array after find
	 * @return bool
	 */
	public function afterFind()
	{
		$this->postgisToWkt();

		$this->wktToArray();

		return true;
	}

	/**
	 * Convert model attribute from array to WKT insert expression
	 * @return Expression
	 */
	public function arrayToWkt()
	{

		$attribute = $this->attribute;

		if ( !empty($this->owner->$attribute) ) {
			$wkt = $this->geometry->arrayToWkt($this->owner->$attribute);
			$query = "ST_GeomFromText('$wkt')";
			$this->owner->$attribute = new Expression($query);
		}
		else {
			$this->owner->$attribute = null;
		}

	}

	/**
	 * Convert WKT to array
	 * @return array
	 */
	public function wktToArray()
	{
		$attribute = $this->attribute;
		if ( !empty($this->owner->$attribute) ) {
			$this->owner->$attribute = $this->geometry->wktToArray($this->owner->$attribute);
		}
	}

	/**
	 * Convert postgis geometry to WKT
	 */
	public function postgisToWkt()
	{
		$attribute = $this->attribute;

		if ( !empty($this->owner->$attribute) ) {
			$query = new Query();
			$res = $query->select("ST_asText('" . $this->owner->$attribute . "') as $attribute")->createCommand()->queryOne();
			$wkt = $res[$attribute];

			$this->owner->$attribute = $wkt;
		}

	}

	/**
	 * Returns geometry object implements IGeometry
	 * @return mixed
	 */
	public function getGeometry()
	{
		if( is_null( $this->_geometry ) ) {
			$className = $this->geometriesClassNames[$this->type];
			$this->_geometry = new $className;
		}

		return $this->_geometry;
	}

	/**
	 * Returns postgis geomtry name
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Set postgis geometry name
	 * @param $value
	 */
	public function setType($value)
	{
		$this->_type = strtoupper($value);
	}

}