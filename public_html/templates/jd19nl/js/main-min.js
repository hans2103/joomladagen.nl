jQuery(function(n){n(window).on("scroll",function(){n(window).scrollTop()>250?n(".header").addClass("sticky fade_down_effect"):n(".header").removeClass("sticky fade_down_effect")}),n(window).width()<991&&n(".navbar-nav li a").on("click",function(){n(this).parent("li").find(".dropdown-menu").slideToggle(),n(this).find("i").toggleClass("fa-angle-up fa-angle-down")}),n(".countdown").length>0&&n(".countdown").jCounter({date:"17 May 2019 09:00:00",fallback:function(){console.log("count finished!")}});var o=!0;n(".ts-funfact").appear(),n(".ts-funfact").on("appear",function(){o&&(n(".counterUp").each(function(){var o=n(this);jQuery({Counter:0}).animate({Counter:o.attr("data-counter")},{duration:8e3,easing:"swing",step:function(){var n=Math.ceil(this.Counter).toString();if(Number(n)>99999)for(;/(\d+)(\d{3})/.test(n);)n=n.replace(/(\d+)(\d{3})/,"");o.html(n)}})}),o=!1)}),new WOW({animateClass:"animated",mobile:!1}).init(),n(window).on("scroll",function(){n(window).scrollTop()>n(window).height()?n(".BackTo").fadeIn("slow"):n(".BackTo").fadeOut("slow")}),n("body, html").on("click",".BackTo",function(o){o.preventDefault(),n("html, body").animate({scrollTop:0},800)}),n(".scroll a").on("click",function(){return n("html, body").animate({scrollTop:n(this.hash).offset().top-70},1e3),!1})});