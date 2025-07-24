/**
	Template Name 	 : W3Grocery
	Author			 : DexignZone
	Version			 : 1.0
	File Name		 : dz.carousel.js
	Author Portfolio : https://themeforest.net/user/dexignzone/portfolio
	
	Core script to handle the entire theme and core functions
	
**/

/* JavaScript Document */
jQuery(document).ready(function() {
    'use strict';
	
    // Get Started ==========
    if(jQuery('.get-started').length > 0){
		var swiperGetStarted = new Swiper('.get-started', {
			speed: 1500,
			parallax: true,
			slidesPerView: "auto",
			spaceBetween: 0,
			autoplay: true,
			loop:false,
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	if(jQuery('.recomand-swiper').length > 0){
		var swiperRecomandSwiper = new Swiper('.recomand-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 1.2,
			spaceBetween: 10,
            centeredSlides: true,
			loop: true,
			navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
			breakpoints: {
				1024: {
				  slidesPerView: 3,
				},
				768: {
				  slidesPerView: 2,
				},
				576: {
				  slidesPerView: 1.5,
				},
			},
		});
	}
    
	if(jQuery('.categorie-swiper').length > 0){
		var swiper4 = new Swiper('.categorie-swiper', {
			speed: 1500,
			parallax: true,
			slidesPerView: "auto",
			spaceBetween: 0,
			loop:true,
		});
	}
	
	if(jQuery('.recomanded-swiper').length > 0){
		var swiperGetStarted = new Swiper('.recomanded-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 20,
			loop: false,
			navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	// Team Swiper
	if(jQuery('.demo-swiper').length > 0){
		var swiper4 = new Swiper('.demo-swiper', {
			speed: 1500,
			parallax: true,
			slidesPerView: "auto",
			spaceBetween: 0,
			autoplay: true,
			loop:false,
            navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	if(jQuery('.offer-swiper').length > 0){
		var swiperGetStarted = new Swiper('.offer-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 20,
			loop: false,
		});
	}
	
	if(jQuery('.team-swiper-4').length > 0){
		var swiper4 = new Swiper('.team-swiper-4', {
			speed: 1500,
			parallax: true,
			slidesPerView: "auto",
			spaceBetween: 10,
			loop:false,
		});
	}
	
	//Ecommerce Swiper ===
	if(jQuery('.ecommerce-swiper1').length > 0){
		var swiperRecomandSwiper = new Swiper('.ecommerce-swiper1', {
			speed: 500,
			parallax: true,
			slidesPerView: 1.2,
			spaceBetween: 10,
            centeredSlides: true,
			loop: true,
			navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	//Milk Swiper ===
	if(jQuery('.milk-swiper').length > 0){
		var swiperRecomandSwiper = new Swiper('.milk-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 1,
			spaceBetween: 0,
            centeredSlides: true,
			loop: true,
			navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	//Milk Swiper ===
	if(jQuery('.milk-swiper2').length > 0){
		var swiperRecomandSwiper = new Swiper('.milk-swiper2', {
			speed: 500,
			parallax: true,
			slidesPerView: 1,
			spaceBetween: 0,
            centeredSlides: true,
			loop: false,
			navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	
	
	//Fruit Swiper ===
	if(jQuery('.fruit-swiper').length > 0){
		var swiperRecomandSwiper = new Swiper('.fruit-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 1.2,
			spaceBetween: 12,
            centeredSlides: true,
			loop: true,
			navigation: {
				nextEl: '.swiper-btn-next',
				prevEl: '.swiper-btn-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	//Fruit Swiper2 ===
	if(jQuery('.fruit-swiper2').length > 0){
		var swiperRecomandSwiper = new Swiper('.fruit-swiper2', {
			speed: 500,
			parallax: true,
			slidesPerView: 1,
			spaceBetween: 0,
            centeredSlides: true,
			loop: false,
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	//Meat Swiper ===
	if(jQuery('.meat-swiper').length > 0){
		var swiperMeatSwiper = new Swiper('.meat-swiper',{
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: false,
			autoplay: {
				delay: 2000,
			},
			navigation: {
				nextEl: '.meat-swiper-button-next',
				prevEl: '.meat-swiper-button-prev',
            },
			pagination: {
                el: ".meat-swiper-pagination",
                clickable: true,
			},
		});
	}
	
	if(jQuery('.product-swiper').length > 0){
		var swiperRecomandSwiper = new Swiper('.product-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: false,
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
            },
			pagination: {
                el: ".swiper-pagination",
                clickable: true,
			},
		});
	}
	
	if(jQuery('.item-swiper').length > 0){
		var swiper4 = new Swiper('.item-swiper', {
			speed: 1500,
			parallax: true,
			slidesPerView: "auto",
			spaceBetween: 10,
			loop:false,
		});
	}
	
	if(jQuery('.item-swiper2').length > 0){
		var swiper = new Swiper(".item-swiper2", {
			direction: "vertical",
			loop: false,
			slidesPerView: 'auto',
			spaceBetween: 0,
			freeMode: true,
			watchSlidesProgress: true,
		});
		var swiper2 = new Swiper(".item-swiper3", {
			loop: true,
			spaceBetween: 0,
			thumbs: {
			  swiper: swiper,
			},
		});
	}
	
	//Restaurant Swiper ====
	if(jQuery('.offer-swiper1').length > 0){
		var swiperRecomandSwiper = new Swiper('.offer-swiper1', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: false,
		});
	}
	
	//Offer Swiper ====
	if(jQuery('.offer-swiper2').length > 0){
		var swiperRecomandSwiper = new Swiper('.offer-swiper2', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: false,
		});
	}
	
	// Welcome page Work Strat ======
	//Package swiper ===
	if(jQuery('.package-swiper').length > 0){
		var swiperRecomandSwiper = new Swiper('.package-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: false,
		});
	}
	
	//Client swiper ===
	if(jQuery('.client-swiper').length > 0){
		var swiperRecomandSwiper = new Swiper('.client-swiper', {
			speed: 500,
			parallax: true,
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: false,
		});
	} 
});
/* Document .ready END */