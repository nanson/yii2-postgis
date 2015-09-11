<?php
namespace nanson\postgis;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Command;
use yii\db\Expression;
use yii\db\Query;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * Class PostgisBehavior
 * Handle model attribute stored in postgis format
 * @package nanson\postgis
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class PostgisBehavior extends Behavior
{

	/**
	 * @var string attribute name that is to be automatically handled
	 */
	public $attribute;

	/**
	 * @var string geometry name
	 */
	public $type;

	/**
	 * @var bool don't convert attribute afterFind
	 */
	public $exceptAfterFind = false;

	/**
	 * @var array list of names for geometries
	 */
	protected $_geometriesNames = ['Point', 'MultiPoint', 'LineString', 'MultiLineString', 'Polygon', 'MultiPolygon',];

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
			ActiveRecord::EVENT_BEFORE_INSERT => "beforeSave",
			ActiveRecord::EVENT_BEFORE_UPDATE => "beforeSave",
			ActiveRecord::EVENT_AFTER_INSERT => "afterSave",
			ActiveRecord::EVENT_AFTER_UPDATE => "afterSave",
		];

		if ( !$this->exceptAfterFind ) {
			$events[ActiveRecord::EVENT_AFTER_FIND] = "afterFind";
		}

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

		if ( !in_array($this->type, $this->_geometriesNames) ) {
			throw new InvalidConfigException('Unknow geometry');
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
			$this->wkbToGeoJson();
		}

		$this->geoJsonToCoordinates();

		$this->owner->setOldAttribute($this->attribute, $this->owner->{$this->attribute});

		return true;
	}

	/**
	 * Convert model attribute from array to GeoJson insert expression
	 * @return Expression
	 */
	public function coordinatesToGeoJson()
	{

		$coordinates = $this->owner->{$this->attribute};

		if ( !empty($coordinates) ) {

			$geoJson = Json::encode([
				'type' => $this->type,
				'coordinates' => $coordinates
			]);;

			$query = "ST_GeomFromGeoJSON('$geoJson')";

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
	public function geoJsonToCoordinates()
	{
		if ( !empty($this->owner->{$this->attribute}) ) {

			$geoJson = Json::decode($this->owner->{$this->attribute});

			$this->owner->{$this->attribute} = $geoJson['coordinates'];
		}
	}

	/**
	 * Convert model attribute from wkb to GeoJson
	 */
	public function wkbToGeoJson()
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