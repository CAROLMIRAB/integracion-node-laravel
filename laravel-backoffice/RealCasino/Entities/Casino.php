<?php

namespace JEL\RealCasino\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Casinos
 *
 *
 *
 * @package JEL\Core\Entities
 * @author  Carol Mirabal
 */
class Casino extends Model
{
    /**
     * Table
     *
     * @var string
     */
    protected $table = 'rccasino';

    /**
     * Connection
     *
     * @var string
     */
    protected $connection = 'jel';

    public $incrementing = true;

    protected $fillable = ['casino', 'name', 'description', 'whitelabel', 'owner', 'businesstype', 'businessvalue', 'operationlimit'];

}
