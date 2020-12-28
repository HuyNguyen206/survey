

<script type="text/javascript">
	jQuery(function($) {
		var oTable1 = $('#sample-table-2').dataTable( {
		"aLengthMenu": [[5, 10, 20], [5, 10, 20]],
        "iDisplayLength": 5,
		"oLanguage": {
				"sEmptyTable":     "<?php echo trans($common.".No data available in table"); ?>",
				"sInfo":           "<?php echo trans($common.".Showing _START_ to _END_ of _TOTAL_ entries"); ?>",
				"sInfoEmpty":      "<?php echo trans($common.".Showing 0 to 0 of 0 entries"); ?>",
				"sInfoFiltered":   "(<?php echo trans($common.".filtered from _MAX_ total entries"); ?>)",
				"sInfoPostFix":    "",
				"sInfoThousands":  ",",
				"sLengthMenu":     "<?php echo trans($common.".Show _MENU_ entries"); ?>",
				"sLoadingRecords": "<?php echo trans($common.".Loading"); ?>...",
				"sProcessing":     "<?php echo trans($common.".Processing"); ?>...",
				"sSearch":         "<?php echo trans($common.".Search"); ?>:",
				"sZeroRecords":    "<?php echo trans($common.".No matching records found"); ?>"
			}
		});

		$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
		function tooltip_placement(context, source) {
			var $source = $(source);
			var $parent = $source.closest('table')
			var off1 = $parent.offset();
			var w1 = $parent.width();

			var off2 = $source.offset();
			var w2 = $source.width();

			if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
			return 'left';
		}
	});
</script>