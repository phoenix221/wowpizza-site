// JavaScript Document
$(document).ready(function () {

    // fancybox

    // class="fancybox" data-fancybox-group="gallery"
    // $('.fancybox').fancybox();

    // class="fancybox-thumbs" data-fancybox-group="thumb"
    // $('.fancybox-thumbs').fancybox({
    // prevEffect : 'none',
    // nextEffect : 'none',

    // closeBtn  : true,
    // arrows    : true,
    // nextClick : true,

    // helpers : {
    // thumbs : {
    // width  : 50,
    // height : 50
    // }
    // }
    // });
    // fancybox

    // Slick Slider
    // <section class="slider">
    // <img src="/images/slides/1.jpg" alt="">
    // <img src="/images/slides/2.jpg" alt="">
    // </section>
    // $('.slider').slick({
    // dots: false,
    // infinite: true,
    // speed: 900,
    // fade: true,
    // cssEase: 'linear',
    // autoplay: true,
    // autoplaySpeed: 2000,
    // arrows: false,
    // });


    let counter = 0;
    $(window).scroll(function() {
        var scroll = $(window).scrollTop() + $(window).height();
        //Если скролл до конца елемента
        var offset = $('#scroll-check').offset().top + $('#scroll-check').height();
        //Если скролл до начала елемента

        if(scroll > offset && counter == 0) {
            $('#vk-count').animateNumber({
                number: $('#vk-count').data('count')
            },4000);
            $('#inst-count').animateNumber({
                number: $('#inst-count').data('count')
            },2000);
            counter = 1;
        }


    });



});

function captcha_clean(){
    $("#data_captcha").val("").focus();
}
