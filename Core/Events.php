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

        self::installCmsSnippets($oDb);
        self::installStaticUrl($oDb);
    }

    /**
     * Seed editable mail bodies as CMS snippets so the admin can change wording
     * via OXID admin -> Customer Info -> CMS Pages without touching template files.
     *
     * Both columns are seeded: OXCONTENT (DE = default lang 0) and OXCONTENT_1 (EN = lang 1).
     * INSERT IGNORE keeps existing edits intact on re-activation.
     */
    protected static function installCmsSnippets($oDb)
    {
        // NOTE: every user-controlled value is rendered through `|escape` so the
        // resulting HTML email body cannot smuggle <script>, <img onerror=...>, etc.
        // Ordernr is INT, OXORDERDATE is a server-formatted timestamp; both are safe
        // without escape but kept consistent for defense-in-depth.

        $sCustomerDe = self::trimSnippet('
Sehr geehrte/r [{$submission->bmwiderruf__oxname->value|escape}],

wir bestätigen den Eingang Ihrer Widerrufserklärung mit folgenden Angaben:

Name: [{$submission->bmwiderruf__oxname->value|escape}]
Bestellnummer: [{$submission->bmwiderruf__oxordernr->value|escape}]
E-Mail: [{$submission->bmwiderruf__oxemail->value|escape}]
Eingangsdatum: [{$submission->bmwiderruf__oxcreated->value|escape}]
[{if $submission->bmwiderruf__oxmessage->value}]
Grund: [{$submission->bmwiderruf__oxmessage->value|escape}]
[{/if}]

Unser Team meldet sich in Kürze bei Ihnen, um die Rückabwicklung zu besprechen.

Mit freundlichen Grüßen
[{$shop->oxshops__oxname->value|escape}]
        ');

        $sCustomerEn = self::trimSnippet('
Dear [{$submission->bmwiderruf__oxname->value|escape}],

we confirm receipt of your cancellation declaration with the following details:

Name: [{$submission->bmwiderruf__oxname->value|escape}]
Order number: [{$submission->bmwiderruf__oxordernr->value|escape}]
E-mail: [{$submission->bmwiderruf__oxemail->value|escape}]
Received at: [{$submission->bmwiderruf__oxcreated->value|escape}]
[{if $submission->bmwiderruf__oxmessage->value}]
Reason: [{$submission->bmwiderruf__oxmessage->value|escape}]
[{/if}]

Our team will get back to you shortly to arrange the return.

Kind regards
[{$shop->oxshops__oxname->value|escape}]
        ');

        $sAdminDe = self::trimSnippet('
Ein Kunde hat über das Widerrufsformular auf der Webseite folgende Erklärung abgegeben:

Name: [{$submission->bmwiderruf__oxname->value|escape}]
Bestellnummer: [{$submission->bmwiderruf__oxordernr->value|escape}]
E-Mail: [{$submission->bmwiderruf__oxemail->value|escape}]
Eingangsdatum: [{$submission->bmwiderruf__oxcreated->value|escape}]
IP: [{$submission->bmwiderruf__oxip->value|escape}]
[{if $submission->bmwiderruf__oxmessage->value}]
Grund:
[{$submission->bmwiderruf__oxmessage->value|escape}]
[{/if}]

Bitte die Bestellnummer im OXID-Admin suchen.
        ');

        $sAdminEn = self::trimSnippet('
A customer submitted the following cancellation request through the website form:

Name: [{$submission->bmwiderruf__oxname->value|escape}]
Order number: [{$submission->bmwiderruf__oxordernr->value|escape}]
E-mail: [{$submission->bmwiderruf__oxemail->value|escape}]
Received at: [{$submission->bmwiderruf__oxcreated->value|escape}]
IP: [{$submission->bmwiderruf__oxip->value|escape}]
[{if $submission->bmwiderruf__oxmessage->value}]
Reason:
[{$submission->bmwiderruf__oxmessage->value|escape}]
[{/if}]

Please look up the order number in the OXID admin.
        ');

        $aSnippets = [
            [
                'oxid'       => 'bmwiderrufmailcust1234567890abcdef',
                'loadid'     => 'bmwiderruf_customer',
                'titleDe'    => 'Widerruf – Bestätigungsmail an Kunde',
                'titleEn'    => 'Cancellation – Confirmation mail to customer',
                'contentDe'  => $sCustomerDe,
                'contentEn'  => $sCustomerEn,
            ],
            [
                'oxid'       => 'bmwiderrufmailadmin234567890abcde',
                'loadid'     => 'bmwiderruf_admin',
                'titleDe'    => 'Widerruf – Benachrichtigung an Admin',
                'titleEn'    => 'Cancellation – Notification to shop',
                'contentDe'  => $sAdminDe,
                'contentEn'  => $sAdminEn,
            ],
        ];

        foreach ($aSnippets as $a) {
            $sSql = "INSERT IGNORE INTO `oxcontents`
                (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`,
                 `OXPOSITION`, `OXTITLE`, `OXTITLE_1`, `OXCONTENT`, `OXCONTENT_1`,
                 `OXCATID`, `OXFOLDER`, `OXTERMVERSION`)
                VALUES (?, ?, 1, 1, 0, 1, 1, '', ?, ?, ?, ?, '', 'CMSFOLDER_EMAILS', '')";

            $oDb->execute(
                $sSql,
                [
                    $a['oxid'],
                    $a['loadid'],
                    $a['titleDe'],
                    $a['titleEn'],
                    $a['contentDe'],
                    $a['contentEn'],
                ]
            );
        }
    }

    protected static function trimSnippet($s)
    {
        // Strip the leading/trailing newlines added by heredoc formatting;
        // keep interior whitespace exactly as written.
        return trim($s, "\n");
    }

    /**
     * Register /widerruf as a clean static URL so the page is reachable
     * without the ?cl=widerruf query string. Inserts one oxseo row per
     * active shop language. INSERT IGNORE keeps admin-overridden mappings.
     */
    protected static function installStaticUrl($oDb)
    {
        $iShopId = 1;
        $aLangs  = [0, 1];
        $sStdUrl = 'index.php?cl=widerruf';
        $sSeoUrl = 'widerruf/';
        $sOxid   = 'bmwiderrufseourl1234567890abcdef';

        foreach ($aLangs as $iLang) {
            $sIdent = md5(strtolower($sSeoUrl) . $iLang . $iShopId);
            $oDb->execute(
                "INSERT IGNORE INTO `oxseo`
                 (`OXOBJECTID`, `OXIDENT`, `OXSHOPID`, `OXLANG`, `OXSTDURL`, `OXSEOURL`,
                  `OXTYPE`, `OXFIXED`, `OXEXPIRED`, `OXPARAMS`)
                 VALUES (?, ?, ?, ?, ?, ?, 'static', 1, 0, '')",
                [$sOxid, $sIdent, $iShopId, $iLang, $sStdUrl, $sSeoUrl]
            );
        }
    }
}
