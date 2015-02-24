$(document).ready ->

	$sectionScroll = $('body #section-scroll')
	$sectionScrollList = $('body #section-scroll ul')
	$sectionScrollContent = $('[data-section-scroll]')

	cleanupSpecialCharacters = (str)->
		str = str.replace(/[ÀÁÂÃÄÅ]/g,'A')
		str = str.replace(/[àáâãäå]/g,'a')
		str = str.replace(/[ÈÉÊË]/g,'E')
		str = str.replace(/[é]/g,'e')
		str = str.replace(/[Í]/g,'I')
		str = str.replace(/[í]/g,'i')
		str = str.replace(/[Ú]/g,'U')
		str = str.replace(/[ú]/g,'u')
		str = str.replace(/[Ó]/g,'O')
		str = str.replace(/[ó]/g,'o')
		return str.replace(/[^a-z0-9]/gi,'')

	$sectionScrollController = new ScrollMagic()

	scrollDownRef = []
	$.each($sectionScrollContent, (index, item)->
		triggerRef = '.section-scroll-' + index
		sectionData = $(item).data('section-scroll')
		idGen = sectionData.toString().toLowerCase()
		idGen = cleanupSpecialCharacters(idGen)
		idGen = idGen.split(' ').join('-')
		scrollRef = '.scroll-ref-' + index
		$(item).addClass('section-scroll-' + index)
		$(item).attr('id', idGen)
		scrollDownRef.push(idGen)
		sectionList = '<li><a href="#' + idGen + '"' + 
								'class="animated bounceInRight ad-' + (index * 2) + 
								' scroll-ref-' + index + ' atm-scroll-item">' + sectionData + '</a></li>'
		$sectionScrollList.append(sectionList)
		$sectionScrollScene = new ScrollScene({
			triggerHook: 'onLeave'
			triggerElement: triggerRef
			duration: $(item).height()
			})
			.setClassToggle(scrollRef, 'active')
			.addTo($sectionScrollController)
		# $sectionScrollScene.addIndicators({zindex: 1000, suffix: index})
		return
		)
	$sectionScrollList.find('li:first-child a').addClass('active')

	# AUTOMATICALLY ADD SCROLL DOWNS TO SECTION SCROLL BLOCKS
	scrollDownRef.shift()
	$.each($sectionScrollContent, (index, item)->
		idRef = scrollDownRef.shift()
		scrollDownAnchor = '<a class="scroll-down scroll-down-sq size lg"' +
											'href="#' + idRef + '"' +
											'rel="nofollow">' +
											'down' +
											'</a>'
		if(idRef != undefined)
			$(item).append(scrollDownAnchor)
		return
		)

	$applyScene = new ScrollScene({
		triggerHook: 'onEnter'
		triggerElement: '.section-scroll-3'
		})
		.addTo($sectionScrollController)

	$applyScene.on('enter', (e)->
		$('.btn-apply').addClass('bounceInRight')
		return
		)

	return