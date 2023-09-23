(function ($) {
	function gallery(el) {
		const $this = $(el);
		const $gallery = $($this.data('gallery'));
		const index = parseInt($this.data('index'), 10) || 0;

		const options = $.extend({
			startIndex: index,
			Image: {
				//zoom: true,
				// click: false,
				// wheel: false,
				// fit: "contain-w",
			},
			Carousel: {
				//Dots: true
			},
			Toolbar: {
				display: [
					{ id: "counter", position: "left" },
					"zoom",
					"slideshow",
					"fullscreen",
					"download",
					"thumbs",
					"close",
				],
			},
		}, $gallery.data('options'))

		const images = $gallery.data('images');

		Fancybox.show(images, options);
	}

	$(document).on('click', '[data-gallery]', function (e) {
		Journal.load(Journal['assets']['fancybox'], 'fancybox', function () {
			gallery(e.currentTarget);
		});

		return false;
	});
})(jQuery);
