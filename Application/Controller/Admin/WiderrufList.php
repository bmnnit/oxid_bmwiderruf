<?php

namespace Bmnnit\bmWiderruf\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\DatabaseProvider;

class WiderrufList extends AdminController
{
    protected $_sThisTemplate = '@bmnnit_widerruf/bm_widerruf_list.html.twig';

    public function render()
    {
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $aRows = $oDb->getAll(
            "SELECT w.OXID, w.OXORDERNR, w.OXNAME, w.OXEMAIL, w.OXMESSAGE, w.OXIP, w.OXCREATED, w.OXORDERID,
                    o.OXBILLFNAME AS BILLFNAME, o.OXBILLLNAME AS BILLLNAME, o.OXORDERDATE
             FROM bmwiderruf w
             LEFT JOIN oxorder o ON o.OXID = w.OXORDERID
             ORDER BY w.OXCREATED DESC
             LIMIT 500"
        );

        $this->_aViewData['rows']    = $aRows ?: [];
        $this->_aViewData['rowsCnt'] = is_array($aRows) ? count($aRows) : 0;

        parent::render();
        return '@bmnnit_widerruf/bm_widerruf_list.html.twig';
    }
}
