<?php
use Crater\Models\CompanySetting;
use Crater\Models\Currency;
use Crater\Models\CustomField;
use Illuminate\Support\Str;

/**
 * Set Active Path
 *
 * @param $path
 * @param string $active
 * @return string
 */
function set_active($path, $active = 'active') {

    return call_user_func_array('Request::is', (array)$path) ? $active : '';

}

/**
 * @param $path
 * @return mixed
 */
function is_url($path)
{
    return call_user_func_array('Request::is', (array)$path);
}

/**
 * @param string $type
 * @return string
 */
function getCustomFieldValueKey(string $type)
{
    switch ($type) {
        case 'Input':
            return 'string_answer';

        case 'TextArea':
            return 'string_answer';

        case 'Phone':
            return 'number_answer';

        case 'Url':
            return 'string_answer';

        case 'Number':
            return 'number_answer';

        case 'Dropdown':
            return 'string_answer';

        case 'Switch':
            return 'boolean_answer';

        case 'Date':
            return 'date_answer';

        case 'Time':
            return 'time_answer';

        case 'DateTime':
            return 'date_time_answer';

        default:
            return 'string_answer';
    }
}

/**
 * @param $money
 * @return formated_money
 */
function format_money_pdf($money, $currency = null)
{
    $money = $money / 100;

    $company_id = auth()->user()->company_id;

    if (!$currency) {
        $currency = Currency::findOrFail(CompanySetting::getSetting('currency', $company_id));
    }

    $format_money = number_format(
        $money,
        $currency->precision,
        $currency->decimal_separator,
        $currency->thousand_separator
    );

    $currency_with_symbol = '';
    if ($currency->swap_currency_symbol) {
        $currency_with_symbol = $format_money.'<span style="font-family: DejaVu Sans;">'.$currency->symbol.'</span>';
    } else {
        $currency_with_symbol = '<span style="font-family: DejaVu Sans;">'.$currency->symbol.'</span>'.$format_money;
    }
    return $currency_with_symbol;
}

/**
 * @param $string
 * @return string
 */
function clean_slug($model, $title, $id = 0)
{
    // Normalize the title
    $slug = Str::upper('CUSTOM_'.$model.'_'.Str::slug($title, '_'));

    // Get any that could possibly be related.
    // This cuts the queries down by doing it once.
    $allSlugs = getRelatedSlugs($model, $slug, $id);

    // If we haven't used it before then we are all good.
    if (!$allSlugs->contains('slug', $slug)) {
        return $slug;
    }

    // Just append numbers like a savage until we find not used.
    for ($i = 1; $i <= 10; $i++) {
        $newSlug = $slug . '_' . $i;
        if (!$allSlugs->contains('slug', $newSlug)) {
            return $newSlug;
        }
    }

    throw new \Exception('Can not create a unique slug');
}

function getRelatedSlugs($type, $slug, $id = 0)
{
    return CustomField::select('slug')->where('slug', 'like', $slug . '%')
        ->where('model_type', $type)
        ->where('id', '<>', $id)
        ->get();
}

if (!function_exists('base64_file_upload')) {
    function base64_file_upload($file_name, $data)
    {
        $base64_str = substr($data, strpos($data, ",")+1);
        $image = base64_decode($base64_str);
        $png_url = uniqid() . time() . $file_name;
        $path = public_path('uploads/' . $png_url);

        file_put_contents($path, $image);

        return $png_url;
    }
}

if (!function_exists('file_remove')) {
    function file_remove($file_name)
    {
        $file_path = public_path('uploads/' . $file_name);
        if(File::exists($file_path)) {
            File::delete($file_path);
        }
    }
}