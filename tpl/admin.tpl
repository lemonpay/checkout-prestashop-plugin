{$lemonpay_confirmation}
<link href="{$module_dir}css/lemonpay.css" rel="stylesheet" type="text/css">
    <img alt="" src="{$lemonpay_tracking|escape:'htmlall':'UTF-8'}" style="display: none;"/>
    <div class="lemonpay-header">
        <img alt="lemonpay" class="lemonpay-logo" src="{$module_dir}logo.png"/>
    </div>
</link>
<form action="{$lemonpay_form|escape:'htmlall':'UTF-8'}" class="defaultForm form-horizontal" id="module_form" method="post">
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs">
            </i>
            Settings
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_prefix">
                    {l s='Phone Prefix:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" id="lemonpay_prefix" name="lemonpay_prefix" type="text" value="{$lemonpay_prefix|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_phone">
                    {l s='Phone No:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" id="lemonpay_phone" name="lemonpay_phone" type="text" value="{$lemonpay_phone|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_slug">
                    {l s='Lemonpay Slug:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" disabled="" id="lemonpay_slug" name="lemonpay_slug" type="text" value="{$lemonpay_slugval|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            {if $lemonpay_slug == ''}
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_name">
                    {l s='Shop Name:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" id="lemonpay_name" name="lemonpay_name" type="text" value="{$lemonpay_name|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_img">
                    {l s='Shop Image URL:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" id="lemonpay_img" name="lemonpay_img" type="text" value="{$lemonpay_img|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_pcolor">
                    {l s='Primary Color' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" id="lemonpay_pcolor" name="lemonpay_pcolor" type="text" value="{$lemonpay_pcolor|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_scolor">
                    {l s='Secondary Color' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-tag">
                            </i>
                        </span>
                        <input class="text" id="lemonpay_scolor" name="lemonpay_scolor" type="text" value="{$lemonpay_scolor|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
            </div>
            {/if}
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_imode">
                    {l s='Pre(Sandbox) Mode:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <select class="form-control" id="input-mode" name="lemonpay_mode">
                        {if $lemonpay_mode == 'Y'}
                        <option selected="selected" value="Y">
                            {l s='Yes' mod='lemonpay'}
                        </option>
                        <option value="N">
                            {l s='No' mod='lemonpay'}
                        </option>
                        {else}
                        <option value="Y">
                            {l s='Yes' mod='lemonpay'}
                        </option>
                        <option selected="selected" value="N">
                            {l s='No' mod='lemonpay'}
                        </option>
                        {/if}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="lemonpay_order_status">
                    {l s='Success Order Status:' mod='lemonpay'}
                </label>
                <div class="col-lg-3">
                    <select class="form-control" id="input-transaction-method" name="lemonpay_order_status">
                        {foreach from=$orderstates key='ordid' item='ordname'}
                        <option $ordid="$lemonpay_order_status}" if}="" selected="selected" value="{$ordid}" {="" {if="">
                            {$ordname}
                        </option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-default pull-right" id="module_form_submit_btn" name="submitlemonpay" type="submit" value="1">
                <i class="process-icon-save">
                </i>
                Save
            </button>
        </div>
    </div>
</form>
