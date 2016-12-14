<?php
namespace nanson\postgis\db;

use nanson\postgis\behaviors\GeometryBehavior;
use yii\helpers\ArrayHelper;

/**
 * Class PostgisQueryTrait
 * Add some usefull features for PostgisData
 * @property array geoFields model attributes which must selected as GeoJson
 * @property-read string tableName table name of AR model
 * @property-read yii\db\ColumnSchema[] tableColumns table columns of AR model
 * @property-read array tableColumnsNames table columns names of AR model
 * @package nanson\postgis\db
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
trait PostgisQueryTrait
{
    /**
     * @var bool select geoFields as GeoJson automatically
     */
    public $autoGeoJson = true;

    /**
     * @var bool exclude all geoFields from select
     */
    public $exceptGeoFields = false;

    /**
     * @var array fields, which must be excluded from select
     */
    public $exceptFields = [];

    /**
     * @var array table columns
     */
    protected $_tableColumns;

    /**
     * @var array table columns names
     */
    protected $_tableColumnsNames;

    /**
     * @var array indexes of teable columns
     */
    protected $_tableColumnsIndexes;

    /**
     * @var array model attributes which must selected as GeoJson
     */
    protected $_geoFields;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->select = $this->tableColumnsNames;

        parent::init();

        if ($this->exceptGeoFields) {
            $this->excludeFields($this->geoFields);
        } elseif ($this->autoGeoJson) {
            $this->withGeoFields();
        }

        $this->excludeFields();
    }

    /**
     * Select fields as GeoJson
     * @param null|string|array $fields
     * @return $this
     */
    public function withGeoFields($fields = null)
    {

        if (is_null($fields)) {
            $fields = $this->geoFields;
        } elseif (is_string($fields)) {
            $fields = [$fields];
        }

        if (!empty($fields) and is_array($fields)) {

            foreach ($fields as $field) {

                if (isset($this->tableColumnsIndexes[$field])) {
                    $key = $this->tableColumnsIndexes[$field];
                    $this->select[$key] = "ST_AsGeoJson($this->tableName.[[$field]]) as [[$field]]";
                }

            }

        }

        return $this;

    }

    /**
     * Exclude fields from select
     * It may be usefull with large fields (for example ST_Buffer), when reading rows take much time
     * @param null $fields
     * @return $this
     */
    public function excludeFields($fields = null)
    {
        if (is_null($fields)) {
            $fields = $this->exceptFields;
        } elseif (is_string($fields)) {
            $fields = [$fields];
        }

        if (!empty($fields) and is_array($fields)) {
            $columns = $this->tableColumnsNames;
            $columnNames = array_flip($columns);

            foreach ($fields as $field) {

                if (isset($this->tableColumnsIndexes[$field])) {

                    $key = $this->tableColumnsIndexes[$field];

                    if (isset($this->select[$key])) {
                        unset($this->select[$key]);
                    }
                }

            }
        }

        return $this;
    }

    /**
     * Returns model attributes which must selected as GeoJson
     * If it was not set - returns all columns whith type geometry or geograpy
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getGeoFields()
    {
        if (is_null($this->_geoFields)) {

            $this->_geoFields = [];

            foreach ($this->tableColumns as $column) {
                if (in_array($column->dbType, ['geography', 'geometry'])) {
                    $this->_geoFields[] = $column->name;
                }
            }

        }

        return $this->_geoFields;
    }

    /**
     * Set model attributes, which must selected as GeoJson
     * @param $value
     */
    public function setGeoFields($value)
    {

        if (is_array($value)) {
            $this->_geoFields = $value;
        }

    }

    /**
     * Returns table name of AR model
     * @return string
     */
    public function getTableName()
    {
        $class = $this->modelClass;
        return $class::tableName();
    }

    /**
     * Returns table columns of AR model
     * @return yii\db\ColumnSchema[]
     */
    public function getTableColumns()
    {
        if (is_null($this->_tableColumns)) {
            $class = $this->modelClass;
            $this->_tableColumns = $class::getTableSchema()->columns;
        }

        return $this->_tableColumns;
    }

    /**
     * Returns table columns names of AR model
     * @return array
     */
    public function getTableColumnsNames()
    {
        if (is_null($this->_tableColumnsNames)) {
            $this->_tableColumnsNames = array_keys($this->tableColumns);

            foreach ($this->_tableColumnsNames as $key => $name) {
                $this->_tableColumnsNames[$key] = "$this->tableName.$name";
            }
        }

        return $this->_tableColumnsNames;
    }

    /**
     * Returns indexeses of table columns
     * @return array
     */
    protected function getTableColumnsIndexes()
    {

        if (is_null($this->_tableColumnsIndexes)) {
            $this->_tableColumnsNames = array_keys($this->tableColumns);

            foreach ($this->_tableColumnsNames as $key => $name) {
                $this->_tableColumnsIndexes[$name] = $key;
            }

        }

        return $this->_tableColumnsIndexes;

    }

}
