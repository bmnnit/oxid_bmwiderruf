[{capture append="oxidBlock_content"}]
    <h1 class="page-header">[{oxmultilang ident="BMNNIT_WIDERRUF_PAGETITLE"}]</h1>

    <div class="row">
        <div class="col-xs-12 col-lg-9">
            <p>[{oxmultilang ident="BMNNIT_WIDERRUF_INTRO"}]</p>

            [{include file="form/widerruf.tpl"}]
        </div>
    </div>

    [{insert name="oxid_tracker" title=$template_title}]
[{/capture}]

[{include file="layout/page.tpl"}]
