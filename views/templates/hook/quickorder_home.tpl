<div id="m4pquickorder-block" class="container">
  <h2>{l s='Quick Order' mod='m4pquickorder'}</h2>
  {if !$customer_logged && $hide_posible_to_order_for_guests}
    <div class="alert alert-info">
      {l s='You have to be logged in to add products to cart and see prices.' mod='m4pquickorder'}
    </div>
  {/if}

  {if $quickorder_error}
    <div class="alert alert-danger">
      {l s='An error occurred while adding the product to your cart. You have probably entered too many products for the product.' mod='m4pquickorder'}
    </div>
  {/if}
  
  <form action="{$form_action}" method="post" id="quickorder-form">
    <div class="row g-3 d-flex" style="flex-direction: row; flex-wrap: wrap;">
      {foreach from=$quickorder_products item=entry name=prod}
        {assign var="product" value=$entry.product}
        {assign var="combinations" value=$entry.combinations}
        {assign var="cover" value=$entry.cover}
        {assign var="price_old" value=$entry.price_old}
        {assign var="price_new" value=$entry.price_new}
        {assign var="stock" value=$entry.stock}

        <div class="col-12 col-sm-6 col-md-3 d-flex mb-3">
          <div class="card h-100 w-100 p-3 d-flex flex-column">
            {if $cover}
              <img src="{$cover}" alt="{$product->name}" class="img-fluid mb-2" />
            {/if}
            <div class="card-body d-flex flex-column">
              <h5 class="card-title" style="white-space: nowrap; text-overflow: ellipsis; width: 100%; overflow: hidden;">{$product->name}</h5>

              <div class="mb-2 product-price" id="price_display_{$product->id}">
                {if $price_old > $price_new}
                  <span class="text-muted" style="text-decoration: line-through;">{Tools::displayPrice($price_old)}</span>
                {/if}
                <span class="fw-bold ms-2">{Tools::displayPrice($price_new)}</span>
              </div>

              {if $combinations|@count > 0}
                <label for="attr_{$product->id}" style="text-align: left;">{l s='Combination' mod='m4pquickorder'}</label>
                <select id="attr_{$product->id}" name="attr[{$product->id}]" class="form-control mb-2" onchange="updatePrice({$product->id}, this.value)">
                  {foreach from=$combinations key=id_attr item=combo}
                    <option value="{$id_attr}" data-old="{Tools::displayPrice($combo.price_old)}" data-new="{Tools::displayPrice($combo.price_new)}">{$combo.label}</option>
                  {/foreach}
                </select>
              {/if}

              <label for="qty_{$product->id}" style="text-align: left;">{l s='Quantity' mod='m4pquickorder'}</label>
              <input type="number" id="qty_{$product->id}" name="qty[{$product->id}]" value="0" min="0" placeholder="{l s='Quantity' mod='m4pquickorder'}" class="form-control mt-auto" />
            </div>
          </div>
        </div>
      {/foreach}
    </div>

    {if $customer_logged && $hide_posible_to_order_for_guests}
      <div class="text-right mt-3">
        <button type="submit" name="submitQuickOrder" class="btn btn-primary">{l s='Order' mod='m4pquickorder'}</button>
      </div>
    {/if}
  </form>
</div>

{literal}
<script>
  function updatePrice(productId, combId) {
    var select = document.getElementById('attr_' + productId);
    var old = select.options[select.selectedIndex].getAttribute('data-old');
    var ne = select.options[select.selectedIndex].getAttribute('data-new');
    var disp = document.getElementById('price_display_' + productId);
    var html = '';
    if (parseFloat(old) > parseFloat(ne)) {
      html += '<span class="text-muted text-decoration-line-through">' + old + '</span> ';
    }
    html += '<span class="fw-bold ms-2">' + ne + '</span>';
    disp.innerHTML = html;
  }
</script>
{/literal}
