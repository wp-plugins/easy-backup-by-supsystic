<script type="text/javascript">
// <!--
jQuery(document).ready(function(){
	jQuery('#toePagesRecreate').click(function(){
		jQuery(this).sendForm({
			msgElID: 'toePagesOptionsMsg',
			data: {page: 'pagesEbbs', action: 'recreatePages', reqType: 'ajax'}
		});
		return false;
	});
});
// -->
</script>
<h1><?php _e('Pages Options', EBBS_LANG_CODE)?></h1>
<table>
	<tr>
		<td><?php echo htmlEbbs::inputButton(array('value' => __('Recreate Pages', EBBS_LANG_CODE), 'attrs' => 'id="toePagesRecreate"'))?></td>
		<td><a href="#" class="toeOptTip" tip="<?php _e('If you accidently deleted one of plugin page - Login, or Checkout, or Shopping cart for example, just click on this button - and pages, that you deleted, will be created again. Do not use it without emergency.', EBBS_LANG_CODE)?>"></a></td>
	</tr>
</table>
<div id="toePagesOptionsMsg"></div>