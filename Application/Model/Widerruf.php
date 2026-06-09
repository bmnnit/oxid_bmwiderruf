<?php

namespace Bmnnit\bmWiderruf\Application\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

class Widerruf extends BaseModel
{
    protected $_sCoreTable = 'bmwiderruf';
    protected $_sClassName = 'Widerruf';

    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
    }
}
