[{assign var="shop" value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]

[{include file="email/html/header.tpl" title=$shop->oxshops__oxname->value}]

    [{oxifcontent ident="bmwiderruf_admin" object="oCont"}]
        [{$oCont->oxcontents__oxcontent->value|nl2br}]
    [{/oxifcontent}]

[{include file="email/html/footer.tpl"}]
