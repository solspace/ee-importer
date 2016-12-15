(function(global, $){
	"use strict";

	var Solspace = window.Solspace = window.Solspace || {};
	Solspace.importer = Solspace.importer || {};

	var items				= Solspace.importer.items;
	var totalItems			= items.length;
	var currentItemIndex	= 0;

	function ajaxUpdateItem(index, callback)
	{
		$.ajax({
			url			: Solspace.importer.ajaxLinks.importer_batch_import_ajax_url,
			dataType	: 'JSON',
			type		: 'POST',
			data		: {batch_number : items[index].itemId},
			error		: function (jqXHR, textStatus, errorThrown)
			{
				//console.log(jqXHR);
				alert('There was error during the processing: '+errorThrown);

				$('#current_item_updating').hide();
				$('#progress').hide();
				$('#current_item_updating').hide();
				$('#update_item_counts').show();
				$('#pause_item_counts').hide();
				$('#resume_item_counts').hide();
			},
			success		: function (data, textStatus, jqXHR)
			{
				callback();
			}
		});
	}

	function ajaxFetchStatistics()
	{
		$.ajax({
			url			: Solspace.importer.ajaxLinks.importer_import_statistics_ajax_url,
			dataType	: 'HTML',
			type		: 'GET',
			error		: function (jqXHR, textStatus, errorThrown)
			{
				//console.log(jqXHR);
				alert('There was error during the processing: '+errorThrown);
			},
			success		: function (data, textStatus, jqXHR)
			{
				$('#finished_message').append('<hr />' + data);
			}
		});
	}

	//on ready
	$(function(){
		var $start					= $('#update_item_counts');
		var $pause					= $('#pause_item_counts');
		var $resume					= $('#resume_item_counts');
		var $update_percent			= $('#update_percent');
		var $updates_completed		= $('#updates_completed');
		var $total_to_update		= $('#total_to_update');
		var $updating_item_name		= $('#updating_item_name');
		var $progressbar			= $('#progressbar');
		var $progress_inidicator	= $('#progress_inidicator');
		var runNextUpdate			= true;

		//start at 0
		$progressbar.progressbar({value: 0});

		function initiateAjaxUpdate()
		{
			//did someone click pause?
			if ( ! runNextUpdate) return;

			if (currentItemIndex < totalItems)
			{
				//update progress
				var percent = Math.floor((currentItemIndex/totalItems) * 100);

				$progressbar.progressbar({value:percent});
				$update_percent.html(percent + '%');
				$updates_completed.html(currentItemIndex);
				$updating_item_name.html(items[currentItemIndex].itemTitle);

				ajaxUpdateItem(currentItemIndex, initiateAjaxUpdate);

				//the function's inner code is asynchronous
				//so this should always call before an HTTP request ever finishes
				currentItemIndex++;
			}
			else
			{
				ajaxFetchStatistics();

				$progressbar.progressbar({value:100});
				$update_percent.html(100 + '%');
				$updates_completed.html(totalItems);
				$('#current_item_updating').hide();
				$pause.hide();
				$resume.hide();
				$('#finished_message').show();
			}
		}

		// -------------------------------------
		//	start button
		// -------------------------------------

		$start.click(function(e)
		{
			currentItemIndex = 0;

			$('#progress').show();
			$('#current_item_updating').show();
			$start.hide();
			$pause.show();

			initiateAjaxUpdate();

			e.preventDefault();
			return false;
		});

		// -------------------------------------
		//	pause and resume
		// -------------------------------------

		$pause.click(function(e){
			runNextUpdate = false;

			$progress_inidicator.hide();
			$pause.hide();
			$resume.show();
			$updating_item_name.html('');

			e.preventDefault();
			return false;
		});

		$resume.click(function(e){
			runNextUpdate = true;

			$progress_inidicator.show();
			$pause.show();
			$resume.hide();

			initiateAjaxUpdate();

			e.preventDefault();
			return false;
		});
	});
}(window, jQuery));