<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $ads = Advertisement::active()
            ->where(function ($query) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->when($request->placement, fn ($query) => $query->where('placement', $request->placement))
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->paginated($ads, 'Campagnes actives');
    }
}
