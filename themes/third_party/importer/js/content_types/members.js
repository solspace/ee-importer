(function($)
{
	"use strict";

	$(function()
	{
		// --------------------------------------------
		//  Default Field Values Modal
		// --------------------------------------------

		$('a.default_field_modal').overlay(
		{
			closeOnEsc:true,
			closeOnClick:true,
			top:"center",
			target:"#solspace_modal_overlay",
			mask:{color:"#262626",loadSpeed:200},
			onBeforeLoad:function(event)
			{
				// Clear modal textarea
				$('select[name="solspace_modal_overlay_select"]').remove();
				$('textarea[name="solspace_modal_overlay_textarea"]').remove();

				// Find our field_id_#
				modal_field_id = this.getTrigger().attr("rel");

				// Set header and modal textarea values
				$('#solspace_modal_overlay > h2').html($('label[for="' + modal_field_id + '_element"]').html());

				var val;

				if ($('#visible-fields select[name="' + modal_field_id + '_default"]').length > 0)
				{
					val = $('#visible-fields select[name="' + modal_field_id + '_default"]').val();

					$('#visible-fields select[name="' + modal_field_id + '_default"]').
						clone().
						attr('name', 'solspace_modal_overlay_select').
						val(val).
						appendTo($('#solspace_modal_overlay p')).
						show();
				}
				else
				{
					val = $('#visible-fields textarea[name="' + modal_field_id + '_default"]').html();

					$('#visible-fields textarea[name="' + modal_field_id + '_default"]').
						clone().
						attr('name', 'solspace_modal_overlay_textarea').
						html(val).
						appendTo($('#solspace_modal_overlay p')).
						show();
				}
			},
			onClose:function(a)
			{
				a = $(a.srcElement).closest(".close");

				var val;
				var c;

				// Closed via "Save" button?
				if (a.hasClass("modal_close_save"))
				{
					if ($('select[name="' + modal_field_id + '_default"]').length > 0)
					{
						val = $('select[name="solspace_modal_overlay_select"]').val();
						$('select[name="' + modal_field_id + '_default"]').remove();

						c = $('select[name="solspace_modal_overlay_select"]').clone(true).attr('name', modal_field_id + '_default').val(val);
						c.appendTo($('#custom_field_default_div_' + modal_field_id));
					}
					else
					{
						val = $('textarea[name="solspace_modal_overlay_textarea"]').val();
						$('textarea[name="' + modal_field_id + '_default"]').remove();

						c = $('textarea[name="solspace_modal_overlay_textarea"]').clone().attr('name', modal_field_id + '_default').html(val);
						c.appendTo($('#custom_field_default_div_' + modal_field_id));
					}
				}
			}
		});

		// --------------------------------------------
		//  Adds Additional EE Fields to Visible Display
		// --------------------------------------------

		$('#add-additional-fields').click(function(e)
		{
			e.preventDefault();

			var field_id = $('#choose-additional-fields').val();

			if ($('#invisible-fields tr[field-id="'+field_id+'"]').length === 0)
			{
				return;
			}

			$('#no-fields-here').hide();

			// Take the invisible field, remove EE's forced odd/even, add our own *correct* one, append to Visible Fields
			$('#invisible-fields tr[field-id="'+field_id+'"]').
				attr('class', '').
				addClass(($('#visible-fields tr').length % 2) ? 'even' : 'odd').
				appendTo($('#visible-fields'));

			// Remove option from the select
			$('#choose-additional-fields option[value="'+field_id+'"]').remove();

			// Fix odd/even
			var i = 0;

			$('#visible-fields tr').each(function(){
				$(this).attr('class', '').addClass((++i % 2) ? 'even' : 'odd');
			});
		});

		// --------------------------------------------
		//  Removes an EE Field from Display
		//  (resets elements, default values and puts in Hidden <tbody>)
		// --------------------------------------------

		$('.remove_me').click(function(e)
		{
			e.preventDefault();

			var field_id = $(this).attr('field-id');

			var label = $('label', $(this).parent()).text().replace(/^\s*/, '').replace(/\s*$/, '');

			// Add option back to the select
			$('#choose-additional-fields').append($('<option></option>').val(field_id).html(label));

			// Sort the list of options
			$('#choose-additional-fields').
				html($("#choose-additional-fields option").sort(function(a, b) {
				return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
			}));

			// Choose the just removed option
			$('#choose-additional-fields').val(field_id);

			// Empty the default value and chosen element fields
			$('#visible-fields tr[field-id="'+field_id+'"] select').val('');
			$('#visible-fields tr[field-id="'+field_id+'"] textarea').html('');

			// Move back to invisible fields
			$('#visible-fields tr[field-id="'+field_id+'"]').appendTo($('#invisible-fields'));

			// If no visible fields, put back the no-fields message.
			if ($('#visible-fields tr').not('#no-fields-here').length === 0)
			{
				$('#no-fields-here').show();
			}

			// Fix odd/even
			var i = 0;
			$('#visible-fields tr').each(function(){
				$(this).attr('class', '').addClass((++i % 2) ? 'even' : 'odd');
			});
		});

		// --------------------------------------------
		//  If No Additional EE Fields, We Give Them a Message Instead of an Empty <tbody>
		// --------------------------------------------

		if ($('#visible-fields tr').not('#no-fields-here').length === 0)
		{
			$('#no-fields-here').show();
		}
	});
})(jQuery);