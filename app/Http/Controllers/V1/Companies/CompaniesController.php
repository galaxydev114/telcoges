<?php

namespace Crater\Http\Controllers\V1\Companies;

use Illuminate\Support\Facades\DB;

use Crater\Http\Controllers\Controller;
use Crater\Http\Requests\CompaniesRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Crater\Models\User;
use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Crater\Models\Setting;
use Crater\Models\Address;
use Crater\Models\PaymentMethod;
use Crater\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompaniesController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $companies = User::where('role', 'admin')
            ->applyFilters(
                $request->only([
                    'phone',
                    'email',
                    'display_name',
                    'orderByField',
                    'orderBy'
                ])
            )
            ->latest()
            ->paginate($limit);

        return response()->json([
            'companies' => $companies
        ]);
    }

    public function store(CompaniesRequest $request)
    {
        $request->validated();

        $company = Company::create([
            'name' => $request->companyName,
            'unique_hash' => str_random(20)
        ]);

        // add an administrator for a company

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' =>  $request->password,
            'role' => 'admin',
            'verify_token' => Str::random(20),
            'company_id' => $company->id,
        ]);

        $user->email_verified_at = Carbon::now();
        $user->save();
        
        $user->setSettings(['language' => 'es']);

        $defaultInvoiceEmailBody = 'Ha recibido una nueva factura de <b>{COMPANY_NAME}</b>.</br>Descarga usando el botón de abajo:';
        $defaultEstimateEmailBody = 'Ha recibido un nuevo presupuesto de <b>{COMPANY_NAME}</b>.</br>Descarga usando el botón de abajo:';
        $defaultPaymentEmailBody = 'Gracias por el pago.</b></br>Please download your payment receipt using the button below:';
        $billingAddressFormat = '<p>{TAX_ID_NUMBER}</p><h3>{BILLING_ADDRESS_NAME}</h3><p>{BILLING_ADDRESS_STREET_1}</p><p>{BILLING_ADDRESS_STREET_2}</p><p>{BILLING_CITY}  {BILLING_STATE}</p><p>{BILLING_COUNTRY}  {BILLING_ZIP_CODE}</p><p>{BILLING_PHONE}</p>';
        $shippingAddressFormat = '<p>{TAX_ID_NUMBER}</p><h3>{SHIPPING_ADDRESS_NAME}</h3><p>{SHIPPING_ADDRESS_STREET_1}</p><p>{SHIPPING_ADDRESS_STREET_2}</p><p>{SHIPPING_CITY}  {SHIPPING_STATE}</p><p>{SHIPPING_COUNTRY}  {SHIPPING_ZIP_CODE}</p><p>{SHIPPING_PHONE}</p>';
        $companyAddressFormat = '<h3><strong>{COMPANY_NAME}</strong></h3><p>{COMPANY_TAX_ID_NUMBER}</p><p>{COMPANY_ADDRESS_STREET_1}</p><p>{COMPANY_ADDRESS_STREET_2}</p><p>{COMPANY_CITY} {COMPANY_STATE}</p><p>{COMPANY_COUNTRY}  {COMPANY_ZIP_CODE}</p><p>{COMPANY_PHONE}</p>';
        $paymentFromCustomerAddress = '<h3>{BILLING_ADDRESS_NAME}</h3><p>{BILLING_ADDRESS_STREET_1}</p><p>{BILLING_ADDRESS_STREET_2}</p><p>{BILLING_CITY} {BILLING_STATE} {BILLING_ZIP_CODE}</p><p>{BILLING_COUNTRY}</p><p>{BILLING_PHONE}</p>';

        $company_settings = [
            'invoice_auto_generate' => 'YES',
            'payment_auto_generate' => 'YES',
            'estimate_auto_generate' => 'YES',
            'save_pdf_to_disk' => 'NO',
            'invoice_mail_body' => $defaultInvoiceEmailBody,
            'estimate_mail_body' => $defaultEstimateEmailBody,
            'payment_mail_body' => $defaultPaymentEmailBody,
            'invoice_company_address_format' => $companyAddressFormat,
            'invoice_shipping_address_format' => $shippingAddressFormat,
            'invoice_billing_address_format' => $billingAddressFormat,
            'estimate_company_address_format' => $companyAddressFormat,
            'estimate_shipping_address_format' => $shippingAddressFormat,
            'estimate_billing_address_format' => $billingAddressFormat,
            'payment_company_address_format' => $companyAddressFormat,
            'payment_from_customer_address_format' => $paymentFromCustomerAddress,
            'currency' => 3,
            'time_zone' => 'Asia/Kolkata',
            'language' => 'es',
            'fiscal_year' => '1-12',
            'carbon_date_format' => 'Y/m/d',
            'moment_date_format' => 'YYYY/MM/DD',
            'notification_email' => 'noreply@telcoges.in',
            'notify_invoice_viewed' => 'NO',
            'notify_estimate_viewed' => 'NO',
            'tax_per_item' => 'NO',
            'discount_per_item' => 'NO',
            'invoice_prefix' => 'INV',
            'invoice_auto_generate' => 'YES',
            'invoice_number_length' => 6,
            'invoice_email_attachment' => 'NO',
            'estimate_prefix' => 'EST',
            'estimate_auto_generate' => 'YES',
            'estimate_number_length' => 6,
            'estimate_email_attachment' => 'NO',
            'payment_prefix' => 'PAY',
            'payment_auto_generate' => 'YES',
            'payment_number_length' => 6,
            'payment_email_attachment' => 'NO',
            'save_pdf_to_disk' => 'NO',
        ];

        CompanySetting::setSettings($company_settings, $company->id);

        Address::create([
            'company_id' => $company->id,
            'country_id' => 205,
            'cif' => $request->cif,
            'phone' => $request->companyPhone,
            'zip' => $request->zip,
            'city' => $request->city,
            'state' => $request->state,
            'address_street_1' => $request->address_street_1,
            'address_street_2' => $request->address_street_2
        ]);

        Unit::create(['name' => 'box', 'company_id' => $company->id]);
        Unit::create(['name' => 'cm', 'company_id' => $company->id]);
        Unit::create(['name' => 'dz', 'company_id' => $company->id]);
        Unit::create(['name' => 'ft', 'company_id' => $company->id]);
        Unit::create(['name' => 'g', 'company_id' => $company->id]);
        Unit::create(['name' => 'in', 'company_id' => $company->id]);
        Unit::create(['name' => 'kg', 'company_id' => $company->id]);
        Unit::create(['name' => 'km', 'company_id' => $company->id]);
        Unit::create(['name' => 'lb', 'company_id' => $company->id]);
        Unit::create(['name' => 'mg', 'company_id' => $company->id]);
        Unit::create(['name' => 'pc', 'company_id' => $company->id]);

        PaymentMethod::create(['name' => 'Cash', 'company_id' => $company->id]);
        PaymentMethod::create(['name' => 'Check', 'company_id' => $company->id]);
        PaymentMethod::create(['name' => 'Credit Card', 'company_id' => $company->id]);
        PaymentMethod::create(['name' => 'Bank Transfer', 'company_id' => $company->id]);

        return response()->json([
            'company' => $company,
            'success' => true
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);
        $company = Company::find($user->company_id);
        $address = Address::where('company_id', $user->company_id)->first();
        
        $company_data = [
            'id' => $id,
            'companyName' => $company->name,
            'companyPhone' => $address->phone,
            'state' => $address->state,
            'city' => $address->city,
            'zip' => $address->zip,
            'cif' => $address->cif,
            'address_street_1' => $address->address_street_1,
            'address_street_2' => $address->address_street_2,
            'name' => $user->name,
            'email' => $user->email
        ];

        return response()->json([
            'success' => true
        ]);
    }

    public function update(CompaniesRequest $request)
    {
        $request->validated();
        
        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' =>  $request->password,
        //     'role' => 'admin',
        //     'verify_token' => Str::random(20),
        //     'company_id' => $company->id
        // ]);
        $user = User::where('id', $request->id)->first();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        Company::where('id', $user->company_id)->update([
            'name' => $request->companyName,
        ]);

        Address::where('company_id', $user->company_id)->update([
            'cif' => $request->cif,
            'phone' => $request->companyPhone,
            'zip' => $request->zip,
            'city' => $request->city,
            'state' => $request->state,
            'address_street_1' => $request->address_street_1,
            'address_street_2' => $request->address_street_2
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        $companies = User::whereIn('id', $request->companies)->pluck('company_id')->toArray();
        
        DB::beginTransaction();
        try {
            $user_d = User::whereIn('id', $request->companies)->delete();
            $address_d = Address::whereIn('company_id', $companies)->delete();
            $payment_method_d = PaymentMethod::whereIn('company_id', $companies)->delete();
            $unit_d = Unit::whereIn('company_id', $companies)->delete();
            $company_settings_d = CompanySetting::whereIn('company_id', $companies)->delete();
            $company_d = Company::whereIn('id', $companies)->delete();
            
            DB::commit();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}