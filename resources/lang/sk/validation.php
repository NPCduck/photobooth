<?php

return [
    'required' => 'Toto pole je povinné.',
    'string' => 'Pole :attribute musí byť text.',
    'max' => [
        'string' => 'Toto pole môže mať najviac :max znakov.',
        'numeric' => 'Toto pole môže byť najviac :max.',
    ],
    'min' => [
        'string' => 'Toto pole musí mať aspoň :min znakov.',
        'numeric' => 'Toto pole musí byť aspoň :min.',
    ],
    'integer' => 'Toto pole musí byť celé číslo.',
    'numeric' => 'Toto pole musí byť číslo.',
    'date' => 'Toto pole musí byť platný dátum.',
    'date_format' => 'Toto pole musí mať formát :format.',
    'email' => 'Toto pole musí byť platný e-mail.',
    'file' => 'Toto pole musí byť súbor.',
    'mimetypes' => 'Toto pole musí byť typu: :values.',
    'nullable' => 'Toto pole môže byť prázdne.',
    
    // a ďalšie pravidlá podľa potreby
    // 'unique', 'confirmed', 'regex' atď.

    /*
    |--------------------------------------------------------------------------
    | Prívetivé názvy polí
    |--------------------------------------------------------------------------
    |
    | Tu môžeš premenovať interné názvy polí na čitateľnejšie pre užívateľa.
    |
    */

    'attributes' => [ 
        'name' => 'názov eventu',
        'details.type' => 'typ eventu',
        'details.date' => 'dátum eventu',
        'details.time_start' => 'čas začiatku',
        'details.time_end' => 'čas konca',
        'details.status' => 'status eventu',
        'details.hosts' => 'počet hostí',
        'details.loc_venue' => 'miesto konania',
        'details.loc_address' => 'celá adresa',
        
        'client.name' => 'meno klienta',
        'client.email' => 'email klienta',
        'client.phone' => 'telefónne číslo klienta',

        // balíčky
        'packages.*.name' => 'názov balíčka',
        'packages.*.price' => 'cena balíčka',
        'packages.*.photo_limit_total' => 'celkový limit fotiek',
        'packages.*.photo_limit_person' => 'počet fotiek na osobu',

        // overlay obrázky
        'overlays.landing_img' => 'obrázok hlavnej stránky',
        'overlays.frame_img' => 'obrázok rámu kamery',
    ],

];
