$.fn.moveIt = function(){
  var $window = $(window);
  var instances = [];
  
  $(this).each(function(){
    instances.push(new moveItItem($(this)));
  });
  
  window.addEventListener('scroll', function(){
    var scrollTop = $window.scrollTop();
    instances.forEach(function(inst){
      inst.update(scrollTop);
    });
  }, {passive: true});
}

var moveItItem = function(el){
  this.el = $(el);
  this.speed = parseFloat(this.el.attr('data-scroll-speed'));
};

moveItItem.prototype.update = function(scrollTop){
  this.el.css('transform', 'translateY(' + -(scrollTop / this.speed) + 'px)');
};

// Initialization
$(function(){
  $('[data-scroll-speed]').moveIt();
});

$(window).scroll(function() {
  if ($(window).scrollTop() > 300) {
    $("#totop").addClass('show');
    $("#navbar-scroll").addClass('show');
  } else {
    $("#totop").removeClass('show');
    $("#navbar-scroll").removeClass('show');
  }
});

$(window).scroll(function() {
  if ($(window).scrollTop() > 300) {
    $('#down').addClass('hide');
    $('#title').addClass('hide');
  } else {
    $('#down').removeClass('hide');
    $('#title').removeClass('hide');
  }
});

function detectmob() { 
 if( navigator.userAgent.match(/Android/i)
   || navigator.userAgent.match(/webOS/i)
   || navigator.userAgent.match(/iPhone/i)
   || navigator.userAgent.match(/iPad/i)
   || navigator.userAgent.match(/iPod/i)
   || navigator.userAgent.match(/BlackBerry/i)
   || navigator.userAgent.match(/Windows Phone/i)
   ){
  return true;
}
else {
  return false;
}
}
var buttonIds = ['#size1', '#size2', '#size3'];
var containerIds = ['#email-container', '#status-container', '#review-container'];

function containerSwitcher(cont_id) 
{ 
  for (var i = 0; i < 3; i++) 
  {
    if("#".concat(cont_id.id) ==buttonIds[i])
    {
      $( containerIds[i] ).removeClass('hide');
    }
    else
    {
      $( containerIds[i] ).addClass('hide');
    }
  }
}
