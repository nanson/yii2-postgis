<?php
namespace nanson\postgis\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use nanson\postgis\components\StBuffer;

/**
 * Class StBufferBehavior
 * Behavior for saving buffer based on geometry and radius model attributes
 * @package nanson\postgis
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class StBufferBehavior extends  Behavior
{
	/**
	 * @var string attribute name for saving ST_Buffer
	 */
	public $attribute;

	/**
	 * @var string geometry attribute name
	 */
	public $attributeGeometry;

	/**
	 * @var string radius attribute name
	 */
	public $attributeRadius;

	/**
	 * @var bool use Geography instead Geometry
	 */
	public $geography = false;

	/**
	 * @var string radius units
	 */
	public $radiusUnit;

	/**
	 * @var array options for ST_Buffer
	 */
	public $options = [];

	/**
	 * @inheritdoc
	 */
	public function events()
	{

		return [
			ActiveRecord::EVENT_BEFORE_INSERT => 'setGeometry',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'setGeometry',
		];

	}

	/**
	 * Generate expression for saving ST_Buffer
	 */
	public function setGeometry()
	{
		$model = $this->owner;

		if ( $model->isAttributeChanged($this->attributeRadius, false) or $model->isAttributeChanged($this->attributeGeometry, false) ) {

			$stBuffer = \Yii::createObject([
				'class' => StBuffer::className(),
				'geography' => $this->geography,
				'radiusUnit' => $this->radiusUnit,
				'options' => $this->options,
			]);

			$model->{$this->attribute} = $stBuffer->getBuffer($model->{$this->attributeGeometry}, $model->{$this->attributeRadius});

		}

	}
}