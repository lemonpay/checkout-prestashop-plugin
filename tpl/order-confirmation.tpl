{if $lemonpay_order.valid == 1}
<div class="conf confirmation">
	{l s='Congratulations! Your payment is pending verification, and your order has been saved under' mod='lemonpay'}{if isset($lemonpay_order.reference)} {l s='the reference' mod='lemonpay'} <b>{$lemonpay_order.reference|escape:html:'UTF-8'}</b>{else} {l s='the ID' mod='lemonpay'} <b>{$lemonpay_order.id|escape:html:'UTF-8'}</b>{/if}.
</div>
{else}
<div class="error">
	{l s='Unfortunately, an error occurred during the transaction.' mod='lemonpay'}<br /><br />
	{l s='Please double-check your credit card details and try again. If you need further assistance, feel free to contact us anytime.' mod='lemonpay'}<br /><br />
{if isset($lemonpay_order.reference)}
	({l s='Your Order\'s Reference:' mod='lemonpay'} <b>{$lemonpay_order.reference|escape:html:'UTF-8'}</b>)
{else}
	({l s='Your Order\'s ID:' mod='lemonpay'} <b>{$lemonpay_order.id|escape:html:'UTF-8'}</b>)
{/if}
</div>
{/if}
