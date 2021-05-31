<?php

namespace App\Repositories;

use App\Models\Area;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class AreaRepository
 * * Author:Twixt Technologies
 */
class AreaRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'name',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Area::class;
    }
}
