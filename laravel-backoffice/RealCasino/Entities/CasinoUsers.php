<?php

namespace JEL\RealCasino\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CasinoUsers
 *
 *
 * @package JEL\Core\Entities
 * @author  Carol Mirabal

 */
class CasinoUsers extends Model
{
    /**
     * Table
     *
     * @var string
     */
    protected $table = 'rccasinousers';

    /**
     * Connection
     *
     * @var string
     */
    protected $connection = 'jel';

}
