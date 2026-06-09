[{capture append="oxidBlock_content"}]
    <h1 class="page-header">[{oxmultilang ident="BMNNIT_WIDERRUF_THANKS_TITLE"}]</h1>

    <div class="row">
        <div class="col-xs-12 col-lg-9">
            [{assign var="_statusMessage" value="BMNNIT_WIDERRUF_THANKS_BODY"|oxmultilangassign}]
            [{include file="message/notice.tpl" statusMessage=$_statusMessage}]
        </div>
    </div>

    [{insert name="oxid_tracker" title=$template_title}]
[{/capture}]

[{include file="layout/page.tpl"}]
