<?php
namespace nanson\postgis\behaviors;

use yii\db\Query;
use yii\db\Command;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use nanson\postgis\helpers\GeoJsonHelper;

/**
 * Class PostgisBehavior
 * Handles model attribute stored in postgis format (via GeoJson)
 * @property-read array geometryNames available geometry names
 * @package nanson\postgis
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class GeometryBehavior extends Behavior
{

	/**
	 * Geometry names
	 */
	const GEOMETRY_POINT = 'Point';
	const GEOMETRY_MULTIPOINT = 'MultiPoint';
	const GEOMETRY_LINESTRING = 'LineString';
	const GEOMETRY_MULTILINESTRING = 'MultiLineString';
	const GEOMETRY_POLYGON = 'Polygon';
	const GEOMETRY_MULTIPOLYGON = 'MultiPolygon';

	/**
	 * @var string attribute name that will be automatically handled
	 */
	public $attribute;

	/**
	 * @var string geometry name
	 */
	public $type;

	/**
	 * @var bool don't convert attribute afterFind if it in Postgis binary format (it requires a separate query)
	 */
	public $skipAfterFindPostgis = false;

	/**
	 * @var array stored coordinates for afterSave
	 */
	protected $_coordinates;

	/**
	 * @inheritdoc
	 */
	public function events()
	{

		$events = [
			ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
			ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
			ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
		];

		return $events;

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

		if ( !in_array($this->type, $this->geometryNames) ) {
			throw new InvalidConfigException('Unknow geometry type');
		}

		parent::init();
	}

	/**
	 * Convert array to GeoJson expression before save
	 * @return bool
	 */
	public function beforeSave()
	{

		$attributeChanged = $this->owner->isAttributeChanged($this->attribute);

		// store coordinates for afterSave;
		$this->_coordinates = $this->owner->{$this->attribute};

		$this->coordinatesToGeoJson();

		if (!$attributeChanged) {
			$this->owner->setOldAttribute($this->attribute, $this->owner->{$this->attribute});
		}

		return true;
	}

	/**
	 * Convert attribute to array after save
	 * @return bool
	 */
	public function afterSave()
	{

		$this->owner->{$this->attribute} = $this->_coordinates;

		$this->owner->setOldAttribute($this->attribute, $this->owner->{$this->attribute});

		return true;
	}

	/**
	 * Convert attribute to array after find
	 * @return bool
	 */
	public function afterFind()
	{

		if ( !is_object( json_decode($this->owner->{$this->attribute}) ) ) {

			if ($this->skipAfterFindPostgis) {
				return true;
			}
			else {
				$this->postgisToGeoJson();
			}

		}

		$this->geoJsonToCoordinates();

		$this->owner->setOldAttribute($this->attribute, $this->owner->{$this->attribute});

		return true;
	}

	/**
	 * Return available geometry names
	 * @return array
	 */
	public function getGeometryNames()
	{
		return [
			self::GEOMETRY_POINT,
			self::GEOMETRY_MULTIPOINT,
			self::GEOMETRY_LINESTRING,
			self::GEOMETRY_MULTILINESTRING,
			self::GEOMETRY_POLYGON,
			self::GEOMETRY_MULTIPOLYGON,
		];
	}

	/**
	 * Convert model attribute from array to GeoJson insert expression
	 * @return Expression
	 */
	protected function coordinatesToGeoJson()
	{

		$coordinates = $this->owner->{$this->attribute};

		if ( !empty($coordinates) ) {

			$query = is_array($coordinates) ? GeoJsonHelper::toGeometry($this->type, $coordinates) : "'$coordinates'";

			$this->owner->{$this->attribute} = new Expression($query);
		}
		else {
			$this->owner->{$this->attribute} = null;
		}

	}

	/**
	 * Convert model attribute from GeoJson to array
	 * @return array
	 */
	protected function geoJsonToCoordinates()
	{
		if ( !empty($this->owner->{$this->attribute}) ) {
			$this->owner->{$this->attribute} = GeoJsonHelper::toArray($this->owner->{$this->attribute});
		}
	}

	/**
	 * Convert model attribute from Postgis binary to GeoJson
	 */
	protected function postgisToGeoJson()
	{
		$attribute = $this->attribute;

		if ( !empty($this->owner->$attribute) ) {
			$query = new Query();
			$res = $query->select("ST_asGeoJson('" . $this->owner->$attribute . "') as $attribute")->createCommand()->queryOne();
			$geoJson = $res[$attribute];

			$this->owner->$attribute = $geoJson;
		}

	}

}