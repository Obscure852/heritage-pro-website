<?php

namespace App\Http\Controllers\Pdp;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MyPdpController extends BasePdpController
{
    public function __construct(\App\Services\Pdp\PdpAccessService $accessService)
    {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $plans = $this->accessiblePlansQuery($request->user())
            ->where('user_id', $request->user()->id)
            ->get();

        return view('pdp.my.index', [
            'plans' => $plans,
        ]);
    }
}
