<?php

namespace nanson\postgis\helpers;

use yii\db\Expression;

/**
 * Class PostgisHelper
 * Helper to generate Postgis sql expressions
 * @package nanson\postgis\helpers
 * @author Chernyavsky Denis <panopticum87@gmail.com>
 */
class PostgisHelper
{

    /**
     * Create sql expression for ST_Within function
     *
     * boolean ST_Within(geometry A, geometry B);
     * @param string $geometry1
     * @param string $geometry2
     * @return Expression sql expression
     */
    public static function stWithin($geometry1, $geometry2)
    {
        return new Expression("ST_Within($geometry1, $geometry2)");
    }

}