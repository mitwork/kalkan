<?php

return [
    'ncanode' => [
        'host' => env('NCANODE_HOST', 'http://localhost:14579'),
    ],
    'links' => [
        'prefix' => 'mobileSign:%s',
        'person' => 'https://mgovsign.page.link/?link=%s&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov',
        'legal' => 'https://egovbusiness.page.link/?link=%s&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business',
    ],
];
