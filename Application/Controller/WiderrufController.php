<?php

namespace Bmnnit\bmWiderruf\Application\Controller;

use Bmnnit\bmWiderruf\Application\Model\Widerruf;
use Bmnnit\bmWiderruf\Core\Email;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class WiderrufController extends FrontendController
{
    protected $_sThisTemplate = 'page/info/widerruf.tpl';
    protected $_iViewIndexState = VIEW_INDEXSTATE_NOINDEXNOFOLLOW;

    /** @var bool */
    protected $blSendStatus = false;

    /** @var array submitted values for re-render on error */
    protected $aFormValues = ['name' => '', 'ordernr' => '', 'email' => '', 'message' => ''];

    public function send()
    {
        // Raw values (no automatic HTML-escape): storage stays faithful to user input
        // and every template/CMS-snippet output is escaped explicitly via Smarty |escape.
        $oRequest = Registry::getRequest();
        $sName    = trim((string) $oRequest->getRequestParameter('name'));
        $sOrderNr = trim((string) $oRequest->getRequestParameter('ordernr'));
        $sEmail   = trim((string) $oRequest->getRequestParameter('email'));
        $sMessage = (string) $oRequest->getRequestParameter('message');

        $this->aFormValues = [
            'name'    => $sName,
            'ordernr' => $sOrderNr,
            'email'   => $sEmail,
            'message' => $sMessage,
        ];

        if ($sName === '' || $sOrderNr === '' || $sEmail === '') {
            Registry::getUtilsView()->addErrorToDisplay('BMNNIT_WIDERRUF_ERR_REQUIRED');
            return;
        }

        if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
            Registry::getUtilsView()->addErrorToDisplay('BMNNIT_WIDERRUF_ERR_INVALID_EMAIL');
            return;
        }

        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $sSql = 'SELECT OXID, OXORDERNR, OXBILLEMAIL, OXBILLFNAME, OXBILLLNAME, OXORDERDATE
                 FROM oxorder
                 WHERE OXORDERNR = ? AND OXBILLEMAIL = ?
                 LIMIT 1';
        $aOrder = $oDb->getRow($sSql, [(int) $sOrderNr, $sEmail]);

        if (empty($aOrder) || empty($aOrder['OXID'])) {
            Registry::getUtilsView()->addErrorToDisplay('BMNNIT_WIDERRUF_ERR_NOMATCH');
            return;
        }

        $iMaxAge = (int) Registry::getConfig()->getConfigParam('iBmWiderrufMaxAgeDays');
        if ($iMaxAge > 0 && !empty($aOrder['OXORDERDATE'])) {
            $iOrderTs = strtotime($aOrder['OXORDERDATE']);
            if ($iOrderTs !== false && $iOrderTs < strtotime('-' . $iMaxAge . ' days')) {
                $sErr = sprintf(
                    Registry::getLang()->translateString('BMNNIT_WIDERRUF_ERR_TOO_OLD', Registry::getLang()->getBaseLanguage(), false),
                    $iMaxAge
                );
                Registry::getUtilsView()->addErrorToDisplay($sErr);
                return;
            }
        }

        $oSubmission = oxNew(Widerruf::class);
        $oSubmission->bmwiderruf__oxshopid  = new Field((int) Registry::getConfig()->getShopId(), Field::T_RAW);
        $oSubmission->bmwiderruf__oxorderid = new Field($aOrder['OXID'], Field::T_RAW);
        $oSubmission->bmwiderruf__oxordernr = new Field((int) $aOrder['OXORDERNR'], Field::T_RAW);
        $oSubmission->bmwiderruf__oxname    = new Field($sName, Field::T_RAW);
        $oSubmission->bmwiderruf__oxemail   = new Field($sEmail, Field::T_RAW);
        $oSubmission->bmwiderruf__oxmessage = new Field($sMessage, Field::T_RAW);
        $oSubmission->bmwiderruf__oxip      = new Field(Registry::getUtilsServer()->getRemoteAddress(), Field::T_RAW);
        // BaseModel sends every field on save, which would write
        // '0000-00-00 00:00:00' for OXCREATED and override the DB default.
        // Set the timestamp explicitly so the mail body shows the real time.
        $oSubmission->bmwiderruf__oxcreated = new Field(date('Y-m-d H:i:s'), Field::T_RAW);
        $oSubmission->save();

        $oMailer = oxNew(Email::class);
        $oMailer->sendWiderrufMails($oSubmission, $aOrder);

        $this->blSendStatus = true;
        $this->_sThisTemplate = 'page/info/widerruf_thanks.tpl';
    }

    public function getSendStatus()
    {
        return $this->blSendStatus;
    }

    public function getFormValues()
    {
        return $this->aFormValues;
    }

    public function getBreadCrumb()
    {
        $sTitle = Registry::getLang()->translateString(
            'BMNNIT_WIDERRUF_PAGETITLE',
            Registry::getLang()->getBaseLanguage(),
            false
        );

        return [[
            'title' => $sTitle,
            'link'  => $this->getLink(),
        ]];
    }

    public function getTitle()
    {
        return Registry::getLang()->translateString(
            'BMNNIT_WIDERRUF_PAGETITLE',
            Registry::getLang()->getBaseLanguage(),
            false
        );
    }
}
