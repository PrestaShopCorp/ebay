<div id="ebayListings">
	<p class="center">
		<button class="button">{l s='See eBay listings' mod='ebay'}</button>
	</p>
</div>
<script type="text/javascript">
	// <![CDATA[
	var content_ebay_listings = $("#ebayListings button");
	content_ebay_listings.bind('click', 'button', function(){
		$.ajax({
			url: module_dir+'ebay/ajax/getEbayListings.php',
			data: "token="+ebay_token+"&id_employee={$id_employee|escape:'htmlall'}",
			success: function(data)
			{
				$('#ebayListings').fadeOut(400, function(){
					$(this).html(data).fadeIn();
				})
			}
		});
	})
	//]]>
</script>