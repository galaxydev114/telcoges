<?php

namespace Crater\Http\Controllers\V1\Expense;

use Illuminate\Http\Request;
use Crater\Http\Controllers\Controller;
use Crater\Models\Expense;

class ChangeExpensePaidStatusController extends Controller
{
    /**
    * Handle the incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\JsonResponse
    */
   public function __invoke(Request $request, Expense $expense)
   {
        if ($request->paid_status == Expense::STATUS_PENDING) {
            $expense->paid_status = Expense::STATUS_PENDING;
            $expense->save();
        } elseif ($request->paid_status == Expense::STATUS_PAID) {
            $expense->paid_status = Expense::STATUS_PAID;
            $expense->save();
        }

        return response()->json([
            'success' => true
        ]);
    }
}
