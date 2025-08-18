/* ===================================================================
    
    Author          : Kazi Shahiduzzaman
    Template Name   : Maxid - Business And Agency HTML Template
    Version         : 1.0
    
* ================================================================= */
(function($) {
    "use strict";

    $(document).ready( function() {

		/* ==================================================
			Preloader Init
		===============================================*/
		
		$(window).on('load', function() {
			// Animate loader off screen
			$(".preloader").fadeOut("slow");
		});
		
		/* ==================================================
            # Youtube Video Init
         ===============================================*/
		
        $('.player').mb_YTPlayer();

		
		/* ==================================================
			# Scroll to top
		 =============================================== */
		
        //Get the button
        var mybutton = document.getElementById("scrtop");

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
          if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
          } else {
            mybutton.style.display = "none";
          }
        }
		
		/* ==================================================
			# Smooth Scroll
		 =============================================== */

		$('a.smooth-menu').on('click', function(event) {
			var $anchor = $(this);
			var headerH = '85';
			$('html, body').stop().animate({
				scrollTop: $($anchor.attr('href')).offset().top - headerH + "px"
			}, 1500, 'easeInOutExpo');
			event.preventDefault();
		});

		/* ==================================================
			# Circuler Progressbar
		 =============================================== */
		
		$('.chart').easyPieChart({
			size:200,
			barColor:'#BB0B0B',
			scaleColor:false,
			lineWidth:15,
			trackColor:'#F1CDCD'
		});
		
		/* ==================================================
			# imagesLoaded active
		===============================================*/
		
		$('.filter-active').imagesLoaded(function () {
			var $filter = '.filter-active',
			$filterItem = '.filter-item',
			$filterMenu = '.filter-menu-active';

			if ($($filter).length > 0) {
				var $grid = $($filter).isotope({
				itemSelector: $filterItem,
				filter: '*',
				masonry: {
						// use outer width of grid-sizer for columnWidth
						columnWidth: 1
					}
				});

				// filter items on button click
				$($filterMenu).on('click', 'button', function () {
					var filterValue = $(this).attr('data-filter');
					$grid.isotope({
						filter: filterValue
					});
				});

				// Menu Active Class 
				$($filterMenu).on('click', 'button', function (event) {
					event.preventDefault();
					$(this).addClass('active');
					$(this).siblings('.active').removeClass('active');
				});
			}
		})

		
		/* ==================================================
			# Magnific popup init
		 ===============================================*/
		
		$(".popup-link").magnificPopup({
			type: 'image',
			// other options
		});

		$(".popup-gallery").magnificPopup({
			type: 'image',
			gallery: {
				enabled: true
			},
			// other options
		});

		$(".popup-youtube, .popup-vimeo, .popup-gmaps").magnificPopup({
			type: "iframe",
			mainClass: "mfp-fade",
			removalDelay: 160,
			preloader: false,
			fixedContentPos: false
		});

		$("#videoLink").magnificPopup({
			type: "inline",
			midClick:true
		});
		
		$('.magnific-mix-gallery').each(function() {
			var $container = $(this);
			var $imageLinks = $container.find('.item');

			var items = [];
			$imageLinks.each(function() {
				var $item = $(this);
				var type = 'image';
				if ($item.hasClass('magnific-iframe')) {
					type = 'iframe';
				}
				var magItem = {
					src: $item.attr('href'),
					type: type
				};
				magItem.title = $item.data('title');
				items.push(magItem);
			});

			$imageLinks.magnificPopup({
				mainClass: 'mfp-fade',
				items: items,
				gallery: {
					enabled: true,
					tPrev: $(this).data('prev-text'),
					tNext: $(this).data('next-text')
				},
				type: 'image',
				callbacks: {
					beforeOpen: function() {
						var index = $imageLinks.index(this.st.el);
						if (-1 !== index) {
							this.goTo(index);
						}
					}
				}
			});
		});
		
		/* ==================================================
		# Quantity
		===============================================*/
		
		function wcqib_refresh_quantity_increments() {
			jQuery("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").each(function(a, b) {
				var c = jQuery(b);
				c.addClass("buttons_added"), c.children().first().before('<input type="button" value="-" class="minus" />'), c.children().last().after('<input type="button" value="+" class="plus" />')
			})
		}
		String.prototype.getDecimals || (String.prototype.getDecimals = function() {
			var a = this,
				b = ("" + a).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
			return b ? Math.max(0, (b[1] ? b[1].length : 0) - (b[2] ? +b[2] : 0)) : 0
		}), jQuery(document).ready(function() {
			wcqib_refresh_quantity_increments()
		}), jQuery(document).on("updated_wc_div", function() {
			wcqib_refresh_quantity_increments()
		}), jQuery(document).on("click", ".plus, .minus", function() {
			var a = jQuery(this).closest(".quantity").find(".qty"),
				b = parseFloat(a.val()),
				c = parseFloat(a.attr("max")),
				d = parseFloat(a.attr("min")),
				e = a.attr("step");
			b && "" !== b && "NaN" !== b || (b = 0), "" !== c && "NaN" !== c || (c = ""), "" !== d && "NaN" !== d || (d = 0), "any" !== e && "" !== e && void 0 !== e && "NaN" !== parseFloat(e) || (e = 1), jQuery(this).is(".plus") ? c && b >= c ? a.val(c) : a.val((b + parseFloat(e)).toFixed(e.getDecimals())) : d && b <= d ? a.val(d) : b > 0 && a.val((b - parseFloat(e)).toFixed(e.getDecimals())), a.trigger("change")
		});
		
		/* ==================================================
            # Typed
         ===============================================*/
		
		$(".typed").typed({
			strings: ["IT Company ", "Software Company ", "Digital Marketplace "],
			// Optionally use an HTML element to grab strings from (must wrap each string in a <p>)
			stringsElement: null,
			// typing speed
			typeSpeed: 100,
			// time before typing starts
			startDelay: 1200,
			// backspacing speed
			backSpeed: 10,
			// time before backspacing
			backDelay: 600,
			// loop
			loop: true,
			// false = infinite
			loopCount: Infinity,
			// show cursor
			showCursor: false,
			// character for cursor
			cursorChar: "|",
			// attribute to type (null == text)
			attr: null,
			// either html or text
			contentType: 'html',
			// call when done callback function
			callback: function() {},
			// starting callback function before each string
			preStringTyped: function() {},
			//callback for every typed string
			onStringTyped: function() {},
			// callback for reset
			resetCallback: function() {}
		});
		
		
		/* ==================================================
			# Fun Factor Init
		===============================================*/
		
			$('.timer').countTo();
			$('.fun-fact').appear(function() {
				$('.timer').countTo();
			}, {
				accY: -100
			});
		
		/* ==================================================
			# Wow Init
		 ===============================================*/
		
		var wow = new WOW({
			boxClass: 'wow', // animated element css class (default is wow)
			animateClass: 'animated', // animation css class (default is animated)
			offset: 0, // distance to the element when triggering the animation (default is 0)
			mobile: true, // trigger animations on mobile devices (default is true)
			live: true // act on asynchronously loaded content (default is true)
		});
		wow.init();
		
		/* ==================================================
            Contact Form Validations
        ================================================== */
        $('.contact-form').each(function() {
            var formInstance = $(this);
            formInstance.submit(function() {

                var action = $(this).attr('action');

                $("#message").slideUp(750, function() {
                    $('#message').hide();

                    $('#submit')
                        .after('<img src="assets/img/logo/ajax-loader.gif" class="loader" />')
                        .attr('disabled', 'disabled');

                    $.post(action, {
                            name: $('#name').val(),
                            email: $('#email').val(),
                            phone: $('#phone').val(),
                            comments: $('#comment').val()
                        },
                        function(data) {
                            document.getElementById('message').innerHTML = data;
                            $('#message').slideDown('slow');
                            $('.contact-form img.loader').fadeOut('slow', function() {
                                $(this).remove()
                            });
                            $('#submit').removeAttr('disabled');
                        }
                    );
                });
                return false;
            });
        });
		
		/* ==================================================
			# Hero Init
		 ===============================================*/
		
		var swiper = new Swiper(".hero-sldr", {
			loop: true,
			autoplay: {
				delay: 5000,
			},
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
		});
		
		/* ==================================================
			# Project Slider
		 ===============================================*/
		
		var swiper = new Swiper('.project-sldr', {
			loop: true,
			autoplay: {
				delay: 5000,
			},
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			breakpoints: {
				640: {
					slidesPerView: 1,
					slidesPerColumn: 1,
					spaceBetween: 20,
				},
				768: {
					slidesPerView: 2,
					slidesPerColumn: 2,
					spaceBetween: 40,
				},
				1024: {
					slidesPerView: 2,
					slidesPerColumn: 2,
					spaceBetween: 30,
				},
			},
		});
		
		/* ==================================================
			# Review Slider
		 ===============================================*/
		
		
		const swiperStageRight = new Swiper(".review-sldr", {
			// Optional parameters
			loop: true,
			freeMode: true,
			grabCursor: true,
			slidesPerView: 1,
			spaceBetween: 30,
			pagination: {
				el: ".swiper-pagination",
				clickable: true,
			},
			// Navigation arrows
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev"
			},
			breakpoints: {
				768: {
					slidesPerView: 2,
				},
				1201: {
					slidesPerView: 2,
				},
				1300: {
					slidesPerView: 2.5,
				},
			},
		});
		
		
		
		/* ==================================================
			# Work Init
		 ===============================================*/
		
		var swiper = new Swiper(".work-sldr", {
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			breakpoints: {
				0: {
					slidesPerView: 1,
					spaceBetween: 10,
				},
				640: {
					slidesPerView: 2,
					spaceBetween: 20,
				},
				768: {
					slidesPerView: 2,
					spaceBetween: 30,
				},
				992: {
					slidesPerView: 3,
					spaceBetween: 30,
				},
				1024: {
					slidesPerView: 4,
					spaceBetween: 30,
				},
			},
		});
		
		
		/* ==================================================
			# Review Slider
		 ===============================================*/
		
		var swiper = new Swiper('.rev-sldr', {
			loop: true,
			autoplay: {
				delay: 5000,
			},
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			pagination: {
				el: ".swiper-pagination",
				clickable: true,
			},
			breakpoints: {
				640: {
					slidesPerView: 1,
					slidesPerColumn: 1,
					spaceBetween: 20,
				},
				768: {
					slidesPerView: 2,
					slidesPerColumn: 1,
					spaceBetween: 40,
				},
				1024: {
					slidesPerView: 1,
					slidesPerColumn: 1,
					spaceBetween: 30,
				},
			},
		});
		
		/* ==================================================
			# Partner Slider
		 ===============================================*/
		
		var swiper = new Swiper('.ptnr-sldr', {
			loop: true,
			autoplay: {
				delay: 5000,
			},
			spaceBetween: 50,
			slidesPerColumn: 1,
			breakpoints: {
				0: {
					slidesPerView: 3,
					spaceBetween: 20,
				},
				640: {
					slidesPerView: 3,
					spaceBetween: 20,
				},
				768: {
					slidesPerView: 4,
					
				},
				1024: {
					slidesPerView: 6,
				},
			},
		});
		
		
		
    }); // end document ready function
})(jQuery); // End jQuery

