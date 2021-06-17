<?php

namespace Crater\Models;

use App;
use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Crater\Models\Currency;
use Crater\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Crater\Models\Payment;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Crater\Models\ExpenseCategory;
use Crater\Models\User;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\DB;
use Crater\Traits\HasCustomFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Expense extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia, HasCustomFieldsTrait;

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SENT = 'SENT';
    const STATUS_VIEWED = 'VIEWED';
    const STATUS_OVERDUE = 'OVERDUE';
    const STATUS_COMPLETED = 'COMPLETED';

    const STATUS_DUE = 'DUE';
    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PARTIALLY_PAID = 'PARTIALLY_PAID';
    const STATUS_PAID = 'PAID';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'total' => 'integer',
        'tax' => 'integer',
        'sub_total' => 'integer',
        'discount' => 'float',
        'discount_val' => 'integer',
    ];

    protected $guarded = ['id'];

    protected $appends = [
        'formattedExpenseDate',
        'formattedExpenseDue',
        'formattedCreatedAt',
        'receipt'
    ];

    public function setExpenseDateAttribute($value)
    {
        if ($value) {
            $this->attributes['expense_date'] = Carbon::createFromFormat('Y-m-d', $value);
        }
    }

    public function setExpenseDueAttribute($value)
    {
        if ($value) {
            $this->attributes['expense_due'] = Carbon::createFromFormat('Y-m-d', $value);
        }
    }

    public function items()
    {
        return $this->hasMany('Crater\Models\ExpenseItem');
    }

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo('Crater\Models\User', 'creator_id');
    }

    public function getPreviousStatus()
    {
        if ($this->expense_due < Carbon::now()) {
            return self::STATUS_OVERDUE;
        } elseif ($this->viewed) {
            return self::STATUS_VIEWED;
        } elseif ($this->sent) {
            return self::STATUS_SENT;
        } else {
            return self::STATUS_DRAFT;
        }
    }

    private function strposX($haystack, $needle, $number)
    {
        if ($number == '1') {
            return strpos($haystack, $needle);
        } elseif ($number > '1') {
            return strpos(
                $haystack,
                $needle,
                $this->strposX($haystack, $needle, $number - 1) + strlen($needle)
            );
        } else {
            return error_log('Error: Value for parameter $number is out of range');
        }
    }
    
    public function getFormattedExpenseDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->expense_date)->format($dateFormat);
    }

    public function getFormattedExpenseDueAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->expense_due)->format($dateFormat);
    }


    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function getReceiptAttribute($value)
    {
        $media = $this->getFirstMedia('receipts');
        if ($media) {
            return $media->getPath();
        }

        return null;
    }

    public function scopeWhereStatus($query, $status)
    {
        return $query->where('expenses.status', $status);
    }
    
    public function scopeWherePaidStatus($query, $status)
    {
        return $query->where('expenses.paid_status', $status);
    }

    public function scopeWhereDueStatus($query, $status)
    {
        return $query->whereIn('expenses.paid_status', [
            self::STATUS_UNPAID,
            self::STATUS_PARTIALLY_PAID
        ]);
    }

    public function scopeWhereDocNum($query, $docNum)
    {
        return $query->where('expenses.doc_num', 'LIKE', '%' . $docNum . '%');
    }

    public function scopeExpensesBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'expenses.expense_date',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->whereHas('category', function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%');
            })
                ->orWhere('notes', 'LIKE', '%' . $term . '%');
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);
        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('status')) {
            if (
                $filters->get('status') == self::STATUS_UNPAID ||
                $filters->get('status') == self::STATUS_PARTIALLY_PAID ||
                $filters->get('status') == self::STATUS_PAID
            ) {
                $query->wherePaidStatus($filters->get('status'));
            } elseif ($filters->get('status') == self::STATUS_DUE) {
                $query->whereDueStatus($filters->get('status'));
            } else {
                $query->whereStatus($filters->get('status'));
            }
        }

        if ($filters->get('paid_status')) {
            $query->wherePaidStatus($filters->get('status'));
        }

        if ($filters->get('expense_category_id')) {
            $query->whereCategory($filters->get('expense_category_id'));
        }

        if ($filters->get('user_id')) {
            $query->whereSupplier($filters->get('user_id'));
        }

        if ($filters->get('expense_id')) {
            $query->whereExpense($filters->get('expense_id'));
        }

        if ($filters->get('doc_num')) {
            $query->whereDocNum($filters->get('duc_num'));
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('Y-m-d', $filters->get('from_date'));
            $end = Carbon::createFromFormat('Y-m-d', $filters->get('to_date'));
            $query->expensesBetween($start, $end);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'expense_date';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }
    }

    public function scopeWhereExpense($query, $expense_id)
    {
        $query->orWhere('id', $expense_id);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('expenses.company_id', $company_id);
    }

    public function scopeWhereCategoryName($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->whereHas('category', function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%');
            });
        }
    }

    public function scopeWhereNotes($query, $search)
    {
        $query->where('notes', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereCategory($query, $categoryId)
    {
        return $query->where('expenses.expense_category_id', $categoryId);
    }

    public function scopeWhereUser($query, $user_id)
    {
        return $query->where('expenses.user_id', $user_id);
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            return collect(['data' => $query->get()]);
        }

        return $query->paginate($limit);
    }

    // public function scopeExpensesAttributes($query)
    // {
    //     $query->select(
    //         DB::raw('
    //             count(*) as expenses_count,
    //             sum(amount) as total_amount,
    //             expense_category_id')
    //     )
    //         ->groupBy('expense_category_id');
    // }

    public static function createExpense($request)
    {
        $data = $request->except('items', 'taxes');

        $data['creator_id'] = Auth::id();
        $data['status'] = Expense::STATUS_DRAFT;
        $data['company_id'] = $request->header('company');
        $data['paid_status'] = Expense::STATUS_UNPAID;
        $data['tax_per_item'] = CompanySetting::getSetting('tax_per_item', $request->header('company')) ?? 'NO ';
        $data['discount_per_item'] = CompanySetting::getSetting('discount_per_item', $request->header('company')) ?? 'NO';
        $data['due_amount'] = $request->total;
        $data['amount'] = $request->total;

        $expense = self::create($data);

        $expense->unique_hash = Hashids::connection(Expense::class)->encode($expense->id);
        $expense->save();

        if ($request->hasFile('attachment_receipt')) {
            $expense->addMediaFromRequest('attachment_receipt')->toMediaCollection('receipts', 'local');
        }

        self::createItems($expense, $request);

        $taxes = json_decode($request->taxes, true);
        if ($request->has('taxes') && (!empty($taxes))) {
            self::createTaxes($expense, $request);
        }

        $customFields = json_decode($request->customFields, true);

        if ($customFields) {
            $expense->addCustomFields($customFields);
        }

        $expense = Expense::with([
            'items',
            'user',
            'category',
            'taxes'
        ]);

        return $expense;
    }

    public function updateExpense($request)
    {
        $data = $request->except('items');
        $oldAmount = $this->total;

        
        if ($oldAmount != $request->total) {
            $oldAmount = (int) round($request->total) - (int) $oldAmount;
        } else {
            $oldAmount = 0;
        }

        $data['due_amount'] = ($this->due_amount + $oldAmount);
        $data['amount'] = ($this->due_amount + $oldAmount);

        if ($data['due_amount'] == 0 && $this->paid_status != Expense::STATUS_PAID) {
            $data['status'] = Expense::STATUS_COMPLETED;
            $data['paid_status'] = Expense::STATUS_PAID;
        } elseif ($this->due_amount < 0 && $this->paid_status != Expense::STATUS_UNPAID) {
            return response()->json([
                'error' => 'invalid_due_amount'
            ]);
        } elseif ($data['due_amount'] != 0 && $this->paid_status == Expense::STATUS_PAID) {
            $data['status'] = $this->getPreviousStatus();
            $data['paid_status'] = Expense::STATUS_PARTIALLY_PAID;
        }

        $this->update($data);

        $this->items()->delete();
        $this->taxes()->delete();

        self::createItems($this, $request);

        if ($request->hasFile('attachment_receipt')) {
            $this->clearMediaCollection('receipts');
            $this->addMediaFromRequest('attachment_receipt')->toMediaCollection('receipts', 'local');
        }

        $taxes = json_decode($request->taxes, true);
        if ($request->has('taxes') && (!empty($taxes))) {
            self::createTaxes($this, $request);
        }

        $customFields = json_decode($request->customFields, true);

        if ($customFields) {
            $this->updateCustomFields($customFields);
        }

        $expense = Expense::with([
            'items',
            'user',
            'category',
            'taxes'
        ])
            ->find($this->id);

        return $expense;
    }

    public static function createItems($expense, $request)
    {
        $expenseItems = json_decode($request->items, true);

        foreach ($expenseItems as $expenseItem) {
            $expenseItem['company_id'] = $request->header('company');
            $item = $expense->items()->create($expenseItem);

            if (array_key_exists('taxes', $expenseItem) && $expenseItem['taxes']) {
                foreach ($expenseItem['taxes'] as $tax) {
                    $tax['company_id'] = $request->header('company');
                    if (gettype($tax['amount']) !== "NULL") {
                        $item->taxes()->create($tax);
                    }
                }
            }
        }
    }

    public static function createTaxes($expense, $request)
    {
        $taxes = json_decode($request->taxes, true);

        if ($request->has('taxes') && (!empty($taxes))) {
            foreach ($taxes as $tax) {
                $tax['company_id'] = $request->header('company');

                if (gettype($tax['amount']) !== "NULL") {
                    $expense->taxes()->create($tax);
                }
            }
        }
    }

    public function scopeExpensesAttributes($query)
    {
        $query->select(
            DB::raw('
                count(*) as expenses_count,
                sum(total) as total_amount,
                expense_category_id')
        )
            ->groupBy('expense_category_id');
    }
}
