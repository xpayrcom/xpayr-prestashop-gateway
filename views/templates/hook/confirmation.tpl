<div class="box">
    {if $status == 'ok'}
        <p>{l s='Your order is pending confirmation on-chain via XPayr.' mod='xpayr'}</p>
    {else}
        <p>{l s='There was an issue with your XPayr payment.' mod='xpayr'}</p>
    {/if}
</div>
