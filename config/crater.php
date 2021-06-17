<?php

return [

    /*
    * Current version of the application.
    */
    'version' => '4.0.0',

    /*
    * List of languages supported by Crater.
    */
    'languages' => [
        ["code"=>"ar", "name" => "árabe"],
        ["code"=>"nl", "name" => "holandés"],
        ["code"=>"en", "name" => "inglés"],
        ["code"=>"fr", "name" => "francés"],
        ["code"=>"de", "name" => "alemán"],
        ["code"=>"it", "name" => "italiano"],
        ["code"=>"lv", "name" => "letona"],
        ["code"=>"pt_BR", "name" => "portugués (Brasileño)"],
        ["code"=>"sr", "name" => "latín serbio"],
        ["code"=>"es", "name" => "español"],
        ["code"=>"sv", "name"=> "svenska"],
        ["code"=>"sk", "name"=> "eslovaco"],
        ["code"=>"vi", "name"=> "tiếng việt"]

    ],

    /*
    * List of Fiscal Years
    */
    'fiscal_years' => [
        ['key' => 'enero-december' , 'value' => '1-12'],
        ['key' => 'febrero-enero' , 'value' => '2-1'],
        ['key' => 'marzo-febrero'   , 'value' => '3-2'],
        ['key' => 'abril-marzo'      , 'value' => '4-3'],
        ['key' => 'mayo-abril'        , 'value' => '5-4'],
        ['key' => 'junio-mayo'         , 'value' => '6-5'],
        ['key' => 'julio-junio'        , 'value' => '7-6'],
        ['key' => 'agosto-julio'      , 'value' => '8-7'],
        ['key' => 'septiembre-agosto' , 'value' => '9-8'],
        ['key' => 'octubre-septiembre', 'value' => '10-9'],
        ['key' => 'noviembre-octubre' , 'value' => '11-10'],
        ['key' => 'diciembre-noviembre', 'value' => '12-11'],
    ]
];
