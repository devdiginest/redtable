<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Repositories\AreaRepository;
use Flash;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class AreaAPIController extends Controller
{
    //

    /** @var  AreaRepository */
    private $AreaRepository;

    public function __construct(AreaRepository $areaRepo)
    {
      $this->areaRepository = $areaRepo;
    }

    /**
     * Display a listing of the Area.
     * GET|HEAD /area
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->areaRepository->pushCriteria(new RequestCriteria($request));
            $this->areaRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $areaList = $this->areaRepository->all();

        foreach ($areaList as $area) {
            // code..
            $area->label = $area->name;
            $area->value = $area->id;
        }

        return $this->sendResponse($areaList->toArray(), 'Area list retrieved successfully');
    }
}
