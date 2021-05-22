<?php

/**
 * Class DeliveryChargeRepository
 * * Author: Diginest Solutions - http://diginestsolutions.com
 */

namespace App\Repositories;

use App\Models\DeliveryCharges;
use InfyOm\Generator\Common\BaseRepository;

class DeliveryChargeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
      'id',
      'area_id',
      'restaurant_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DeliveryCharges::class;
    }
    public function charges()
    {
        return DeliveryCharges::all();
    }
}
