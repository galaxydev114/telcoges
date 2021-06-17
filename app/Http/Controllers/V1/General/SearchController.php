<?php

namespace Crater\Http\Controllers\V1\General;

use Crater\Http\Controllers\Controller;
use Crater\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (Auth::user()->role == 'admin' || Auth::user()->role == 'super admin') {
            $customers = User::where([
                        'role' => 'customer',
                        'company_id' => Auth::user()->company_id
                    ]
                )->applyFilters($request->only(['search']))
                ->latest()
                ->paginate(10);
            
            $suppliers = User::where([
                    'role' => 'supplier',
                    'company_id' => Auth::user()->company_id
                ]
            )->applyFilters($request->only(['search']))
            ->latest()
            ->paginate(10);
            
            $users = User::where([
                        'role' => 'user',
                        'company_id' => Auth::user()->company_id
                    ]
                )->applyFilters($request->only(['search']))
                ->latest()
                ->paginate(10);
        } else {
            $customers = User::where([
                        'role' => 'customer',
                        'creator_id' => Auth::user()->id
                    ]
                )->applyFilters($request->only(['search']))
                ->latest()
                ->paginate(10);
            
            $suppliers = User::where([
                        'role' => 'supplier',
                        'creator_id' => Auth::user()->id
                    ]
                )->applyFilters($request->only(['search']))
                ->latest()
                ->paginate(10);
        }

        return response()->json([
            'customers' => $customers,
            'suppliers' => $suppliers,
            'users' => $users ?? []
        ]);
    }
}
