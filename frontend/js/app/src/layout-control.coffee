$(document).ready ->

# DISABLE ANCHORS
	$('.disable-anchors a').click (e)->
		e.preventDefault()
		return

	$('[data-href]').click((e)->
		console.log($(this).data('href'))
		document.location.replace($(this).data('href'))
		return
		)

	# SITE MENU CONTROL
	$('.site-menu .top').click((e)->
		e.preventDefault()
		$('.site-wrapper').toggleClass('menu-on')
		return
		)

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