jQuery(function($){
	"use strict";

	var Solspace = window.Solspace = window.Solspace || {};

	if (typeof Solspace.message !== 'undefined' &&
		Solspace.message !== '')
	{
		$.ee_notice(Solspace.message,{open: true, type:"success"});
		setTimeout(function(){ $.ee_notice.destroy(); }, 3000);
	}

	//magic checkboxes
	$('table.magic_checkbox_table, ' +
		'table.magicCheckboxTable, '  +
		'table.cb_toggle'
	).each(function(){
		var $table		= $(this),
			$magicCB	= $table.find(
				'input[type=checkbox][name=toggle_all_checkboxes]'
			);

		$magicCB.each(function(){
			var $that		= $(this),
				colNum		= $that.parent().index();

			$that.click(function(){
				var checked = ($that.is(':checked')) ? 'checked' : false;

				$table.find('tr').find(
					'th:eq(' + colNum + ') input[type=checkbox], ' +
					'td:eq(' + colNum + ') input[type=checkbox]'
				).attr('checked', checked);
			});
		});
	});
});

(function(global, $){

	"use strict";

	var Solspace = global.Solspace = global.Solspace || {};

	Solspace.importer = Solspace.importer || {};

	Solspace.importer.switchDataSource = function()
	{
		var source_value = $('#data_source').val();

		$('#import_source tbody tr').not('.data_source').hide();

		if ((source_value == 'ftp' || source_value == 'sftp') &&
			$.trim($('input[name="ftp_host"]').val()) === '')
		{
			if (source_value == 'ftp')
			{
				$('input[name="ftp_port"]').val('21');
			}
			else
			{
				$('input[name="ftp_port"]').val('22');
			}
		}

		$('#import_source tbody tr.for_' + source_value).show();
	};

	Solspace.importer.openDialogWindow = function(header, message)
	{
		var modalHtml = '<section>' +
							'<h3>'+ header + '</h3>' +
							'<div>'+ message + '</div>' +
							'<a href="#" class="solspace_modal_close">' +
							Solspace.importer.lang.modal_close_button +
							'</a>' +
						'</section>';

		if (!$('div.solspace_modal_dialog').length)
		{
			$("body").append('<div class="solspace_modal_dialog"></div>');
		}

		$("div.solspace_modal_dialog").dialog({
			autoOpen: true,
			width: 450,
			position: "middle",
			modal: true,
			resizable: false,
			closeOnEscape: true,
			draggable: false,
			open: function()
			{
				$(this).html(modalHtml);
			}
		});

		$('body').delegage("a.solspace_modal_close", "click", function()
		{
			$("div.solspace_modal_dialog").remove();
			event.preventDefault();
		});
	},

	Solspace.importer.connectionTest = function()
	{
		$.ajax(
		{
			type: "POST",
			url: Solspace.importer.connectionTestUrl,
			data: $('#module_importer_profile_source').serialize(),
			async: false,
			dataType: "json",
			error : function(jqXHR, textStatus, errorThrown)
			{
				$('#solspace_modal_overlay > h2').html(Solspace.importer.lang.error_ajax_request);
				$('#solspace_modal_overlay > p').html(errorThrown);
			},
			success: function(data, textStatus, jqXHR)
			{
				$('#solspace_modal_overlay > h2').html(data.heading);
				$('#solspace_modal_overlay > p').html(data.content);
			}
		});
	};

})(window, jQuery);