<?php

namespace Crater\Http\Controllers\V1\Supplier;

use Illuminate\Http\Request;
use Crater\Http\Requests;
use Crater\Models\User;
use Crater\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $suppliers = User::with('creator')
            ->supplier()
            ->applyFilters($request->only([
                'search',
                'contact_name',
                'display_name',
                'phone',
                'supplier_id',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'))
            ->select(
                'users.*',
                DB::raw('sum(invoices.due_amount) as due_amount')
            )
            ->groupBy('users.id')
            ->leftJoin('invoices', 'users.id', '=', 'invoices.user_id')
            ->paginateData($limit);

        return response()->json([
            'suppliers' => $suppliers,
            'supplierTotalCount' => User::where([
                'role' => 'supplier',
                'company_id' => Auth::user()->company_id
                ])->count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Requests\SupplierRequest $request)
    {
        $supplier = User::createSupplier($request);

        return response()->json([
            'supplier' => $supplier,
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  User $supplier
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $supplier)
    {
        $supplier->load([
            'billingAddress.country',
            'shippingAddress.country',
            'fields.customField',
            'creator'
        ]);

        $currency = $supplier->currency;

        return response()->json([
            'supplier' => $supplier,
            'currency' => $currency,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Crater\Models\User $supplier
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Requests\SupplierRequest $request, User $supplier)
    {
        $supplier = User::updateSupplier($request, $supplier);

        $supplier = User::with('billingAddress', 'shippingAddress', 'fields')->find($supplier->id);

        return response()->json([
            'supplier' => $supplier,
            'success' => true
        ]);
    }

     /**
     * Remove a list of Suppliers along side all their resources (ie. Estimates, Invoices, Payments and Addresses)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        User::deleteSuppliers($request->ids);

        return response()->json([
            'success' => true
        ]);
    }
}