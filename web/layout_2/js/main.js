jQuery('.best-sl').slick({
  dots: false,
  infinite: false,
  speed: 300,
  slidesToShow: 4,
  slidesToScroll: 1,
  responsive: [
    {
      breakpoint: 1680,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 1,
        infinite: true,
        dots: false
      }
    },
    {
      breakpoint: 1200,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        infinite: true,
        dots: false
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }
  ]
});
jQuery('.offers-sl').slick({
  dots: false,
  infinite: true,
  speed: 300,
  slidesToShow: 4,
  slidesToScroll: 1,
  responsive: [
    {
      breakpoint: 1680,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 1,
        infinite: true,
        dots: false
      }
    },
    {
      breakpoint: 1200,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        infinite: true,
        dots: false
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }
  ]
});
/////////Placeholder IE9
jQuery(function() {
 jQuery('input, textarea').placeholder();
});
/////////Tabs
jQuery(function() {
    jQuery('ul.i-tab li:first').addClass ('active');
    jQuery('ul.tab-content li:first').css ('display', 'block');
    jQuery('ul.i-tab').delegate('li:not(.active)', 'click', function() {
        jQuery(this).addClass('active').siblings().removeClass('active')
            .parents('.tabs').find('ul.tab-content li.tab-li').hide()
            .eq(jQuery(this).index()).fadeIn('fast');
    })
})
/////////Slider Object
jQuery(window).load(function(){
    jQuery(".pv, .pv2").sliderkit({
      circular:true,
      shownavitems:3,
      verticalnav:true,
      auto:false,
    });
});

jQuery(function(){
  if(device.mobile())
  {
    jQuery( "<a class='tel' href='tel:+74959567799'>+7 (495) 956-77-99</a>" ).appendTo( ".mobile-top span, .zayvka-block .tel1" );
    jQuery( "<a class='tel' href='tel:+79255142629'>+7 925 514 26 29</a>" ).appendTo( ".zayvka-block .tel2" );  
  }
  else
  {
    jQuery( "<span class='tel'>+7 (495) 956-77-99</span>" ).appendTo( ".mobile-top span, .zayvka-block .tel1" ); 
    jQuery( "<span class='tel'>+7 925 514 26 29</span>" ).appendTo( ".zayvka-block .tel2" ); 
  }  
});


var trigClick = false;
jQuery(".more-info-a").click(function() {
  if (trigClick) return false;
  trigClick = true;
  jQuery(this).toggleClass("active"); 
  jQuery('.about-jk').slideToggle(); 
  setTimeout(function() { trigClick=false }, 500);     
})

jQuery(document).ready(function() { // вся магия после загрузки страницы
  jQuery('a.zayvka-modal').click( function(event){ // ловим клик по ссылки с id="go"
    event.preventDefault(); // выключаем стандартную роль элемента
    jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
      function(){ // после выполнения предъидущей анимации
        jQuery('#modal_form') 
          .css('display', 'block') // убираем у модального окна display: none;
          .animate({opacity: 1}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
    });
  });
  /* Закрытие модального окна, тут делаем то же самое но в обратном порядке */
  jQuery('.modal_close, #overlay').click( function(){ // ловим клик по крестику или подложке
    jQuery('#modal_form')
      .animate({opacity: 0, top: '15%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
        function(){ // после анимации
          jQuery(this).css('display', 'none'); // делаем ему display: none;
          jQuery('#overlay').fadeOut(400); // скрываем подложку
        }
      );
  });
});

jQuery("#phone").mask("+7 (999) 999-99-99");
jQuery("#modal_form #ch").change(function(){ 
    if(jQuery('#modal_form #ch').is(':checked')){
      jQuery("#phone").unmask("+7 (999) 999-99-99");
      jQuery("#phone").data('placeholder',jQuery("#phone").attr('placeholder'))
      jQuery("#phone").attr('placeholder','');
    } 
    else{
      jQuery("#phone").mask("+7 (999) 999-99-99");
      jQuery("#phone").data('placeholder',jQuery("#phone").attr('placeholder'))
      jQuery("#phone").attr('placeholder','+7 (   )');
    }
});