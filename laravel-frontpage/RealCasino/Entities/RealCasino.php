<?php

namespace JEL\RealCasino\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditGuidedTour
 *
 * This class allows to interact with the auditguidedtour table and define the entity attributes
 *
 * @package JEL\Core\Entities
 * @author  Arniel Serrano

 */
class RealCasino extends Model
{
    /**
     * Table
     *
     * @var string
     */
    protected $table = 'rctransactions';

    /**
     * Connection
     *
     * @var string
     */
    protected $connection = 'jel';

    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Timestamps
     *
     * @var bool
     */
    public $timestamps = true;
}
