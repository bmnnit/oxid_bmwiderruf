[{include file="headitem.tpl" title="BMNNIT_WIDERRUF_ADMIN_TITLE"|oxmultilangassign box=" "}]

<h2 style="padding:8px 12px 0">[{oxmultilang ident="BMNNIT_WIDERRUF_ADMIN_TITLE"}]</h2>
<p class="listitem" style="padding:0 12px 8px">
    [{oxmultilang ident="BMNNIT_WIDERRUF_ADMIN_COUNT"}]: [{$rowsCnt}]
</p>

[{if $rowsCnt == 0}]
    <p class="listitem" style="padding:8px 12px">
        <b>[{oxmultilang ident="BMNNIT_WIDERRUF_ADMIN_EMPTY"}]</b>
    </p>
[{else}]
    <table cellspacing="0" cellpadding="0" border="0" width="99%" style="margin:8px 4px">
        <colgroup>
            <col width="14%">
            <col width="9%">
            <col width="18%">
            <col width="14%">
            <col width="35%">
            <col width="10%">
        </colgroup>
        <tr>
            <td class="listheader">[{oxmultilang ident="BMNNIT_WIDERRUF_ADMIN_CREATED"}]</td>
            <td class="listheader">[{oxmultilang ident="BMNNIT_WIDERRUF_ORDERNR"}]</td>
            <td class="listheader">[{oxmultilang ident="BMNNIT_WIDERRUF_EMAIL"}]</td>
            <td class="listheader">[{oxmultilang ident="BMNNIT_WIDERRUF_ADMIN_CUSTOMER"}]</td>
            <td class="listheader">[{oxmultilang ident="BMNNIT_WIDERRUF_REASON"}]</td>
            <td class="listheader">IP</td>
        </tr>
        [{assign var="blWhite" value=""}]
        [{foreach from=$rows item=row}]
            <tr>
                <td class="listitem[{$blWhite}]" valign="top" nowrap>[{$row.OXCREATED|escape}]</td>
                <td class="listitem[{$blWhite}]" valign="top">[{$row.OXORDERNR|escape}]</td>
                <td class="listitem[{$blWhite}]" valign="top">[{$row.OXEMAIL|escape}]</td>
                <td class="listitem[{$blWhite}]" valign="top">[{$row.BILLFNAME|escape}] [{$row.BILLLNAME|escape}]</td>
                <td class="listitem[{$blWhite}]" valign="top">[{$row.OXMESSAGE|escape|nl2br}]</td>
                <td class="listitem[{$blWhite}]" valign="top">[{$row.OXIP|escape}]</td>
            </tr>
            [{if $blWhite == "2"}]
                [{assign var="blWhite" value=""}]
            [{else}]
                [{assign var="blWhite" value="2"}]
            [{/if}]
        [{/foreach}]
    </table>
[{/if}]

<script type="text/javascript">
if (parent.parent) {
    parent.parent.sShopTitle   = "[{$actshopobj->oxshops__oxname->getRawValue()|oxaddslashes}]";
    parent.parent.sMenuItem    = "[{oxmultilang ident="mxorders"}]";
    parent.parent.sMenuSubItem = "[{oxmultilang ident="bm_widerruf_list"}]";
    parent.parent.setTitle();
}
</script>
</body>
</html>
