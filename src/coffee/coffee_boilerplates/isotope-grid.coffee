$(document).ready ->

	# ISOTOPE GRIDS
	$container = $('.isotope-grid')
	$container.children('li').addClass('isotope-item')
	$container.imagesLoaded ->
		$container.isotope({
			itemSelector: '.isotope-item'
			layoutMode: 'fitRows'
			})
		return

	return # END ON READY