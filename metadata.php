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

    // Twig templates in views/twig/ are auto-discovered under the @bmnnit_widerruf
    // namespace, so no 'templates' map is needed (OXID 7.3 convention).

    'settings' => [
        [
            'group' => 'main',
            'name'  => 'iBmWiderrufMaxAgeDays',
            'type'  => 'num',
            'value' => 30,
        ],
    ],
];
