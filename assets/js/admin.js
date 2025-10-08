/**
 * Admin JavaScript for AI Prompts Library.
 */

(function( $ ) {
	'use strict';

	/**
	 * Initialize on document ready.
	 */
	$(document).ready(function() {
		// Show export example modal
		$('#show-export-example').on('click', function(e) {
			e.preventDefault();
			$('#export-example-modal').fadeIn(200);
		});

		// Show import example modal
		$('#show-import-example').on('click', function(e) {
			e.preventDefault();
			$('#import-example-modal').fadeIn(200);
		});

		// Close modal when clicking the X
		$('.ai-prompt-modal-close').on('click', function() {
			var modalId = $(this).data('modal');
			$('#' + modalId).fadeOut(200);
		});

		// Close modal when clicking outside the modal content
		$('.ai-prompt-modal').on('click', function(e) {
			if ($(e.target).hasClass('ai-prompt-modal')) {
				$(this).fadeOut(200);
			}
		});

		// Close modal on Escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape') {
				$('.ai-prompt-modal').fadeOut(200);
			}
		});
	});

})( jQuery );
