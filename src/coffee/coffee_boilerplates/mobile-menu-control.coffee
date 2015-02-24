'use-strict'
$(document).ready ->
# MOBILE NAV DISPLAY CONTROL
	$siteWrapper = $('.site-wrapper')
	$menuTrigger = $('.menu-trigger')
	$mobileNavDisplayTriggers = $('.mobile-nav-display-triggers')

	$menuTrigger.click (e)->
		e.preventDefault()
		$siteWrapper.toggleClass('menu-on')
		return

	$mobileNavRefGlobal = ''
	$mobileNavDisplayTriggers.on('click', '[href^=#]', (e)->
		$navRef = $(this).attr('href')
		$mobileNavRefGlobal = $navRef.slice(1)
		if $navRef.length > 0
			e.preventDefault()
			$navRefClass = $navRef.slice(1)
			$siteWrapper.addClass('display ' + $navRefClass)
		return
		)
	$('.mobile-nav-display .back').click (e)->
		e.preventDefault()
		$siteWrapper.removeClass('display')
		$siteWrapper.removeClass($mobileNavRefGlobal)
		return

	return