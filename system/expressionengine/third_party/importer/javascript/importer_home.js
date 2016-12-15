jQuery(function($)
{
	$('#datatype').change(function(e)
	{
		$('#content_type option').remove();

		$("#content_type_all option").each(function()
		{
			if ($(this).hasClass($('#datatype').val()))
			{
				$("#content_type").append("<option value='"+$(this).val()+"'>"+$(this).text()+"</option>");
			}
		});
	});

	$('#datatype').trigger('change');

});