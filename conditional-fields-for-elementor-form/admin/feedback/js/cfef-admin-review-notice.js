jQuery(document).ready(function ($) {

     // Dismiss admin review notice ajax call
		$(".cfef_dismiss_notice").on("click", function (event) {
				var $this = $(this);
				var wrapper=$this.parents(".cfef-review-notice-wrapper");
				var ajaxURL=wrapper.data("ajax-url");
				var ajaxCallback=wrapper.data("ajax-callback");
				var ajaxNonce=wrapper.data("nonce");
				$.post(ajaxURL, { "action":ajaxCallback, "nonce":ajaxNonce }, function( data ) {
					wrapper.slideUp("fast");
				}, "json");
		});

}) 
 