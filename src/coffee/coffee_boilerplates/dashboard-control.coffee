$(document).ready ->

	# SITE MENU CONTROL
	$('.site-menu .top').click((e)->
		e.preventDefault()
		$('.site-wrapper').toggleClass('menu-on')
		return
		)

	return # END ON READY