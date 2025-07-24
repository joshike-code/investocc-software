/**
	Template Name 	 : W3Grocery
	Author			 : DexignZone
	Version			 : 1.0
	File Name		 : custom.js
	Author Portfolio : https://themeforest.net/user/dexignzone/portfolio
	
	Core script to handle the entire theme and core functions
	
**/

W3Grocery = function(){
	
	"use strict"
	
	var screenWidth = $( window ).width();
	var screenHeight = $( window ).height();
	
	// Preloader ============
	var handlePreloader = function(){
		setTimeout(function() {
            jQuery('#preloader').fadeOut(300);
		},300);
	}

	// Menubar Toggler ============
    var handleMenubar = function() {
        jQuery('.menu-toggler').on('click',function(){
			jQuery('.sidebar').toggleClass('show');
			jQuery('.menu-toggler').toggleClass('show');
			jQuery('.dark-overlay').toggleClass('active');
		});
		jQuery('.dark-overlay').on('click',function(){
			jQuery('.menu-toggler,.sidebar').removeClass('show');
			jQuery(this).removeClass('active');
		});
		jQuery('.nav-color').on('click',function(){
			jQuery('.dark-overlay').removeClass('active');
		});
	}
    
	// Show Pass ============
    var handleShowPass = function(){
		jQuery('.show-pass').on('click',function(){
			jQuery(this).toggleClass('active');
			if(jQuery('#dz-password, .dz-password').attr('type') == 'password'){
				jQuery('#dz-password, .dz-password').attr('type','text');
			}else if(jQuery('#dz-password, .dz-password').attr('type') == 'text'){
				jQuery('#dz-password, .dz-password').attr('type','password');
			}
		});
	}
	
	// Sticky Header ============
	var handleIsFixed = function(){
		$(window).scroll(function() {    
			var scroll = $(window).scrollTop();
			if (scroll >= 50) {
				$(".main-bar").addClass("sticky-header");
			}else{
				$(".main-bar").removeClass("sticky-header");
			}
		});
	}
    
	// Custom File Input ============
	var handleCustomFileInput = function() {
		$(".custom-file-input").on("change", function() {
			var fileName = $(this).val().split("\\").pop();
			$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
		});
	}
    
	// Default Select ============
	var handleSelectpicker = function(){
		if(jQuery('.default-select').length > 0 ){
			jQuery('.default-select').selectpicker();
		}
		$(".circle-radio input").on("change",function(){
			if ($(this).is(':checked')){
				$(".select-location").removeClass("d-none");
			}
        });
	}
    
	// Menubar Nav Active ============
    var handleMenubarNav = function() {
        $(".menubar-nav .nav-link").on("click",function(){
            $(".menubar-nav .nav-link").removeClass("active");
            $(this).addClass("active");
        });
	}
    
	// Message Sheet ============
	var handleMessageHandle = function() {
        $(".message-area .icon-popup").on("click",function(){
            $(".message-icon").slideToggle('slow');
        });
        $(".messagebar-sheet-image").on("change",function(){
            var iconsrc = $(this).attr('data-icon'); 
			
		if($(this).find('input[type="checkbox"]').is(':checked')) {
				$(".append-media").append("<div class='emoji-icon' data-icon="+iconsrc+"><img src="+iconsrc+ "></div>");				
			}else{
			    var mediaicon = $('.message-area .emoji-icon[data-icon="'+iconsrc+'"]');
				mediaicon.remove();
			}
        });
	}
	
	// Scroll Top ============
	var handleScrollTop = function (){
		jQuery(window).bind("scroll", function() {
			var scroll = jQuery(window).scrollTop();
			if (scroll > 100) { 
				jQuery(".btn.scrollTop").fadeIn(500);
			} else {
				jQuery(".btn.scrollTop").fadeOut(500);
			}
		});
		/* page scroll top on click function end*/
	}
	
	// Chat Box ============
	var handleChatBox = function (){
        $('.btn-chat').on('click', function() {
			
			var chatInput = $('.message-area .form-control');
			var chatMessageValue = chatInput.val();
			
			var chatEmojiArea = $('.append-media').html();
            
            var current = new Date();
            var ampm = current.getHours() >= 12 ? 'pm' : 'am';
            var actualTime = (current.getHours()% 12 +':'+current.getMinutes() +' '+ ampm);
			
			var messageEmojiHtml = '<div class="chat-content user">'+
				'<div class="message-item">'+
					'<div class="bubble">'+chatEmojiArea+'</div>'+
					'<div class="message-time">'+actualTime+'</div>'+
				'</div>'+
			'</div>';
				
			if(chatEmojiArea.length > 0){   
				$('.chat-box-area').append(messageEmojiHtml);
			}
			
			var messageHtml = '<div class="chat-content user">'+
				'<div class="message-item">'+
					'<div class="bubble">'+chatMessageValue+'</div>'+
					'<div class="message-time">'+actualTime+'</div>'+
				'</div>'+
			'</div>';
			
			if(chatMessageValue.length > 0){
				var appendMessage = $('.chat-box-area').append(messageHtml);
			}
			
			window.scrollTo(0, document.body.scrollHeight);
			var clearChatInput = chatInput.val('');    
			var clearChatInputE = $('.append-media').empty();     
        });
    }
    
    // Page Back ============
	var handleGoBack = function(){
		$('.back-btn').on('click',function(){
			window.history.go(-1); return false
		})        
    }
    
	// Progressive Web App Modal ============
	var handlePWAModal = function (){
		if (!window.matchMedia('(display-mode: standalone)').matches) {
		    setTimeout(function(){
    			jQuery('.pwa-offcanvas').addClass('show');
    			jQuery('.pwa-backdrop').addClass('fade show');
    		}, 3000);
    		jQuery('.pwa-backdrop, .pwa-close, .pwa-btn').on('click',function(){
    			jQuery('.pwa-offcanvas').slideUp(500, function() {
    				jQuery(this).removeClass('show');
    			});
    			setTimeout(function(){
    				jQuery('.pwa-backdrop').removeClass('show');
    			}, 500);
    		}); 
		}
	}
    
	// Search Form ============
	var handleSearch = function(){
        $('.search-input .form-control').on('change paste keyup',function(){
            if($(this).val().length > 0){
                $('.search-input .btn-close').fadeIn(500);
            }else{
                $('.search-input .btn-close').fadeOut(500);
            }
        })
        $('.search-input .btn-close').on('click',function(){
            $('.search-input .form-control').val(null);
            $(this).fadeOut(0);
        })
    }
	
	// Theme Version ============
	var handleThemeVersion = function() {
		jQuery('.theme-btn').on('click',function(){
			jQuery('body').toggleClass('theme-dark');
			jQuery('.theme-btn').toggleClass('active');
		});
	}
	
	var handleThemeVersion2 = function() {
		jQuery('.dz-list-group ul li a').on('click',function(){
			if($(this).hasClass('theme-change'))
			{				
				jQuery('body').toggleClass('theme-dark');
				jQuery('.theme-btn').toggleClass('active');
			}
		});
	}
    
    // Theme Version ============
	var handleRemoveClass = function() {
		jQuery('.nav-color').on('click',function(){
			jQuery('.sidebar, .menu-toggler').removeClass('show');
		});
	}
    
    // Theme Version ============
	var handleToggleButton = function() {
		jQuery('.dz-treeview-item').on('click',function(){
			jQuery(this).toggleClass('open');
		});
	}
	
	// Light Gallery ============
	var handleLightgallery = function() {
		if(jQuery('#lightgallery').length > 0){
			lightGallery(document.getElementById('lightgallery'), {
                plugins: [lgZoom, lgThumbnail],
            });
		}
		if(jQuery('#lightgallery-2').length > 0){
			lightGallery(document.getElementById('lightgallery-2'), {
                plugins: [lgZoom, lgThumbnail],
            });
		}
		if(jQuery('#lightgallery-3').length > 0){
			lightGallery(document.getElementById('lightgallery-3'), {
                plugins: [lgZoom, lgThumbnail],
            });
		}
		if(jQuery('#lightgallery-4').length > 0){
			lightGallery(document.getElementById('lightgallery-4'), {
                plugins: [lgZoom, lgThumbnail],
            });
		}
		
		// lightgallery by class name
		if(jQuery('.lightgallery').length > 0){
			var elements = document.getElementsByClassName('lightgallery');
			for (let item of elements) {
				lightGallery(item,{
					plugins: [lgZoom, lgThumbnail],
				})
			}
		}
	}
    
    // Tab Slide ============ 
	var handleTabSlide = function() {
		if(jQuery('.tab-slide-effect').length > 0){
			var a =  $('.tab-slide-effect li.active').width();
			var b =  $('.tab-slide-effect li.active').position().left;
			$('.tab-active-indicator').css({'width' : a,'transform': 'translateX('+b+'px)'});
			$('.tab-slide-effect li').on('click',function(){
                var tabItem = $(this).parent().find('li');
				$(tabItem).removeClass('active');
                $(this).addClass('active');
				var x = $(this).width();
				var y = $(this).position().left;
                var indicator = $(this).parent().find('.tab-active-indicator');
				$(indicator).css({'width' : x,'transform': 'translateX('+y+'px)'});
			})
		}
	}
	
	// OTP Input ============ 
    var handleOTP = function() {
		if(jQuery('#otp').length > 0)
		$('.digit-group').find('input').each(function() {
            $(this).attr('maxlength', 1);
            $(this).on('keyup', function(e) {
                var thisVal = $(this).val();
                var parent = $($(this).parent());
                
                if(e.keyCode === 8 || e.keyCode === 37) {
                    var prev = parent.find('input#' + $(this).data('previous'));
                    
                    if(prev.length) {
                        $(prev).select();
                    }
                } else {
                    var next = parent.find('input#' + $(this).data('next'));
                    
                    if(!$.isNumeric(thisVal))
                    {
                        $(this).val('');
                        return false;
                    }

                    if(next.length) {
                        $(next).select();
                    } else {
                        if(parent.data('autosubmit')) {
                            parent.submit();
                        }
                    }
                }
            });
        });
		
	}
    
	function getCodeBoxElement(index) {
        return document.getElementById('codeBox' + index);
    }
    function onKeyUpEvent(index, event) {
		const eventCode = event.which || event.keyCode;
		if (getCodeBoxElement(index).value.length === 1) {
			if (index !== 4) {
				getCodeBoxElement(index+ 1).focus();
			} else {
				getCodeBoxElement(index).blur();
				console.log('submit code ');
			}
		}
		if (eventCode === 8 && index !== 1) {
			getCodeBoxElement(index - 1).focus();
		}
    }
    function onFocusEvent(index) {
		for (item = 1; item < index; item++) {
			const currentElement = getCodeBoxElement(item);
			if (!currentElement.value) {
				currentElement.focus();
				break;
			}
		}
    }
	
	// Post Like ============
	var handlePostLike = function() {
		$('.action-btn.bg-primary').on('click',function(){
			var userText = Number($(this).parent().find("#value1").text());

			if($(this).hasClass('active')){
				$(this).removeClass('active');
				$(this).parent().find("#value1").text(userText - 1);
			}else{
				$(this).addClass('active');
				$(this).parent().find("#value1").text(userText + 1);
			}
        }); 
	} 
    
	// Input Search ============
    var handleSearch = function() {
      $("#myInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".recent-jobs-list li").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
        if($(this).val().length > 0){
            $('.search-input .btn-close').fadeIn(500);
        }else{
            $('.search-input .btn-close').fadeOut(500);
        }
      });
      
        $('.search-input .btn-close').on('click',function(){
            $(".recent-jobs-list li").filter(function() {
              $(this).toggle($(this).text().toLowerCase().indexOf('') > -1)
            });
            $('.search-input .form-control').val(null);
            $(this).fadeOut(0);
        })
	}
	
	// Input With Image Select ============
	var handleImageSelect = function(){
		if(jQuery('.image-select').length > 0){
			const $_SELECT_PICKER = $('.image-select');
			$_SELECT_PICKER.find('option').each((idx, elem) => {
				const $OPTION = $(elem);
				const IMAGE_URL = $OPTION.attr('data-thumbnail');
				if (IMAGE_URL) {
					$OPTION.attr('data-content', "<img src='%i'/> %s".replace(/%i/, IMAGE_URL).replace(/%s/, $OPTION.text()))
				}
			});
			$_SELECT_PICKER.selectpicker();
		}
	}
    
	// Change Text ============
	var handleChangeText = function (){
		$('.follow-btn').on('click', function(){
			$(this).toggleClass('follow');
			if($(this).hasClass('follow')){
				$(this).text('FOLLOW')
			}
			else{
				$(this).text('UNFOLLOW')
			}
		});
	}
	
	// Like Button ============
	var handleLikeButton = function (){
		$(".item-bookmark").on('click', function(){
			$(this).toggleClass("active");
		});
	}
	
	// Like Button ============
	var handleLikeButton2 = function (){
		$(".like-button").on('click', function(){
			$(this).toggleClass("active");
		});
	}
	
	// TouchSpin ============
	var handleTouchSpin = function (){
		if(jQuery('.stepper').length > 0){	
			$(".stepper").TouchSpin({
				initval: 1
			});
		}
	}
	
	var handleChangeElement = function (){
		$(".add-btn").on('click', function(){
			$(this).offsetParent().find(".dz-stepper").addClass('active');
			$(this).offsetParent().find(".stepper").val(1);
		});
		$(".dz-stepper .stepper").on('change',function(){
			if($(this).val() == 0){
				$(this).offsetParent().offsetParent().removeClass('active');
			}
		});
	}
	
	// Cross List ============
	var handleTagRemove = function (){
        $(".remove-tag").on('click',function(){
            $(this).parent().remove();
        })
		$(".all-close").on('click',function(){
            $(".remove-tag").parent().remove();
        })
	}
	
	// Add List ============
	var handleTagRemove2 = function (){
		$('.main-in').on('keypress',function(e) {
			if(e.which == 13 && $(this).val().length > 0) {
				var x = $(this).val();
				$('.recent-search-list').append('<li><div class="d-flex justify-content-spece-between align-item-center">'+'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 11C21.7348 11 21.4804 11.1054 21.2929 11.2929C21.1053 11.4804 21 11.7348 21 12C21.0041 14.046 20.3124 16.0325 19.0384 17.6334C17.7645 19.2344 15.984 20.3545 13.9894 20.8099C11.9948 21.2654 9.90455 21.029 8.062 20.1397C6.21945 19.2503 4.73407 17.7608 3.8498 15.9159C2.96552 14.0709 2.7349 11.98 3.19581 9.98665C3.65672 7.99328 4.78176 6.21589 6.38618 4.94634C7.99061 3.67679 9.97905 2.99055 12.025 3.00031C14.0709 3.01006 16.0527 3.71523 17.645 5.00002H16C15.7348 5.00002 15.4804 5.10537 15.2929 5.29291C15.1053 5.48044 15 5.7348 15 6.00002C15 6.26523 15.1053 6.51959 15.2929 6.70712C15.4804 6.89466 15.7348 7.00002 16 7.00002H20C20.2652 7.00002 20.5195 6.89466 20.7071 6.70712C20.8946 6.51959 21 6.26523 21 6.00002V2.00002C21 1.7348 20.8946 1.48044 20.7071 1.29291C20.5195 1.10537 20.2652 1.00002 20 1.00002C19.7348 1.00002 19.4804 1.10537 19.2929 1.29291C19.1053 1.48044 19 1.7348 19 2.00002V3.53102C17.0727 1.94258 14.6617 1.05806 12.1644 1.02319C9.66714 0.988309 7.23246 1.80516 5.26153 3.33915C3.2906 4.87314 1.901 7.03275 1.32169 9.46217C0.742374 11.8916 1.00791 14.4459 2.07453 16.7042C3.14116 18.9625 4.94525 20.7901 7.18956 21.8859C9.43387 22.9817 11.9845 23.2803 14.4212 22.7325C16.858 22.1847 19.0354 20.8232 20.5948 18.8723C22.1542 16.9214 23.0025 14.4976 23 12C23 11.7348 22.8946 11.4804 22.7071 11.2929C22.5195 11.1054 22.2652 11 22 11Z" fill="#7D8FAB"/><path d="M12 5.00002C11.7348 5.00002 11.4804 5.10537 11.2929 5.29291C11.1054 5.48045 11 5.7348 11 6.00002V12C11.0001 12.2652 11.1055 12.5195 11.293 12.707L14.293 15.707C14.4816 15.8892 14.7342 15.99 14.9964 15.9877C15.2586 15.9854 15.5094 15.8803 15.6948 15.6948C15.8802 15.5094 15.9854 15.2586 15.9877 14.9964C15.99 14.7342 15.8892 14.4816 15.707 14.293L13 11.586V6.00002C13 5.7348 12.8946 5.48045 12.7071 5.29291C12.5196 5.10537 12.2652 5.00002 12 5.00002Z" fill="#7D8FAB"/></svg>'+'<h5 class="sub-title ms-2 mb-0">'+x+'</h5></div><a href="javascript:void;" class="close-1 remove-tag"><i class="fa-solid fa-xmark cross"></i></a></li>');
				$(this).val(null);
				
				$(".remove-tag").on('click',function(){
					$(this).parent().remove();
				})
			}
		});
	}
	
	var handleWizardSteps = function (){		
		if(jQuery('#DZWizardSteps').length > 0){			
			$("#DZWizardSteps").steps({
				headerTag: "h3",
				bodyTag: "section",
				transitionEffect: "slideLeft",
				autoFocus: true,
				onFinishing: function (event, currentIndex) { window.location.href = "payment-confirm.html" }, 
			});
		}
	}
	
	
	var handleAddBox = function (){
		$('.submit-btn').on('click',function() {
			var x = $('.review').val();
			$('.review-box').append('<div class="card"><div class="card-body"><div class="d-flex align-items-center border-bottom pb-3 mb-2"><div class="media media-50 me-2"><img class="rounded" src="assets/images/avatar/1.jpg" alt="https://w3grocery.dexignzone.com/"></div><div><h6 class="mb-0">David Martin</h6><span class="text-soft">1 hour ago</span></div></div><div class="review-content"><ul class="d-flex align-items-center"><li class="pe-1"><span class="font-w600 font-16 text-dark">4.5</span></li><li><i class="fa-solid fa-star"></i></li><li><i class="fa-solid fa-star"></i></li><li><i class="fa-solid fa-star"></i></li><li><i class="fa-solid fa-star"></i></li><li><i class="fa-solid fa-star-half-stroke"></i></li></ul><p class="font-15 mb-0 text-soft">'+x+'</p></div></div></div>');
			$(this).val(null);
			$('.add-review').modal('hide');
		});
	}
	
	var handleSnackbar = function (){
		$('.snackbar-btn').click(function() {
			var pos = $(this).attr('data-pos');
			var text = $(this).attr('data-Text');
			var bg = $(this).attr('data-Bg');
			var actionText = $(this).attr('data-actionText');
			var actionColor = $(this).attr('data-actionColor');
			Snackbar.show({
				text: text,
				//actionText: actionText,
				actionTextColor: actionColor,
				backgroundColor: bg,
				width: 'auto',
				pos: pos
			  });
		});
	}
	
	/* Masonry Box ============ */
	var masonryBox = function(){
		
		/* masonry by  = bootstrap-select.min.js */
		if(jQuery('#masonry, .masonry').length > 0)
		{
			jQuery('.filters li').removeClass('active');
			jQuery('.filters li:first').addClass('active');
			var self = jQuery("#masonry, .masonry"); 
			var filterValue = "";
	 
			if(jQuery('.card-container').length > 0)
			{
				var gutterEnable = self.data('gutter');
				
				var gutter = (self.data('gutter') === undefined)?0:self.data('gutter');
				gutter = parseInt(gutter);
				
				
				var columnWidthValue = (self.attr('data-column-width') === undefined)?'':self.attr('data-column-width');
				if(columnWidthValue != ''){columnWidthValue = parseInt(columnWidthValue);}
				
				self.imagesLoaded(function () {
					filter: filterValue,
					self.masonry({
						gutter: gutter,
						columnWidth:columnWidthValue, 
						isAnimated: true,
						itemSelector: ".card-container",
					});
					
				}); 
			} 
		}
		if(jQuery('.filters').length)
		{
			jQuery(".filters li:first").addClass('active');
			
			jQuery(".filters").on("click", "li", function() {
				
				jQuery('.filters li').removeClass('active');
				jQuery(this).addClass('active');
				
				var filterValue = $(this).attr("data-filter");
				self.isotope({ 
					filter: filterValue,
					masonry: {
						gutter: gutter,
						columnWidth: columnWidthValue,
						isAnimated: true,
						itemSelector: ".card-container"
					}
				});
			});
		}
		/* masonry by  = bootstrap-select.min.js end */
	}
	
	
	var appNavigateShare = function(){

		const dzShareData = {
			title: 'W3Kit',
			text: 'W3Kit: Pre-Build Mobile App Template ( Bootstrap 5 + PWA )',
			url: document.location.protocol + "//" + document.location.host
		}

		jQuery('#shareBtn').on('click', function() {
			if(navigator.share)
			{
				navigator.share(dzShareData);
			}
		});
		
	}
	
	var handlelangPicker = function(){
		if(jQuery('#offcanvasLang').length > 0)
		{
			const bsOffcanvas = new bootstrap.Offcanvas('#offcanvasLang')
			$('.confirm-lang li').on('click', function () {
				var x =  $(this).attr("data-lang")
				$('.select-lang').text(x)
				bsOffcanvas.hide();
			});
		}
	}
	
	/* Function ============ */
	return {
		init:function(){
			handleMenubar();
			handlelangPicker();
			handleThemeVersion2();
			handleToggleButton();
			handleLikeButton();
			handleLikeButton2();
			handleShowPass();
			handleWizardSteps();
			handleIsFixed();
			handleSnackbar();
			handleChatBox();
			handleScrollTop();
			handleLightgallery();
			handleCustomFileInput();
            handleMessageHandle();
            handleGoBack();
            handlePWAModal();
            handleSearch();
            handleRemoveClass();
            handlePostLike();
			handleTabSlide();
			handleChangeText();
			handleImageSelect();
			handleOTP();
			handleTagRemove();
			handleTagRemove2();
			handleChangeElement();
			handleTouchSpin();
			handleAddBox();
			masonryBox();
		},

		load:function(){
			handlePreloader();
			handleSelectpicker();
			masonryBox();
			appNavigateShare();
		},
		
		resize:function(){
			screenWidth = $( window ).width();
			handleTabSlide();
		},
	}
	
}();

/* Document.ready Start */	
jQuery(document).ready(function() {
	W3Grocery.init();
	
	$('[data-bs-toggle="popover"]').popover();
    $('.theme-dark .custom-switch input').prop('checked', true);
	
});
/* Document.ready END */

/* Window Load START */
jQuery(window).on('load',function () {
	W3Grocery.load();
    
	setTimeout(function(){
		jQuery('#splashscreen').addClass('active');
	 	jQuery('#splashscreen').fadeOut(1500);
	}, 1500);
	
    $('.theme-dark .custom-switch input').prop('checked', true).addClass('active');
	
});
/*  Window Load END */

/* Window Resize START */
jQuery(window).on('resize',function () {
	
	W3Grocery.resize();
});
/*  Window Resize END */	