jQuery(function($)
{
	"use strict";
	$("#order_the_field li").css("cursor", "move");
	$("#order_the_field").sortable(
	{
		axis:"y",
		items: 'li',
		containment:$('#order_the_field').parent()
	});
});

