<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\VenueService;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new VenueService();
    }
    public function venueList(Request $request)
    {
        return  $this->service->venueList($request);
    }
    public function createVenue(Request $request)
    {
        return  $this->service->createVenue($request);
    }
    public function venueDetail(Request $request)
    {
        return  $this->service->venueDetail($request);
    }

    public function updateVenue(Request $request)
    {
        return  $this->service->updateVenue($request);
    }

    public function venueBusiness(Request $request)
    {
        return  $this->service->venueBusiness($request);

    }
    public function venueDelete(Request $request)
    {
        return  $this->service->venueDelete($request);

    }
}
