jQuery(function($)
{
	"use strict";

	$('a[class="solpace_import_log"]').overlay(
	{
		closeOnEsc:true,
		closeOnClick:true,
		top:"center",
		target:"#solspace_modal_overlay",
		mask:{color:"#262626",loadSpeed:200},
		onBeforeLoad:function(event)
		{
			// Find our log_id
			var log_id = this.getTrigger().attr("rel");

			// Set header and modal textarea values
			$('#solspace_modal_overlay > h2').html($('#log_id_'+ log_id +'_date').html());
			$('#solspace_modal_overlay > p').html($('#log_details_'+ log_id + '').html());
		}
	});

});