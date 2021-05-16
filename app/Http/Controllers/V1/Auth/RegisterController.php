<?php

namespace Crater\Http\Controllers\V1\Auth;

use Crater\Http\Controllers\Controller;
use Crater\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Crater\Models\User;
use Crater\Models\Company;
use Crater\Models\Setting;
use Crater\Models\CompanySetting;
use Crater\Models\Address;
use Crater\Models\PaymentMethod;
use Crater\Models\Unit;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if ( $validator->fails() ) {
            return response()->json([
                'success' => false,
                'message' => 'Error en los datos, por favor verifique e intente de nuevo.'
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'admin',
        ]);

        if (is_null($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar, intenta más tarde.'
            ]);
        }

        $company = Company::create([
            'unique_hash' => str_random(20)
        ]);

        $defaultInvoiceEmailBody = 'You have received a new invoice from <b>{COMPANY_NAME}</b>.</br>Please download using the button below:';
        $defaultEstimateEmailBody = 'You have received a new estimate from <b>{COMPANY_NAME}</b>.</br>Please download using the button below:';
        $defaultPaymentEmailBody = 'Thank you for the payment.</b></br>Please download your payment receipt using the button below:';
        $billingAddressFormat = '<h3>{BILLING_ADDRESS_NAME}</h3><p>{BILLING_ADDRESS_STREET_1}</p><p>{BILLING_ADDRESS_STREET_2}</p><p>{BILLING_CITY}  {BILLING_STATE}</p><p>{BILLING_COUNTRY}  {BILLING_ZIP_CODE}</p><p>{BILLING_PHONE}</p>';
        $shippingAddressFormat = '<h3>{SHIPPING_ADDRESS_NAME}</h3><p>{SHIPPING_ADDRESS_STREET_1}</p><p>{SHIPPING_ADDRESS_STREET_2}</p><p>{SHIPPING_CITY}  {SHIPPING_STATE}</p><p>{SHIPPING_COUNTRY}  {SHIPPING_ZIP_CODE}</p><p>{SHIPPING_PHONE}</p>';
        $companyAddressFormat = '<h3><strong>{COMPANY_NAME}</strong></h3><p>{COMPANY_ADDRESS_STREET_1}</p><p>{COMPANY_ADDRESS_STREET_2}</p><p>{COMPANY_CITY} {COMPANY_STATE}</p><p>{COMPANY_COUNTRY}  {COMPANY_ZIP_CODE}</p><p>{COMPANY_PHONE}</p>';
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
            'currency' => 1,
            'time_zone' => 'Asia/Kolkata',
            'language' => 'en',
            'fiscal_year' => '1-12',
            'carbon_date_format' => 'Y/m/d',
            'moment_date_format' => 'YYYY/MM/DD',
            'notification_email' => 'noreply@crater.in',
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

        Address::create(['company_id' => $company->id, 'country_id' => 205]);

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

        $user->company_id = $company->id;
        $user->verify_token = Str::random(20);
        $user->save();

        $user->setSettings(['language' => 'es']);

        Mail::to( $user->email )->send( new RegistrationVerifyLink( $user ) );

        return response()->json([
            'success' => true,
            'message' => 'Verifica tu email para completar el registro.'
        ]);
    }

    // public function verify(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();

    //     if (! is_null($user) ) {

    //     }
    // }
}
