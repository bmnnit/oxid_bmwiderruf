<?php

namespace Bmnnit\bmWiderruf\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;

class Events
{
    public static function onActivate()
    {
        $oDb = DatabaseProvider::getDb();

        $oDb->execute("CREATE TABLE IF NOT EXISTS `bmwiderruf` (
            `OXID`       CHAR(32)        NOT NULL,
            `OXSHOPID`   INT(11)         NOT NULL DEFAULT 1,
            `OXORDERID`  CHAR(32)        NOT NULL DEFAULT '',
            `OXORDERNR`  INT(11)         NOT NULL DEFAULT 0,
            `OXNAME`     VARCHAR(255)    NOT NULL DEFAULT '',
            `OXEMAIL`    VARCHAR(255)    NOT NULL DEFAULT '',
            `OXMESSAGE`  TEXT            NOT NULL,
            `OXIP`       VARCHAR(45)     NOT NULL DEFAULT '',
            `OXCREATED`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`OXID`),
            KEY `OXORDERID` (`OXORDERID`),
            KEY `OXORDERNR` (`OXORDERNR`),
            KEY `OXEMAIL`   (`OXEMAIL`),
            KEY `OXCREATED` (`OXCREATED`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Widerruf-Anfragen (Kunden-Submissions /widerruf)'");

        // Idempotent column add for installs that pre-date the OXNAME column.
        $bHasName = false;
        foreach ($oDb->metaColumns('bmwiderruf') as $col) {
            if (strtoupper($col->name) === 'OXNAME') {
                $bHasName = true;
            }
        }
        if (!$bHasName) {
            $oDb->execute("ALTER TABLE `bmwiderruf` ADD `OXNAME` VARCHAR(255) NOT NULL DEFAULT '' AFTER `OXORDERNR`");
        }

        // NOTE: kein automatisches Eintragen einer SEO-URL fuer /widerruf/ in
        // OXID 7 - der SEO-Encoder behandelt static URLs anders als in 6.5,
        // und ein manuelles INSERT in `oxseo` fuehrt zu 301→404 Loops.
        // Im OXID-Admin koennen statische URLs ueber Stammdaten -> Stamm -> ...
        // konfiguriert werden.
    }
}
