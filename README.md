# Yii2 Postgis

Extension for working with postgis. As intermediate fromat used Geo Json.

## Installing

The preferred way to install this extension is through Composer.

```json
{
  "require": {
    "nanson/yii2-postgis": "*"
  }
}
```

## GeometryBehavior

Converts coordinates array to SQL expression for saving in postgis binary format befor insert/update and from postgis binary to array after find.

```php
<?php

use yii\db\ActiveRecord;
use nanson\postgis\behaviors\GeometryBehavior;

MyModel extends ActiveRecord
{

	...
	
	public function behaviors()
	{
		return [
			[
				'class' => GeometryBehavior::className(),
				'attribute' => 'point',
				'type' => GeometryBehavior::GEOMETRY_POINT,
			],
			[
				'class' => GeometryBehavior::className(),
				'attribute' => 'line',
				'type' => GeometryBehavior::GEOMETRY_LINESTRING,
				'skipAfterFindPostgis' => true,
			],
		];
	}

	...

}

...

$model = new MyModel;

$model->point = [39.234, 54,456];
$model->line = [[102.0, 0.0], [103.0, 1.0], [104.0, 0.0], [105.0, 1.0]];

$model->save();

?>
```
| Option				| Type		| Default	| Description	|
|-----------------------|-----------|-----------|---------------|
| attribute				| string	|			| attribute that will be automatically handled|
| type					| string	|			| geometry type: `Point`, `LineString`, `Polygon`, `MultiPoint`, `MultiLineString`, `MultiPolygon`|
| skipAfterFindPostgis	| boolean	| false		| skip convertion after find, if data in postgis binary  (it requires a separate query, look `PostgisQueryTrait`)|

##StBufferBehavior
Generate SQL expression before insert/update based on geometry and radius

```php
<?php

use yii\db\ActiveRecord;
use nanson\postgis\behaviors\GeometryBehavior;
use nanson\postgis\behaviors\StBufferBehavior;

MyModel extends ActiveRecord
{

	...
	
	public function behaviors()
	{
		return [
			[
				'class' => GeometryBehavior::className(),
				'attribute' => 'point',
				'type' => GeometryBehavior::GEOMETRY_POINT,
			],
			[
				'class' => StBufferBehavior::className(),
				'attribute' => 'buffer',
				'attributeGeometry' => 'point',
				'attributeRadius' => 'radius',
			],
		];
	}

	...

}

...

$model = new MyModel;

$model->point = [39.234, 54,456];
$model->radius = 5;

// It will be save St_Buffer for `$model->point` with `$model->radius` in `$model->buffer`
$model->save();

?>
```

| Option			| Type		| Default	| Description	|
|-------------------|-----------|-----------|---------------|
| attribute			| string	|			| attribute for saving buffer |
| attributeGeometry	| string	|			| attribute with geometry |
| attributeRadius	| string	|			| attribute with radius |
| geography			| boolean	| false		| build buffer as geography |
| radiusUnit		| string	| `deg` for geomtery or `m` for geography | units of buffer radius: `deg`, `m`, `km` |
| options			| array		|[]			| additional options for St_Buffer function |

## PostgisQueryTrait

This trait extends ActiveQuery for working with Postgis data.

## GeoJsonHelper
Helper for working with Geo Json