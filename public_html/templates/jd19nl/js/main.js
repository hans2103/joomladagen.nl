/*

	Template Name: Exhibit - Conference & Event HTML Template
	Author: Themewinter
	Author URI: https://themeforest.net/user/themewinter
	Description: Exhibit - Conference & Event HTML Template
	Version: 1.0
   =====================
   table of content 
   ====================
   1.   menu toogle
   2.   event counter
   3.   funfact
   4.   isotope grid
   5.   main slider
   6.   speaker popup
   7.   gallery
   8.   video popup
   9.   hero area image animation
   10.  wow animated
   11.  back to top
  
*/


jQuery(function ($) {

    /**-------------------------------------------------
     *Fixed HEader
     *----------------------------------------------------**/
    $(window).on('scroll', function () {

        /**Fixed header**/
        if ($(window).scrollTop() > 250) {
            $('.header').addClass('sticky fade_down_effect');
        } else {
            $('.header').removeClass('sticky fade_down_effect');
        }
    });

    /* ---------------------------------------------
                      Menu Toggle
    ------------------------------------------------ */

    if ($(window).width() < 991) {
        $(".navbar-nav li a").on("click", function () {
            $(this).parent("li").find(".dropdown-menu").slideToggle();
            $(this).find("i").toggleClass("fa-angle-up fa-angle-down");
        });
    }

    /* ----------------------------------------------------------- */
    /*  Event counter
    /* -----------------------------------------------------------*/

    if ($('.countdown').length > 0) {
        $(".countdown").jCounter({
            date: '17 May 2019 09:15:00',
            fallback: function () {
                console.log("count finished!")
            }
        });
    }


    /*==========================================================
      funfact
      ======================================================================*/
    var skl = true;
    $('.ts-funfact').appear();

    $('.ts-funfact').on('appear', function () {
        if (skl) {
            $('.counterUp').each(function () {
                var $this = $(this);
                jQuery({
                    Counter: 0
                }).animate({
                    Counter: $this.attr('data-counter')
                }, {
                    duration: 8000,
                    easing: 'swing',
                    step: function () {
                        var num = Math.ceil(this.Counter).toString();
                        if (Number(num) > 99999) {
                            while (/(\d+)(\d{3})/.test(num)) {
                                num = num.replace(/(\d+)(\d{3})/, '');
                            }
                        }
                        $this.html(num);
                    }
                });
            });
            skl = false;
        }
    });

    /*==========================================================
    wow animated
     ======================================================================*/
    var wow = new WOW({
        animateClass: 'animated',
        mobile: false
    });
    wow.init();

    /* ----------------------------------------------------------- */
    /*  Back to top
    /* ----------------------------------------------------------- */

    $(window).on('scroll', function () {
        if ($(window).scrollTop() > $(window).height()) {
            $(".BackTo").fadeIn('slow');
        } else {
            $(".BackTo").fadeOut('slow');
        }

    });

    $("body, html").on("click", ".BackTo", function (e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 800);
    });


    /*==========================================================
              go current section
     ======================================================================*/
    $('.scroll a').on('click', function () {
        $('html, body').animate({scrollTop: $(this.hash).offset().top - 70}, 1000);
        return false;
    });

});