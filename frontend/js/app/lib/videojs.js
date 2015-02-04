// Generated by CoffeeScript 1.8.0
(function() {
  $(document).ready(function() {
    var myPlayer, playBtn, playFullScreen, videoIsFullscreen, videoPlaying, videocover;
    videocover = document.getElementById('videocover');
    playBtn = $('.play-video');
    playFullScreen = $('.play-video-full-screen');
    videoPlaying = false;
    videoIsFullscreen = false;
    if (videocover) {
      myPlayer = videojs('videocover');
      myPlayer.controls(false);
      myPlayer.currentTime(6);
      playBtn.click(function(e) {
        e.preventDefault();
        if (videoPlaying) {
          myPlayer.pause();
          $('.play-video i').removeClass('flaticon-pause31').addClass('flaticon-key9');
          videoPlaying = false;
        } else {
          myPlayer.play();
          $('.video-placeholder').addClass('hidden');
          $('.play-video i').removeClass('flaticon-key9').addClass('flaticon-pause31');
          videoPlaying = true;
        }
      });
      playFullScreen.click(function(e) {
        e.preventDefault();
        myPlayer.requestFullscreen();
        myPlayer.isFullscreen(true);
        myPlayer.controls(true);
        myPlayer.muted(false);
        myPlayer.play();
        myPlayer.currentTime(0);
      });
      myPlayer.on('fullscreenchange', function() {
        if (!myPlayer.isFullscreen()) {
          myPlayer.isFullscreen(false);
          myPlayer.controls(false);
          myPlayer.muted(true);
        }
      });
    }
  });

}).call(this);