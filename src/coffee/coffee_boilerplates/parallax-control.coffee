'use-strict'
$(document).ready ->
	scrollToController = new ScrollMagic()
	parallaxController = new ScrollMagic()

	# FOLD OVERLAY PARALLAX
	tween = TweenMax.fromTo(".on-scroll", 1, {bottom: 0, opacity: 1}, {bottom: -140, opacity: 0.3}, ease: Linear.easeNone)
	parallaxScene = new ScrollScene({
		triggerElement: '.site-content'
		triggerHook: 'onEnter'
		duration: 300
		})
		.setTween(tween)
		.addTo(parallaxController)
	# parallaxScene.addIndicators({zindex: 1000, suffix: "2"})

	return