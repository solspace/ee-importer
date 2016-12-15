jQuery(function($)
{
	"use strict";
	var Solspace = window.Solspace = window.Solspace || {};
	Solspace.importer = Solspace.importer || {};

	$('tr[id^="source_"]').hide();

	$('#data_source').change(function(event)
	{
		Solspace.importer.switchDataSource();
	});

	$(".solpace_modal_overlay_trigger").overlay(
	{
		closeOnEsc:true,
		closeOnClick:true,
		top:"center",
		target:"#solspace_modal_overlay",
		mask:{color:"#262626",loadSpeed:200},
		onBeforeLoad:function()
		{
			$('#solspace_modal_overlay > h2').html(
				Solspace.importer.lang.beginning_connection_test
			);
			$('#solspace_modal_overlay > p').html(
				Solspace.importer.lang.connection_test_underway_please_standby
			);
		},
		onLoad:function()
		{
			Solspace.importer.connectionTest();
		}
	});

	Solspace.importer.switchDataSource();
});
