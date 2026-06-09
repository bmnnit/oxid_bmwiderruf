<?php

$sMetadataVersion = '2.1';

define('BMWIDERRUF_PLUGIN_VERSION', '1.0.0');

$aModule = [
    'id'          => 'bmnnit_widerruf',
    'title'       => '[BM] Bmnnit Widerruf-Formular',
    'description' => 'Standalone Widerruf-Formular (/widerruf): Kunde gibt Bestellnummer und E-Mail an; bei Treffer wird die Anfrage protokolliert und je eine Bestätigungsmail an Kunde und Shop versendet.',
    'thumbnail'   => '_BmnnIT_3_65px_high.png',
    'version'     => BMWIDERRUF_PLUGIN_VERSION,
    'author'      => 'Baumann IT-Dienstleistungen',
    'email'       => 'info@bmnnit.com',
    'url'         => 'https://www.bmnnit.com',

    'controllers' => [
        'widerruf'          => \Bmnnit\bmWiderruf\Application\Controller\WiderrufController::class,
        'bm_widerruf_list'  => \Bmnnit\bmWiderruf\Application\Controller\Admin\WiderrufList::class,
    ],

    'events' => [
        'onActivate' => '\Bmnnit\bmWiderruf\Core\Events::onActivate',
    ],

    'templates' => [
        'page/info/widerruf.tpl'         => 'bmnnit/bmWiderruf/views/tpl/page/info/widerruf.tpl',
        'page/info/widerruf_thanks.tpl'  => 'bmnnit/bmWiderruf/views/tpl/page/info/widerruf_thanks.tpl',
        'form/widerruf.tpl'              => 'bmnnit/bmWiderruf/views/tpl/form/widerruf.tpl',
        'widerruf_customer_html.tpl'     => 'bmnnit/bmWiderruf/views/tpl/email/html/widerruf_customer.tpl',
        'widerruf_customer_plain.tpl'    => 'bmnnit/bmWiderruf/views/tpl/email/plain/widerruf_customer.tpl',
        'widerruf_admin_html.tpl'        => 'bmnnit/bmWiderruf/views/tpl/email/html/widerruf_admin.tpl',
        'widerruf_admin_plain.tpl'       => 'bmnnit/bmWiderruf/views/tpl/email/plain/widerruf_admin.tpl',
        'bm_widerruf_list.tpl'           => 'bmnnit/bmWiderruf/views/admin/tpl/bm_widerruf_list.tpl',
    ],

    'settings' => [
        [
            'group' => 'main',
            'name'  => 'iBmWiderrufMaxAgeDays',
            'type'  => 'num',
            'value' => 30,
        ],
    ],
];
