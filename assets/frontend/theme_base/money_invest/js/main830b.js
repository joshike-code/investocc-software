// Hyiprio main jQuery


(function($) {
    'use strict';

    // Menu sticky
    $(window).on('scroll',function() {    
        var scroll = $(window).scrollTop();
        if (scroll < 20) {
        $(".header").removeClass("sticky-header");
        }else{
        $(".header").addClass("sticky-header");
        }
    });

    // ToolTip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
    })


    // Side Nav Click Collapse
    $(".sidebar-toggle").on('click', function(){
        $(".panel-layout").toggleClass("nav-folded");
    });

    // Side Nav Hover Collapse
    $(".side-nav").hover(function(){
        $(".nav-folded .side-nav").toggleClass("side-nav-hover");
    });

    // Side Nav Click Dropdowns
    $(".side-nav-dropdown").on('click', function(){
        $(this).toggleClass("show");
    });

    // To top
    $.scrollUp({
        scrollText: '<i class="fa-solid fa-arrow-up"></i>',
        easingType: 'linear',
        scrollSpeed: 500,
        animation: 'fade'
    });

    // Nice Select
    $('.site-nice-select').niceSelect();


    //Counter-JS
    $('.count').counterUp({
        delay: 10,
        time: 2000
    });

    //lucide icons
    lucide.createIcons();

    // Color Switcher
    $(".color-switcher").on('click', function(){
        $("body").toggleClass("dark-theme");
    });

    // AOS initialization
    AOS.init();

    

    // Invoice Options
    $('.add-new-option').on('click',function(){
        var optionss = 
        `<div class="row">
          <div class="col-xl-8">
            <div class="input-group">
              <input type="text" class="form-control itemname"  name="name[]" placeholder="Item Name">
            </div>
          </div>
          <div class="col-xl-3">
            <div class="input-group">
              <input type="text" class="form-control amount"  name="amount[]" placeholder="Amount">
            </div>
          </div>
          <div class="col-xl-1">
            <button type="button" class="invoice-option-btn add-new-option remove-optionss"><i class="anticon anticon-minus"></i></button>
          </div>
      </div>`
      $('.optionss').append(optionss);
   });
   // Invoice Options Remove
   $(document).on('click','.remove-optionss',function () { 
       $(this).closest('.row').remove();
   });


   $('.currency').on('change',function () { 
    var selected = $('.currency option:selected')
    if(selected.val() == ''){
       $('.itemname').attr('disabled',true)
       $('.amount').attr('disabled',true)
       $('.add-new-option').addClass('disabled')
       return false
    }else{
       $('.itemname').attr('disabled',false)
       $('.amount').attr('disabled',false)
       $('.add-new-option').removeClass('disabled')
    }
    $('.currencyCode').text(selected.data('code'))
})



$(document).on('keyup','.amount',function () { 
    var total = 0;
    $('.amount').each(function(e){
        if($(this).val()!=''){
            total += parseFloat($(this).val());
        }
        $('.totalAmount').text(total.toFixed(2))
    })
})


// Image Preview
$('input[type="file"]').each(function(){
    // Refs
    var $file = $(this),
        $label = $file.next('label'),
        $labelText = $label.find('span'),
        labelDefault = $labelText.text();
  
    // When a new file is selected
    $file.on('change', function(event){
      var fileName = $file.val().split( '\\' ).pop(),
          tmppath = URL.createObjectURL(event.target.files[0]);
      //Check successfully selection
          if( fileName ){
        $label
          .addClass('file-ok')
          .css('background-image', 'url(' + tmppath + ')');
              $labelText.text(fileName);
      }else{
        $label.removeClass('file-ok');
              $labelText.text(labelDefault);
      }
    });
  });


  
  // DataTable
//   $('#myDataTable2').DataTable({
//     // paging: false,
//     // searching: false,
//   });


// Auto Popup
$('.auto-popup-close').on('click', function () {
    var $this = $(this),
        $popup = $this.closest('#auto-popup'),
        $dialog = $this.parent('.auto-popup-dialog');
    $popup.addClass('close');
    $dialog.removeClass('fadeInUp').addClass('fadeOutUp');
});


// magnificPopup initialization
$('.video-popup').magnificPopup({
    type: 'iframe',
});

$('.cer-img').magnificPopup({type:'image'});

  
    
    
})(jQuery);