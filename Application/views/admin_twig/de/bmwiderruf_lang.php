<?php

$sLangName = 'Deutsch';

$aLang = [
    'charset' => 'UTF-8',

    // menu.xml node ids — must match the SUBMENU/TAB id="..."
    'bm_widerruf_list'                  => 'Widerrufe',

    // list page
    'BMNNIT_WIDERRUF_ADMIN_TITLE'       => 'Eingegangene Widerrufe',
    'BMNNIT_WIDERRUF_ADMIN_COUNT'       => 'Anzahl',
    'BMNNIT_WIDERRUF_ADMIN_EMPTY'       => 'Es liegen noch keine Widerrufs-Anfragen vor.',
    'BMNNIT_WIDERRUF_ADMIN_CREATED'     => 'Eingegangen am',
    'BMNNIT_WIDERRUF_ADMIN_CUSTOMER'    => 'Kunde',

    // shared form-label keys (reused from frontend, also rendered in the list)
    'BMNNIT_WIDERRUF_ORDERNR'           => 'Bestellnummer',
    'BMNNIT_WIDERRUF_EMAIL'             => 'E-Mail',
    'BMNNIT_WIDERRUF_REASON'            => 'Grund',

    // module-settings group + field labels
    'SHOP_MODULE_GROUP_main'            => 'Allgemein',
    'SHOP_MODULE_iBmWiderrufMaxAgeDays' => 'Maximales Bestellalter in Tagen (0 = keine Begrenzung)',
];
