'use-strict'
$(document).ready ->
	videocover = document.getElementById 'videocover'
	playBtn = $('.play-video')
	playFullScreen = $('.play-video-full-screen')
	videoPlaying = false
	videoIsFullscreen = false

	if videocover 
		myPlayer = videojs 'videocover'
		myPlayer.controls false
		myPlayer.currentTime 6
		playBtn.click((e)->
			e.preventDefault()
			if videoPlaying
				myPlayer.pause()
				$('.play-video i').removeClass('flaticon-pause31').addClass('flaticon-key9')
				videoPlaying = false
			else 
				myPlayer.play()
				$('.video-placeholder').addClass('hidden')
				$('.play-video i').removeClass('flaticon-key9').addClass('flaticon-pause31')
				videoPlaying = true
			return
			)
		playFullScreen.click (e) ->
			e.preventDefault()
			# videoIsFullscreen = true
			myPlayer.requestFullscreen()
			myPlayer.isFullscreen(true)
			myPlayer.controls true
			myPlayer.muted false
			myPlayer.play()
			myPlayer.currentTime 0
			return
		myPlayer.on 'fullscreenchange', ->
			if !myPlayer.isFullscreen()
				myPlayer.isFullscreen(false)
				myPlayer.controls false
				myPlayer.muted true
			return

	return