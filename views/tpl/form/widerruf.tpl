[{assign var="vals" value=$oView->getFormValues()}]
<form class="form-horizontal" action="[{$oViewConf->getSslSelfLink()}]" method="post" novalidate="novalidate">
    <div class="hidden">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="fnc" value="send"/>
        <input type="hidden" name="cl" value="widerruf"/>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-2" for="widerruf_name">
            [{oxmultilang ident="BMNNIT_WIDERRUF_NAME"}]
        </label>
        <div class="col-lg-10 controls">
            <input type="text"
                   name="name"
                   id="widerruf_name"
                   size="70"
                   maxlength="255"
                   value="[{$vals.name|escape}]"
                   class="form-control"
                   required="required">
        </div>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-2" for="widerruf_ordernr">
            [{oxmultilang ident="BMNNIT_WIDERRUF_ORDERNR"}]
        </label>
        <div class="col-lg-10 controls">
            <input type="text"
                   name="ordernr"
                   id="widerruf_ordernr"
                   size="70"
                   maxlength="20"
                   value="[{$vals.ordernr|escape}]"
                   class="form-control"
                   required="required">
        </div>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-2" for="widerruf_email">
            [{oxmultilang ident="BMNNIT_WIDERRUF_EMAIL"}]
        </label>
        <div class="col-lg-10 controls">
            <input type="email"
                   name="email"
                   id="widerruf_email"
                   size="70"
                   maxlength="255"
                   value="[{$vals.email|escape}]"
                   class="form-control"
                   required="required">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2" for="widerruf_message">
            [{oxmultilang ident="BMNNIT_WIDERRUF_REASON"}]
        </label>
        <div class="col-lg-10 controls">
            <textarea rows="8"
                      cols="70"
                      name="message"
                      id="widerruf_message"
                      class="form-control">[{$vals.message|escape}]</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <p class="alert alert-info">[{oxmultilang ident="COMPLETE_MARKED_FIELDS"}]</p>
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-envelope"></i> [{oxmultilang ident="BMNNIT_WIDERRUF_SUBMIT"}]
            </button>
            <p class="text-muted" style="margin-top:10px">
                [{oxmultilang ident="BMNNIT_WIDERRUF_BUTTON_DISCLAIMER"}]
            </p>
        </div>
    </div>
</form>
