<?php

namespace Bmnnit\bmWiderruf\Core;

use Bmnnit\bmWiderruf\Application\Model\Widerruf;
use OxidEsales\Eshop\Core\Email as CoreEmail;
use OxidEsales\Eshop\Core\Registry;

class Email extends CoreEmail
{
    /**
     * Sends two notification mails: one to the customer, one to the shop's order address.
     */
    public function sendWiderrufMails(Widerruf $oSubmission, array $aOrder)
    {
        $blCustomerOk = $this->sendCustomerMail($oSubmission, $aOrder);
        $blAdminOk    = $this->sendAdminMail($oSubmission, $aOrder);

        return $blCustomerOk && $blAdminOk;
    }

    protected function sendCustomerMail(Widerruf $oSubmission, array $aOrder)
    {
        $oShop = $this->_getShop();
        $this->_setMailParams($oShop);

        $oLang  = Registry::getLang();
        $iLang  = $oLang->getBaseLanguage();

        $this->setViewData('submission', $oSubmission);
        $this->setViewData('order',      $aOrder);
        $this->setViewData('oEmailView', $this);
        $this->_processViewArray();

        $renderer = $this->getRenderer();
        $this->setBody($renderer->renderTemplate('widerruf_customer_html.tpl', $this->getViewData()));
        $this->setAltBody($renderer->renderTemplate('widerruf_customer_plain.tpl', $this->getViewData()));

        $this->setSubject($oLang->translateString('BMNNIT_WIDERRUF_EMAIL_SUBJECT_CUSTOMER', $iLang, false));

        $this->setRecipient($oSubmission->bmwiderruf__oxemail->value, trim($aOrder['OXBILLFNAME'] . ' ' . $aOrder['OXBILLLNAME']));
        $this->setReplyTo($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());

        return $this->send();
    }

    protected function sendAdminMail(Widerruf $oSubmission, array $aOrder)
    {
        // Fresh mailer instance: PHPMailer state from the customer mail (recipients, body) must not leak.
        $oMail  = oxNew(self::class);
        $oShop  = $oMail->_getShop();
        $oMail->_setMailParams($oShop);

        $oLang = Registry::getLang();
        $iLang = $oLang->getBaseLanguage();

        $oMail->setViewData('submission', $oSubmission);
        $oMail->setViewData('order',      $aOrder);
        $oMail->setViewData('oEmailView', $oMail);
        $oMail->_processViewArray();

        $renderer = $oMail->getRenderer();
        $oMail->setBody($renderer->renderTemplate('widerruf_admin_html.tpl', $oMail->getViewData()));
        $oMail->setAltBody($renderer->renderTemplate('widerruf_admin_plain.tpl', $oMail->getViewData()));

        $oMail->setSubject($oLang->translateString('BMNNIT_WIDERRUF_EMAIL_SUBJECT_ADMIN', $iLang, false));

        $oMail->setRecipient($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());
        $oMail->setReplyTo($oSubmission->bmwiderruf__oxemail->value, trim($aOrder['OXBILLFNAME'] . ' ' . $aOrder['OXBILLLNAME']));

        return $oMail->send();
    }
}
