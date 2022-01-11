// JavaScript Document
$(document).ready(function () {
    // Slick Slider
    // <section class="slider">
    // <img src="/images/slides/1.jpg" alt="">
    // <img src="/images/slides/2.jpg" alt="">
    // </section>

    init_main_slider();
    init_mobile_slider();


    // Validate Jquery
    // Сообщение об ошибке
    // $.validator.messages.required = $('input[name=required_error]').val();
    // инициализируем валидацию формы
    // $('#profile-form').validate();
    // Пример
    // <label>
    // <span><i class="icon icon-c"></i>{"company"|t}</span>
    // <div class="inp"><input type="text" name="title" class="required" value="{user.title}" required /></div>
    // </label>
    if(typeof ym !== 'undefined') {
        var counter = $('#yandex_counter').val();
        ym(counter, 'getClientID', function (clientID) {
            document.getElementById('clientID').value = clientID;
        });
    }

    $('.cart-products').scroll(function () {
        var wt = $('.white-trans');
        if(this.scrollHeight - this.scrollTop === this.clientHeight) {
            if(wt.is(':visible')){
                wt.hide('fast');
            }
        }else{
            if(wt.is(':hidden')){
                wt.show('fast');
            }
        }
    });

    // прилипание шапки
    var $navBar = $('#sticky');
    // find original navigation bar position
    if($navBar.length){
        var navPos = $navBar.offset().top;
    }

    //высота фиксированного блока
    var navbar_height = $navBar.outerHeight();
    //запомним стандартный отступ
    var navbar_pb = parseInt($navBar.parent().css("padding-bottom"));

    $(window).scroll(function() {
        // get scroll position from top of the page
        var scrollPos = $(this).scrollTop();
        // check if scroll position is >= the nav position
        if (scrollPos >= navPos) {
            $navBar.parent().css('padding-bottom', navbar_height);
            $navBar.addClass('sticky-fixed');
        } else {
            $navBar.parent().css('padding-bottom', navbar_pb);
            $navBar.removeClass('sticky-fixed');
        }
    });
    // get scroll position from top of the page
    var scrollPos = $(this).scrollTop();
    // check if scroll position is >= the nav position
    if (scrollPos >= navPos) {
        $navBar.parent().css('padding-bottom', navbar_height);
        $navBar.addClass('sticky-fixed');
    } else {
        $navBar.parent().css('padding-bottom', navbar_pb);
        $navBar.removeClass('sticky-fixed');
    }
    // прилипание шапки

    // прилипание подкатегорий
    var topmenuheight = 71;
    if($(window).width()>991){
        topmenuheight = 51;
    }
    var $subnavBar = $('#sticky-subcategories');
    // find original navigation bar position
    if($subnavBar.length){
        var subnavPos = $subnavBar.offset().top-topmenuheight;
    }

    //высота фиксированного блока
    var subnavbar_height = $subnavBar.outerHeight();
    //запомним стандартный отступ
    var subnavbar_pb = parseInt($subnavBar.parent().css("padding-top"));

    $(window).scroll(function() {
        // get scroll position from top of the page
        var subscrollPos = $(this).scrollTop();
        // check if scroll position is >= the nav position
        if (subscrollPos >= subnavPos) {
            $subnavBar.parent().css('padding-top', subnavbar_height);
            $subnavBar.addClass('substicky-fixed');
            $subnavBar.find('.subcategories').addClass('container');
        } else {
            $subnavBar.parent().css('padding-top', subnavbar_pb);
            $subnavBar.removeClass('substicky-fixed');
            $subnavBar.find('.subcategories').removeClass('container');
        }
    });
    // get scroll position from top of the page
    var subscrollPos = $(this).scrollTop();
    // check if scroll position is >= the nav position
    if (subscrollPos >= subnavPos) {
        $subnavBar.parent().css('padding-top', subnavbar_height);
        $subnavBar.addClass('substicky-fixed');
        $subnavBar.find('.subcategories').addClass('container');
    } else {
        $subnavBar.parent().css('padding-top', subnavbar_pb);
        $subnavBar.removeClass('substicky-fixed');
        $subnavBar.find('.subcategories').removeClass('container');
    }
    // прилипание подкатегорий

    var tpmnheight = 110;
    if($(window).width()>991){
        tpmnheight = 135;
    }
    $('.subcat-lnk').on('click', function() {
        let href = $(this).attr('href');
        let hot = Number($(href).offset().top) - tpmnheight;
        $('html, body').animate({
            scrollTop: hot
        },600);
        return false;
    });

    // меню в мобилке
    $('.nav-mobile').slideAndSwipe();

    // автоматический скролл в менюшке
    var containerOuterWidth = $('.white-bg').outerWidth();
    if($(".menu li.active").length){
        var itemOuterWidth = $(".menu li.active").outerWidth();
        var itemOffsetLeft = $(".menu li.active").offset().left;
        var containerScrollLeft = $(".white-bg").scrollLeft(); // узнаем текущее значение скролла
        var positionCetner = (containerOuterWidth / 2 - itemOuterWidth / 2);
        var scrollLeftUpd = containerScrollLeft + itemOffsetLeft - positionCetner;
        $('.white-bg').animate({
            scrollLeft: scrollLeftUpd
        }, 200);
    }
    // автоматический скролл в менюшке
    // обработчик клика по элементу
    $(".menu li a").click(function() {
        var itemOuterWidth = $(this).outerWidth();
        var itemOffsetLeft = $(this).offset().left;
        var containerScrollLeft = $(".white-bg").scrollLeft();
        var positionCetner = (containerOuterWidth / 2 - itemOuterWidth / 2);
        var scrollLeftUpd = containerScrollLeft + itemOffsetLeft - positionCetner;
        // анимируем
        $('.white-bg').animate({
            scrollLeft: scrollLeftUpd
        }, 400);
    });

    $(".review-but").click(function() {
        $(".review-but").removeClass('active');
        $(this).addClass('active');
        var block_id = $(this).data('block-id');
        $('.review_block').hide();
        $('#'+block_id).show();
    });

    $(".reviews-items").click(function() {
        $(".reviews-items").removeClass('reviews-items-active');
        $(this).addClass('reviews-items-active');
        var block_id = $(this).data('block-id');
        $('.rev-sites').hide();
        $('#'+block_id).show();
    });

    $(".stickers-list").hover(function() {
        var wrap = $(this);
        var imgs = wrap.find('img');
        imgs.each(function(){
            var s = $(this).attr('style');
            var ds = $(this).attr('data-style');
            $(this).attr('style', ds).attr('data-style', s).data('style', s);
        })
    });

    // маска для телефона
    $('#auth-phone').inputmask({
        mask: "+7 (999) 999-99-99",
        placeholder: "+7 (___) ___-__-__",
        showMaskOnHover: true,
        showMaskOnFocus: true,
        oncomplete: function(){
            auth_phone_complete();
        },
        onincomplete: function(){
            auth_phone_incomplete();
        },
    });

    // маска для кода подтверждения
    $('#conf-code').inputmask({
        mask: "9 9 9 9",
        placeholder: "_ _ _ _",
        showMaskOnHover: true,
        showMaskOnFocus: true,
        oncomplete: function(){
            check_conf_code();
        }
    });

    // маска для кода подтверждения
    $('#conf-code-order').inputmask({
        mask: "9 9 9 9",
        placeholder: "_ _ _ _",
        showMaskOnHover: true,
        showMaskOnFocus: true,
        oncomplete: function(){
            check_conf_code_order();
        }
    });

    // маска для кода подтверждения
    $('#conf-code-details-order').inputmask({
        mask: "9 9 9 9",
        placeholder: "_ _ _ _",
        showMaskOnHover: true,
        showMaskOnFocus: true,
        oncomplete: function(){
            check_conf_code_details_order();
        }
    });

    // маска для кода подтверждения
    $('#conf-code-cansel-order').inputmask({
        mask: "9 9 9 9",
        placeholder: "_ _ _ _",
        showMaskOnHover: true,
        showMaskOnFocus: true,
        oncomplete: function(){
            check_conf_code_cancel_order();
        }
    });

    // маска для даты рождения
    $('#birthday_input').inputmask({
        mask: "d9.m9.yu99",
        definitions: {
            "d": {
                validator: "[0123]",
            },
            "m": {
                validator: "[01]",
            },
            "y": {
                validator: "[12]",
            },
            "u": {
                validator: "[90]",
            }
        },
        placeholder: "__.__.____",
        showMaskOnHover: true,
        showMaskOnFocus: true
    });


    $('#auth-modal').on('shown.bs.modal', function(){
        if($('#auth-modal').data('change-password')==0){
            $('#auth-phone').prop('readonly', false).val('+7').focus();
            auth_phone_incomplete();
            $('#reg-password-block').hide();
            $('#confirm-phone').show();
            $('#forgot-password').val('0');
        }
        if($('#auth-modal').data('change-phone')==0 && $('#auth-modal').data('change-password')==0){
            $('#auth-hint').show().html('пожалуйста введите свой номер телефона, что-бы авторизоваться или зарегистрироваться');
        }
        $('#auth-password').removeClass('error');
        $('#confirm-phone-block').hide();
        $('#rg-hint').show();
        $('#auth-error').hide().removeClass('mt-0');
        $('#reg-password').val('').removeClass('m_error').removeClass('error')
        $('#reg-confirm-password').val('').removeClass('m_error').removeClass('error');
        $('#error-code-hint').hide();
        $('#resend-hint').hide();
    });

    $('#add-address-modal').on('hidden.bs.modal', function(){
        var modal = $('#add-address-modal');
        var form = modal.find('form');
        var h = modal.find('h4');
        var hint = modal.find('.hint');
        var btn = form.find('button[type="submit"]');

        var input_edit = form.find('input[name="edit"]');
        var input_address = $(form).find('input[name="address"]');
        var input_room_number = $(form).find('input[name="room-number"]');
        var input_entrance = $(form).find('input[name="entrance"]');
        var input_floor = $(form).find('input[name="floor"]');
        var input_title = $(form).find('input[name="title"]');

        input_edit.val('');
        input_address.val('');
        input_room_number.val('');
        input_entrance.val('');
        input_floor.val('');
        input_title.val('');
        h.html('Добавить адрес доставки');
        hint.html('для добавления адреса, заполните поля ниже');
        btn.prop('disabled', true);
    });


    $('#order-confirmation-modal').on('hidden.bs.modal', function(){
        $('#co-resend-hint').html('').hide();
    });

    $('#info-modal').on('hidden.bs.modal', function(){
        $('#info-modal').find('.modal-content').removeClass('fffopacity');
    });

    $('#change_delivery').on('hidden.bs.modal', function(){
        $('#change_delivery .mctxctx p').show();
        $('#change_delivery .mctxctx p.clear_promo_text').hide();

        $('#change_delivery .modal-title').html('Изменение цен в корзине');
    });

    $('#dr-gift-er').on('hidden.bs.modal', function(){
        $('#dr-gift-er p.clserr').show();
        $('#dr-gift-er p.temperr').hide();
    });

    $('#del-gifts-info').on('hidden.bs.modal', function(){
        $('#p-pickup-gifts').hide();
        $('#p-dr-gifts').hide();
    });

    $('#del-promo-modal').on('hidden.bs.modal', function(){
        $('#del-promo-action-name').html('Изменение корзины');
        $('#del-promo-dop-text').hide();
        $('#del-promo-btns2').addClass('none');
        $('#del-promo-btns3').addClass('none');
        $('#del-promo-btns').removeClass('none');

        $('.dpm-classic-text').removeClass('none');
        $('.dpm-text2').addClass('none');

        $('#del-promo-btns2 button:eq(1)').removeClass('none');
        $('#del-promo-btns2 button:eq(2)').html('Использовать <span class="fact"></span>');

        var del_ids = $('#tempid');
        del_ids.each(function(){
            $(this).attr('id', '');
        });
    });

    $('#del-dr-gifts-info').on('hidden.bs.modal', function(){
        var del_ids = $('#tempid');
        del_ids.each(function(){
            $(this).attr('id', '');
        });
    });

    $('#auth-form').validate({
        errorPlacement: function(error,element) {
            return true;
        },
        submitHandler: function(form) {
            var btn = $(form).find('button[type=submit]');
            var load = $('#auth-loading');
            var input_password = $(form).find('input[name="password"]');
            var input_phone = $(form).find('input[name="hidden_login"]');
            var password_error = $('#auth-error');

            load.show();
            btn.prop('disabled', true);
            password_error.hide();
            input_password.removeClass('m_error');

            $.post('/ajax/auth', {phone:input_phone.val(), password:input_password.val()}, function(data) {
                // console.log(data);
                if(data=='success'){
                    window.location="/cabinet";
                }
                if(data=='error'){
                    password_error.show().html('пароль введен неверно');
                    input_password.addClass('m_error');
                    input_password.focus();
                }
                load.hide();
                btn.prop('disabled', false);
            });
        }
    });

    $('#write-guide').validate({
        errorPlacement: function(error,element) {
            return true;
        },
        submitHandler: function(form) {
            var btn = $(form).find('button[type=submit]');
            var load = $(form).find('.load');

            load.show();
            btn.hide();

            run_recaptcha('writeguide');
        }
    });


    $('#add-address-form').validate({
        errorPlacement: function(error,element) {
            return true;
        },
        submitHandler: function(form) {
            var btn = $(form).find('button[type=submit]');
            var load = $('#address-loading');
            var hint = $('#address-hint');

            var address = $(form).find('input[name="address"]');
            var room_number = $(form).find('input[name="room-number"]');
            var entrance = $(form).find('input[name="entrance"]');
            var floor = $(form).find('input[name="floor"]');

            var is_private = 0;
            if($(form).find('input[name="is_private_cab"]').is(':checked')){
                is_private = 1;
                //change-order-address
            }

            var title = $(form).find('input[name="title"]');
            var edit = $(form).find('input[name="edit"]');
            var lat = $(form).find('input[name="lat"]');
            var lon = $(form).find('input[name="lon"]');

            load.show();
            hint.hide();
            btn.prop('disabled', true);

            $.post('/ajax/add_address', {address:address.val(), room_number:room_number.val(), entrance:entrance.val(), floor:floor.val(), title:title.val(), edit:edit.val(), lon:lon.val(), lat:lat.val(), is_private:is_private}, function(data) {
                //console.log(data);
                if(data=='success'){
                    // hint.show().html('Адрес успешно добавлен').addClass('hint-success');
                    // address.val('');
                    // room_number.val('');
                    // entrance.val('');
                    // floor.val('');
                    // title.val('');
                    document.location.href = "/cabinet?action=add_address";
                }
                if(data=='success_edit'){
                    document.location.href = "/cabinet?action=edit_address";
                }
                if(data=='address_error'){
                    hint.show().html('Неверно указан адрес').addClass('error');
                    load.hide();
                }
                if(data=='title_error'){
                    hint.show().html('Необходимо указать название адреса').addClass('error');
                    load.hide();
                }
                if(data=='auth_error'){
                    hint.show().html('Ошибка авторизации. Обновите страницу и попробуйте еще раз.').addClass('error');
                    load.hide();
                }
                if(data=='room_error'){
                    hint.show().html('Необходимо указать квартиру, подъезд и этаж').addClass('error');
                    load.hide();
                }
            });
        }
    });

    $('#reg-form').validate({
        errorPlacement: function(error,element) {
            return true;
        },
        submitHandler: function(form) {
            var btn = $(form).find('button[type=submit]');
            var load = $('#address-loading');
            var forgot = $(form).find('input[name="forgot-password"]');
            var password = $(form).find('input[name="reg-password"]');
            var confirm_password = $(form).find('input[name="reg-confirm-password"]');
            var password_error = $('#auth-error');

            var input_password = $('#auth-password');
            var input_login = $('input[name="hidden_login"]');
            var hint = $('#auth-hint');
            var auth_block = $('#auth-block');

            load.show();
            btn.prop('disabled', true);
            password.removeClass('m_error');
            confirm_password.removeClass('m_error');

            $.post('/ajax/registration', {forgot:forgot.val(), password:password.val(), confirm_password:confirm_password.val()}, function(data) {
                // console.log(data);
                if(data=='success'){
                    // ЯМ цель
                    if(typeof ym !== 'undefined') {
                        ym($('#yandex_counter').val(), 'reachGoal', 'm_auth_send_code1');
                    }

                    password_error.hide();
                    window.location="/cabinet?action=registration";
                }else if(data=='forgot_success'){
                    if($('#auth-modal').data('change-password')==0) {
                        // переходим на окно авторизации
                        auth_block.show();
                        hint.html('пароль успешно изменен, пожалуйста авторизуйтесь').show();
                        input_login.val($('#auth-phone').val());
                        input_password.focus();

                        $('#reg-block').hide();
                        $('#reg-password-block').hide();
                        $('#auth-error').hide();
                        $('#auth-password').val('').focus().removeClass('error').removeClass('m_error');
                    }else{
                        $('#auth-modal').modal('hide');
                        $('#lk-success-alert').show().html('Пароль успешно изменен');
                    }
                }else{
                    if(data=='server_error'){
                        password_error.html('ошибка сервера, попробуйте выполнить операцию позже').show();
                    }else{
                        password_error.html(data).show();
                    }
                    password.addClass('m_error');
                    confirm_password.addClass('m_error');
                    password.focus();
                }
                load.hide();
                btn.prop('disabled', false);
            });
        }
    });

    // автоматический скролл в меню ЛК
    var containerOuterWidth = $('.lk-menu-wrap').outerWidth();
    if($(".lk-menu li.active").length){
        var itemOuterWidth = $(".lk-menu li.active").outerWidth();
        var itemOffsetLeft = $(".lk-menu li.active").offset().left;
        var containerScrollLeft = $(".lk-menu-wrap").scrollLeft(); // узнаем текущее значение скролла
        var positionCetner = (containerOuterWidth / 2 - itemOuterWidth / 2);
        var scrollLeftUpd = containerScrollLeft + itemOffsetLeft - positionCetner;
        $('.lk-menu-wrap').animate({
            scrollLeft: scrollLeftUpd
        }, 400);
    }
    // автоматический скролл в менюшке

    $('.lk-edit-block input').keyup(function(event){
        if(event.keyCode == 13){
            save_lk_info(this, $(this).attr('name'));
        }
    });
    $('.promo-tooltip').hide();

    $('#address-street').blur(function(){
        if($('#check_hints').val() == 2){
            setTimeout(function t() {
                if($('#address-street').data('select') != 1){
                    ya_check_address();
                }
            }, 1000);
        }
        $('#address-loading').hide();
    });
    $('#address-street').focus(function(){
        $('#address-street2').val('');
        $('#address-loading').show();
    });

    $('.onlynumber').bind("change keyup input click", function() {
        if (this.value.match(/[^0-9]/g)) {
            this.value = this.value.replace(/[^0-9]/g, '');
        }
    });

    $(document).on("keyup input", "#cho-wrap input#address-street",  function() {
        $('.btn-order').prop('disabled', true);
        $('#cho-wrap').data('zone', 0).attr('data-zone', 0);
    });

    // запускаем слайдер для продуктов
    ajax_slick_slider_products();

    // $(".add-comment span").click(function() {
    //     var textarea = $(this).closest('div').find('textarea');
    //     if(textarea.is(':hidden')){
    //         textarea.show().focus();
    //         $(this).html('Удалить комментарий');
    //     }else{
    //         textarea.hide();
    //         textarea.val('');
    //         $(this).html('Добавить комментарий');
    //     }
    // });

    zebra_cho_run();

    if($(".change-order-address").length){
        change_func_hint($(".change-order-address input[name=adress]:checked").val());
    }
    // переделал на onchange input
    //$(".change-order-address").on("change", "input[name=adress]", function(){
    //    change_order_address($(this).val());
    //});

    // маска для телефона в заказе
    $('#order-phone').inputmask({
        mask: "+7 (999) 999-99-99",
        oncomplete: function(){
            order_phone_complete();
        },
        onincomplete: function(){
            order_phone_incomplete();
        },
    });

    $('.copy-js').click(function () {
        var msg = $('#copy-msg');
        msg.html($(this).data('msg'));
        copyToClipboard(document.getElementById($(this).data('copyid')));
        msg.addClass('copy-msg-show');
        setTimeout(function(){
            msg.removeClass('copy-msg-show');
        }, 1500);
    });

    init_gifts_ts();
    init_gifts_dr();
    ra_order_date_run();

    $(document).mouseup(function (e){
        var div = $(".tr-menu-wrap");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            findhide();
        }
    });

    $(document).click(function (e){
        var div = $(".lk-o-lnk");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            $('.lk-cart-detail').removeClass('show');
        }
    });

    // кнопка вверх
    $(window).scroll(function(){
        if($(this).scrollTop()>100){
            $('.scrollup').fadeIn();
        }else{
            $('.scrollup').fadeOut();
        }
    });

    $('.scrollup').click(function(){
        $("html, body").animate({ scrollTop: 0 }, 600);
        return false;
    });

    $('.autoscroll').click(function(){
        var scrl = Number($($.attr(this, 'href')).offset().top) - 80;
        $('html, body').animate({
            scrollTop: scrl
        }, 400);
        return false;
    });

    if($('#i-info-modal').val()=='show'){
        $('#info-modal').modal('show');
    }
    if($('#i-wt-modal').val()=='show'){
        var flag_wt = 1;
        $.post('/ajax/wt_modal', {flag_wt: flag_wt}, function(data){
            if(data.length){
                var res = JSON.parse(data);
                var wt_title = "Привет :)";
                var wt_text = "Мы пока закрыты.<br>Откроемся в"+res['bukva']+" "+res['f_week_day']+" в "+res['f_worktime'];
                $('.modal-title').text(wt_title);
                $('.bg-wt').html(wt_text);
                $('#wt-modal').modal('show');
            }
        });
    }
    if($('#i-okt-modal').val()=='show'){
        $('#okt-modal').modal({
            show: 'show',
            backdrop: 'static',
            keyboard: false
        });
    }
    if($('#i-new-city-modal').val()=='show'){
        $('#new-city-modal').modal({
            show: 'show',
            backdrop: 'static',
            keyboard: false
        });
    }
    if($('#i-info').val()=='show'){
        var flag_sc = 2;
        $.post('/ajax/stop_cause', {flag_sc: flag_sc}, function(data){
            if(data.length){
                var res = JSON.parse(data);
                $('#info-modal').modal({
                    show: 'show',
                });
                var t_im = "Информация";
                var text_im = res['text'];
                $('.modal-title').text(t_im);
                $('.conf-email-mb').html(text_im);
            }
        });
    }

    if($('#i-stop-order').val()=='show'){
        var flag_sc = 1;
        $.post('/ajax/stop_cause', {flag_sc: flag_sc}, function(data){
            if(data.length){
                var res = JSON.parse(data);
                $('#stop-order-modal').modal({
                    show: 'show',
                    backdrop: 'static',
                    keyboard: false
                });
                var title = "Информация";
                var txt = res['text'];
                $('.bg-wt').html(txt);
                $('.modal-title').text(title);
            }
        });
    }
    if($('#i-wt-pickup-modal').val()=='show'){
        $('#wt-pickup-modal').modal('show');
    }

    if($('#products-wrap').length){
        $(window).scroll(function(){
            var fh = $('footer').height()+105;
            var point = $(document).height()-$(window).height()-fh-100;
            if($(window).scrollTop() >= point && $('.loadmore-img').is(':hidden')){
                loadmore();
            }
        });
    }

    if($('#quiz-history-rolls').length){
        quiz_history_rolls();
    }

    if($('#quiz').length){
        quiz();
    }

    //fix for mac
    if(navigator.userAgent.indexOf('Mac') > 0){
        $('.cart-btn i.rub').addClass('macrub');
    }

    $("img.lazyload").lazyload();

    $(document).on('change', '.other_radio_style input[type=checkbox]', function() {
        other_disabled($(this));
    });

    $('#other-modal').on('hidden.bs.modal', function(){
        $('#other-modal .modal-body').html('<img src="/images/loading.gif" alt="" class="other-loading" />');
    });

    $(document).on('change', '.other_radio_style input', function() {
        if($(this).attr('type')=='radio') {
            $(this).closest('.other-cw').find('.other_radio_style').removeClass('checked');
            $(this).closest('.other-cw').find('.other_radio_style').removeClass('checked2');
        }
        other_change($(this));
        change_image($(this));
    });

    //$('#other-modal').modal('show');

    $('.tbm-close').click(function(){
        $('.tbm-wrap').addClass('none');
    });
    $('.tbm-close2').click(function(){
        $('.tbm-wrap').addClass('none');
    });
    $(document).keydown(function(eventObject){
        if( eventObject.which == 27 ){
            $('.tbm-wrap').addClass('none');
        }
    });


    $('.mkuik').on('click', function(e){
        if(!$($(this).attr('href')).length)return;
        var scrl = $($(this).attr('href')).offset().top-80;
        $('html,body').stop().animate({ scrollTop: scrl }, 600);
        e.preventDefault();
    });

    if($(window).width()>991){
        $('ul.menu').flexMenu({
            linkText: 'Еще <i class="mdi mdi-chevron-down"></i>',
            linkTitle: "Показать еще",
            linkTextAll: 'Меню  <i class="mdi mdi-chevron-down"></i>',
            showOnHover: true
        });
    }

    // файл №1
    $("#wg-file").change(function(){
        var $input = $("#wg-file");
        var fd = new FormData;

        if($('#wg-file')[0].files[0].size <= 100000000){
            fd.append('file', $input.prop('files')[0]);
            $('#wg-alert').removeClass('alert-danger').hide().html('');
            $('#wg-submit').hide();
            $('#wg-load').show();
            $('#wg-file2').attr('disabled', true);
            $('#wg-file3').attr('disabled', true);
            $.ajax({
                url: '/ajax/upload_file',
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (data) {
                    console.log(data);
                    $('#wg-submit').show();
                    $('#wg-load').hide();
                    $('#wg-file2').attr('disabled', false);
                    $('#wg-file3').attr('disabled', false);
                }
            });
        }else{
            $('#wg-alert').addClass('alert-danger').show().html('Большой файл! Файл не должен превышать 100МБ');
            $('#wg-file').val('');
        }
        //console.log($('#wg-file')[0].files[0].size);
        //return;
    });

    // файл №2
    $("#wg-file2").change(function(){
        var $input = $("#wg-file2");
        var fd = new FormData;

        if($('#wg-file2')[0].files[0].size <= 100000000){
            fd.append('file', $input.prop('files')[0]);
            $('#wg-alert').removeClass('alert-danger').hide().html('');
            $('#wg-submit').hide();
            $('#wg-load').show();
            $('#wg-file').attr('disabled', true);
            $('#wg-file3').attr('disabled', true);
            $.ajax({
                url: '/ajax/upload_file',
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (data) {
                    console.log(data);
                    $('#wg-submit').show();
                    $('#wg-load').hide();
                    $('#wg-file').attr('disabled', false);
                    $('#wg-file3').attr('disabled', false);
                }
            });
        }else{
            $('#wg-alert').addClass('alert-danger').show().html('Большой файл! Файл не должен превышать 100МБ');
            $('#wg-file2').val('');
        }
        //console.log($('#wg-file')[0].files[0].size);
        //return;
    });

    // файл №3
    $("#wg-file3").change(function(){
        var $input = $("#wg-file3");
        var fd = new FormData;

        if($('#wg-file3')[0].files[0].size <= 100000000){
            fd.append('file', $input.prop('files')[0]);
            $('#wg-alert').removeClass('alert-danger').hide().html('');
            $('#wg-submit').hide();
            $('#wg-load').show();
            $('#wg-file2').attr('disabled', true);
            $('#wg-file').attr('disabled', true);
            $.ajax({
                url: '/ajax/upload_file',
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (data) {
                    console.log(data);
                    $('#wg-submit').show();
                    $('#wg-load').hide();
                    $('#wg-file2').attr('disabled', false);
                    $('#wg-file').attr('disabled', false);
                }
            });
        }else{
            $('#wg-alert').addClass('alert-danger').show().html('Большой файл! Файл не должен превышать 100МБ');
            $('#wg-file3').val('');
        }
        //console.log($('#wg-file')[0].files[0].size);
        //return;
    });

    $('[data-toggle="tooltip"]').tooltip();

    $(document).mouseup(function (e) {
        var container = $("#other-modal .btn-red-auth");
        if (container.has(e.target).length === 0){
            container.tooltip('hide');
        }
    });

    $('.rejection_reason').focus(function(){
        $('#page-danget-hint').html('').removeClass('alert alert-danger');
    });
});

function show_tg_info(e) {
    var elem = $(e);
    var wrap = elem.closest('div');
    var block = wrap.find('.tgconfdiv');
    block.slideDown();
}

function points_info_modal() {
    $('#points-info-modal').modal('show');
}

function copytext_autin(el) {
    var $tmp = $("<input>");
    $("body").append($tmp);
    $tmp.val($(el).text()).select();
    document.execCommand("copy");
    $tmp.remove();

    var msg = $('#copy-msg');
    msg.html($(el).data('msg'));
    msg.addClass('copy-msg-show');
    setTimeout(function(){
        msg.removeClass('copy-msg-show');
    }, 1500);
}

function stb() {
    var tm = 60*60*24*2;
    document.cookie = "tbm=1; path=/; max-age=" + tm;
    $('.tbm-wrap').removeClass('none');
}

function other_disabled(inp){
    var input = $(inp);
    var wrap = input.closest('.other-cw');
    var max = Number(wrap.data('max'));
    if(max>0){
        var inputs = wrap.find('input:checked');
        var c_count = 0;
        inputs.each(function(){
            var ccnn = Number($(this).closest('.other_radio_style').find('.other-ccl span').text());
            c_count = c_count + ccnn;
        });
        if(c_count >= max){
            wrap.find('input').not(':checked').prop('disabled', true);
            wrap.addClass('disabled');
        }else{
            wrap.find('input:disabled').prop('disabled', false);
            wrap.removeClass('disabled');
        }
    }

    if(!input.is(':checked')){
        input.closest('.other_radio_style').find('.other-ccl span').html(1);
        var price = Number(input.closest('.other_radio_style').find('.other-price').data('price'));
        input.closest('.other_radio_style').find('.other-price').html('+ '+price+'<i class="rub">q</i>');
    }
}

function minusother(e){
    var btn = $(e);
    var wrap = btn.closest('.other-ccl');
    var span = wrap.find('span');
    var input = btn.closest('.other_radio_style').find('input');
    var cnt = Number(span.html());
    if(cnt > 1){
        cnt = cnt-1;
    }else{
        cnt = 1;
    }
    span.html(cnt);

    var price = Number(btn.closest('.other_radio_style').find('.other-price').data('price'));
    btn.closest('.other_radio_style').find('.other-price').html('+ '+price*cnt+'<i class="rub">q</i>');

    other_change(btn, 1);
    other_disabled(input);
}

function plusother(e){
    var btn = $(e);
    var parent = btn.closest('.other-cw');
    if(parent.hasClass('disabled'))return;
    var wrap = btn.closest('.other-ccl');
    var span = wrap.find('span');
    var input = btn.closest('.other_radio_style').find('input');
    var cnt = Number(span.html());
    cnt = cnt+1;
    span.html(cnt);

    var price = Number(btn.closest('.other_radio_style').find('.other-price').data('price'));
    btn.closest('.other_radio_style').find('.other-price').html('+ '+price*cnt+'<i class="rub">q</i>');

    other_change(btn, 1);
    other_disabled(input);
}

function other_change(input, flg){
    var wrap = $(input).closest('.other-row');
    var wrap_block = $(input).closest('.other_radio_style');
    var parrent = $(input).closest('.other-cw');
    var is_remove = parrent.data('remove');
    var all_inputs = wrap.find('input[type=checkbox]:checked, input[type=radio]:checked');
    var price = wrap.find('.other-tp span');
    var def_price = Number(wrap.find('input[name=def_price]').val());
    var input_price = wrap.find('input[name=price]');
    var input_items = wrap.find('input[name=items]');

    if(!flg){
        if(is_remove==1){
            if(wrap_block.hasClass('checked2')){
                wrap_block.removeClass('checked2');
            }else{
                wrap_block.addClass('checked2');
            }
        }else{
            if(wrap_block.hasClass('checked')){
                wrap_block.removeClass('checked');
            }else{
                wrap_block.addClass('checked');
            }
        }
    }

    //var span_cnt = wrap.find('.other-ccl span');
    //var cnt = Number(span_cnt.html());
    var cnt = 1;
    var p = 0;
    var fp = 0;
    var items = '';
    all_inputs.each(function(){
        p = Number($(this).closest('.other_radio_style').find('.other-price').data('price'));
        var count  = Number($(this).closest('.other_radio_style').find('.other-ccl span').text());
        if(!count)count=1;
        p = count*p;
        fp += p;

        if($(this).attr('name').indexOf('other_')>=0){
            if($(this).closest('.other-cw').data('remove')==1)count = '-';
            items += $(this).val()+'|'+count+',';
        }
    });
    fp = cnt*fp;
    if(!wrap.find('.property').length){
        fp = fp+def_price;
    }
    if(input.closest('.property').length){
        wrap.find('input[name=property]').val(input.val());
        var def_price = input.closest('.other_radio_style').find('.other-price').text().replace(/[^+\d]/g, '');
        wrap.find('input[name=def_price]').val(def_price);
    }


    number_animation(price, input_price.val(), fp);
    input_price.val(fp);

    if(items)items = items.substring(0, items.length - 1);
    input_items.val(items);
}

function change_cnt(elem, znak, max) {
    var wrap = $(elem).closest('.wrap');
    var input = wrap.find('input');
    var name = input.attr('name');
    var val = Number(input.val());
    if(znak=='plus'){
        val = val+1;
    }else{
        val = val-1;
    }
    if(val<1){
        val = 0;
    }
    if(max && Number(max) < val){
        var msg = 'Максимальное допустимое количество приборов для Вашего заказа: '+max+'.<br>Вы можете заказать больше приборов в меню на сайте, из раздела: Дополнительно.';
        var doplink = $('#dopolnitelno-lnk').val();
        if(doplink){
            msg = 'Максимальное допустимое количество приборов для Вашего заказа: '+max+'.<br>Вы можете заказать больше приборов в меню на сайте, из раздела: <a href="/menu/'+doplink+'/">Дополнительно</a>.';
        }
        $('.oe-person').html(msg);
        return;
    }

    input.val(val);

    if($('.oe-person').length){
        $('input[name=persons]').removeClass('error');
        $('.oe-person').html('');
    }
    $.post('/ajax/order_info', {name:name, val:val});
}

function findopen(elem) {
    $(elem).hide();
    $('.find-show').show();
    $('.tr-menu-wrap form').addClass('width-30');
    $('.tr-menu-wrap form input').focus();
    $('.tr-menu-wrap').addClass('width-70');
}

function findhide() {
    var form = $('.tr-menu-wrap form');
    if (form.hasClass("width-30")) {
        $('.find-open').show();
        $('.find-show').hide();
        form.removeClass('width-30');
        $('.tr-menu-wrap').removeClass('width-70');
    }
}

function init_gifts_ts(){
    if(!$('#gl-samovivoz').length)return;
    const slider_init_gifts_ts = tns({
        container: '#gl-samovivoz',
        loop: true,
        items: 2,
        slideBy: 1,
        nav: false,
        autoplay: true,
        speed: 400,
        autoplayButtonOutput: false,
        mouseDrag: true,
        gutter: 15,
        responsive: {
            640: {
                items: 2,
            },
            768: {
                items: 2,
            },
            1200: {
                items: 3,
            }
        }

    });
}

function init_gifts_dr(){
    if(!$('#gl-dr').length)return;
    const slider_init_gifts_dr = tns({
        container: '#gl-dr',
        loop: true,
        items: 2,
        slideBy: 1,
        nav: false,
        autoplay: true,
        speed: 400,
        autoplayButtonOutput: false,
        mouseDrag: true,
        gutter: 15,
        responsive: {
            640: {
                items: 2,
            },
            768: {
                items: 2,
            },
            1200: {
                items: 3,
            }
        }

    });
}

function copyToClipboard(elem) {
    // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;
    if (isInput) {
        // can just use the original source element for the selection and copy
        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = window.pageYOffset+"px";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.textContent;
    }
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);

    // copy the selection
    var succeed;
    try {
        succeed = document.execCommand("copy");
    } catch(e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }

    if (isInput) {
        // restore prior selection
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }
    return succeed;
}

function add_gift(id, type) {
    var gift = $('#'+type+'-'+id);
    var wrap = gift.closest('.gifts-list');
    var ch_cart = $('#ch-cart-wrap');
    var cart = $('#cart-products');
    wrap.find('.add-gift').attr('disabled', true);

    $.post("/ajax/add_gift", {id: id, type: type}, function(data){
        if(data.result=='success'){
            console.log(data);
            if(data.others){
                $('#other-modal').modal('show');
                $.post('/ajax/other_modal_gift', {id:data.others_array, type:type}, function(data) {
                    $('#other-modal .modal-body').html(data);
                });
            }

            ch_cart.html(data.ch_cart);
            cart.html(data.cart);
            number_animation($('#cart-total-count'), $('#cart-total-count').data('value'), data.header_cart_total_price);
            $('#cart-total-count').data('value', data.header_cart_total_price).attr('data-value', data.header_cart_total_price);
            $('#xs-cart-count').html(data.cart_count);
        }else{
            //alert(data.result);
            alert('Ошибка сервера');
        }
    }, "JSON");
}

// неуверен, что эта функция используется
function remove_gift(i, type) {
    var gift = $('#cart-gift-'+i);
    var wrap = $('#ch-cart-gift-wrap');

    if($('#ch-cart-gift-wrap section').length == 1) {
        wrap.slideUp(function () {
            gift.remove();
        });
    }else{
        gift.slideUp(function () {
            gift.remove();
        });
    }

    if(type == 'dr'){
        $('.add-gift').attr('disabled', false);
    }

    $.post("/ajax/remove_gift", {i: i});
}
// неуверен, что эта функция используется

function remove_gifts(type, s) {
    var t = '';

    $('#cart-products section').each(function(){
        t = $(this).data('property');
        if(t == type){
            var gft = $('#ch_'+$(this).attr('id'));
            var gft_autoadd = $(this).attr('data-autoadd');
            if(gft_autoadd.length){
                var arr_gft_auto_id = gft_autoadd.split(",");
                $.each(arr_gft_auto_id, function(index, value){
                    var gft_auto_id = value.split("|");
                    var gfta_id = gft_auto_id[0].replace('_', '__');
                    var auto_add_id = '';
                    $('#cart-products section').each(function(){
                        auto_add_id = $(this).attr('id');
                        auto_add_id = auto_add_id.replace("ch_", "");
                        if(auto_add_id == gfta_id){
                            var aa_ch_section = $('#ch_'+auto_add_id);
                            var aa_section = $('#'+auto_add_id);
                            var aa_oldcount = Number(aa_section.find('.cart-cnt-line span').html());
                            var aa_newcount = aa_oldcount - Number(gft_auto_id[1]);
                            if(aa_newcount == 0){
                                aa_ch_section.remove();
                            }else{
                                aa_section.find('.cart-cnt-line span').html(aa_newcount);
                                aa_ch_section.find('.cart-cnt-line span').html(aa_newcount);
                            }
                        }
                    });
                });
            }
            if(gft.length){
                gft.slideUp(function () {
                    gft.remove();
                });
            }
            $(this).remove();
        }
    });
    $('.type_'+type+' .add-gift').prop('disabled', false);
    $.post("/ajax/remove_"+type, {check: s});
}

function show_gifts(type="") {
    $('#gifts-wrap').slideDown();
    event.preventDefault();

    var total_price = $('#ch-cart-total-price');
    console.log(total_price);
    var dr_date = '';
    if(type=='dr'){
        dr_date = $('#client-dr').val();
    }

    if(type){
        $.post('/ajax/change_gifts_type', {type:type, dr_date:dr_date}, function(d) {
            // удалям баллы, если есть
            var points = Number($('#g-points').val());
            if(points > 0 && type == 'dr'){
                var wrap = $('#ch-d-points');
                var error = wrap.find('.error');
                var pay_points = wrap.find('.ch-pay-points');
                var next_block = $('.ch-next');
                var total_price = $('#ch-cart-total-price');
                var head_total_price = $('#cart-total-price');
                var new_price = Number(total_price.data('price')) + points;

                number_animation(total_price, total_price.data('price'), new_price);
                total_price.data('price', new_price).attr('data-price', new_price);
                head_total_price.data('discount-price', new_price).attr('data-discount-price', new_price);
                $('#g-points').val(0);

                wrap.find('div').show();
                wrap.removeClass('block_important');
                pay_points.hide().removeClass('block_important');
                error.html('');
                next_block.find('small.points_span').removeClass('block_important');
                next_block.find('small.points_span em').html('');
            }
            // удалям баллы, если есть

            //if($('#gifts-wrap').is(':visible')){

                /*var wrap = $('#ch-wrap');
                var url = wrap.data('url');
                wrap.addClass('opacity');
                var load = $('#d-loading');
                load.show();
                $.post('/ajax/'+url, function(d) {
                    wrap.html(d);
                    load.hide();
                    wrap.removeClass('opacity');
                });*/
            //}
        });
    }
}

function zebra_cho_run(){
    if($('#time-limit').length){
        var timelimit = $('#time-limit').val().split(',');
        var timelimit3 = $('#time-limit3').val().split(',');
        var this_h_t = timelimit[0];
        var last_time = timelimit[timelimit.length-1];
        var this_h = timelimit3[0];
        //console.log(this_h_t);
        //console.log(last_time);
        //console.log(this_h);
        var tlar = [];
        var mlar = [];
        timelimit3.shift();
        if(this_h_t != this_h){
            timelimit.shift();
        }
        $.each(timelimit,function(index,value){
            tlar.push(Number(value));
        });
        $.each(timelimit3,function(index,value){
            mlar.push(Number(value));
        });
        //console.log(tlar);
        //console.log(mlar);
        var timelimit2 = $('#time-limit2').val().split(',');
        var tlar2 = [];
        $.each(timelimit2,function(index,value){
            tlar2.push(Number(value));
        });
        var mlar2 = [0,5,10,15,20,25,30,35,40,45,50,55];
        var mlar3 = [0];
    }else{
        var tlar = [0,10,11,12,13,14,15,16,17,18,19,20,21,22,23];
        var tlar2 = [0,10,11,12,13,14,15,16,17,18,19,20,21,22,23];
        var mlar = [0,5,10,15,20,25,30,35,40,45,50,55];
        var mlar2 = [0,5,10,15,20,25,30,35,40,45,50,55];
        var mlar3 = [0];
    }

    $('#zebra-cho').Zebra_DatePicker({
        format: 'd.m.Y, H:i',
        direction: [true, 1],
        select_other_months: true,
        enabled_hours: tlar,
        enabled_minutes: mlar,
        show_icon: false,
        container: $('.zebra-wrap'),
        days: ['ВС', 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'],
        months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        show_select_today: 'Сегодня',
        lang_clear_date: 'Очистить',
        onChange: function() {
            var name = 'cook_time_value';
            var val = $(this).val();
            var dpd = $('#zebra-cho').data('pick-date');
            var da = new Date().toLocaleString();
            var d = da.split(',');
            // выбранная дата
            var ta = val.split(',');
            if(ta[1]){
                var tm = ta[1].split(':');
                var val_time = tm[0].trim();
                if(Number(val_time) == last_time){
                    var mnt = mlar3;
                }else{
                    var mnt = mlar2;
                    if(val_time == this_h && ta[0] == d[0]){
                        var mnt = mlar;
                    }
                    if(val_time != this_h && ta[0] == d[0]){
                        var mnt = mlar2;
                    }
                }
                $('#zebra-cho').data('Zebra_DatePicker').update({
                    enabled_minutes: mnt,
                });
            }
            // если езминился день, обновляем часы
            if(ta[0] != dpd){
                // сегодня
                //var da = new Date().toLocaleString();
                //var d = da.split(',');
                if(d[0] == ta[0]){
                    var hrs = tlar;
                    if(val_time == 23){
                        var mnt = mlar3;
                    }else{
                        if(val_time == this_h){
                            var mnt = mlar;
                        }
                        if(val_time != this_h){
                            var mnt = mlar2;
                        }
                    }
                }else{
                    var hrs = tlar2;
                    if(val_time == 23){
                        var mnt = mlar3;
                    }else{
                        var mnt = mlar2;
                    }
                }
                $('#zebra-cho').data('Zebra_DatePicker').update({
                    enabled_hours: hrs,
                    enabled_minutes: mnt,
                });

                var sd = ta[0]+', '+hrs[0]+':'+mnt[0];
                $('#zebra-cho').data('Zebra_DatePicker').set_date(sd);
                val = sd;
                //console.log(sd);
            }
            $('#zebra-cho').data('pick-date', ta[0]).attr('data-pick-date', ta[0]);
            $.post('/ajax/order_info', {name:name, val:val});
        },
        onClear: function() {
            var name = 'cook_time_value';
            var val = '';

            $.post('/ajax/order_info', {name:name, val:val});
        },
        onClose: function() {
            var name = 'cook_time_value';
            var mnt = $('.dp_time_minutes div').html();
            var hrs = $('.dp_time_hours div').html();
            var ttime = $(this).val();
            var time_pick = ttime.split(',');
            var end_time = time_pick[0]+', '+hrs+':'+mnt;
            if(end_time){
                $(this).val(end_time);
            }
            $.post('/ajax/order_info', {name:name, val:$(this).val()});
            if(time_pick[1] === ' 23:00'){
                $('#info-modal').modal('show');
                var title = "Информация";
                var txt = 'В связи с повышением мер безопасности по нераспространению коронавирусной инфекции, заказы на самовывоз осуществляется до 23:00. Необходимо забрать ваш заказ до этого времени.';
                $('.conf-email-mb').text(txt);
                $('.modal-title').text(title);
            }

            $.post('/ajax/check_time_order', {time:ttime}, function(data){
                if(data == 'error'){
                    $('#info-modal').modal('show');
                    var title = "Внимание";
                    var txt = 'На '+ttime+' оформлено много заказов. Пожалуйста укажите другую дату или время.';
                    $('.conf-email-mb').text(txt);
                    $('.modal-title').text(title);
                    $('#zebra-cho').val('');
                }
            });
        }
    });
}

function ra_order_date_run(){
    if($('#ra_order_date').length){
        $('#ra_order_date').Zebra_DatePicker({
            format: 'd.m.Y',
            show_icon: false,
            container: $('.ra-zebra-wrap'),
            days: ['ВС', 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'],
            months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            show_select_today: 'Сегодня',
            lang_clear_date: 'Очистить',
        });
    }
}

function run_order(elem, first=0) {
    var $btn = $(elem);
    var $btn_wrap = $btn.closest('.cho-rules');
    var $load = $btn_wrap.find('.cho-loading');

    $btn.hide();
    $load.show();
    $('#order-error').hide();

    // собираем массив данных
    var $info = {};
    $info['error'] = 0;
    $info['name'] = $('#order-name').val();
    $info['phone'] = $('#order-phone').val();
    $info['pay'] = $('#cho-pay .hl-prices li.active').html();
    $info['pay_type'] = $('#cho-pay .hl-prices li.active').attr('id');
    $info['comment'] = $('#cho-comment').val();
    $info['banknote'] = $('input[name="banknote"]').val();
    $info['persons'] = $('input[name="persons"]').val();
    // подтверждение заказа звонком/смс
    $info['confirmation'] = $('#confirmation').val();
    // id клиента в яндекс метрике
    $info['clientID'] = $('#clientID').val();

    if(!$info['phone']){
        $info['error'] = 'phone';
    }
    if($info['phone'].indexOf('+7 (8') >= 0){
        $info['error'] = 'phone_eight';
    }
    if(!$info['name']){
        $info['error'] = 'fio';
    }

    // время приготовления
    if($('#cho-pay .cook_time li.active').data('id')=='now'){
        $info['cook_time'] = 'now';
    }else{
        var $ztime = $('#select_cook_time').val();
        //var $ztime = $('#zebra-cho').val();
        if(!$ztime){
            $info['error'] = 'ztime';
        }else{
            $info['cook_time'] = $ztime;
        }
    }
    // доставка и соответствующие поля
    //$info['delivery_type'] = $('#change-delivery-btns li.active').data('type');
    $info['delivery_type'] = 2;
    if($info['delivery_type']==1){
        // самовывоз
        $info['office'] = $(".offices-list input[name=office]:checked").closest('h4').find('label').html();
        $info['office_id'] = $(".offices-list input[name=office]:checked").val();
    }else{
        // доставка
        $info['house_marker'] = $("#address-house-marker").val();
        $info['room_number'] = $("#address-room-number").val();
        $info['floor'] = $("#address-floor").val();
        $info['entrance'] = $("#address-entrance").val();
        $info['address_title'] = $("#address-title").val();

        $info['is_private'] = 0;
        if($("#is_private").is(':checked'))$info['is_private'] = 1;

        $info['is_not_intercom'] = 0;
        if($("#is_not_intercom").is(':checked'))$info['is_not_intercom'] = 1;
    }
    //console.log($info);

    if(!$info['error']){
        if(!first){
            $('#co-tg-hint').addClass('none');
            $('#co-hint').removeClass('none');
            $('#order-conf-link').removeClass('none');
            $('#order-other-conf-link').addClass('none');
        }else{
            $('#co-tg-hint').removeClass('none');
            $('#co-hint').addClass('none');
            $('#order-conf-link').addClass('none');
            $('#order-other-conf-link').removeClass('none');
        }
        // финальная проверка заказа на ошибки
        $.post("/ajax/check_order?first="+first, {info: JSON.stringify($info)}, function(data){
            if(data.result=='success'){
                if($info['pay_type']=='pay_3'){
                    window.location.href = "/checkout/online_pay?order_id="+data.order_id+'&ac='+data.ac;
                }else{
                    window.location.href = "/checkout/finish?order_id="+data.order_id+'&ac='+data.ac;
                }
                return;
            }else if(data.result=='confirmation'){
                $('#co-resend-hint').html('').hide();
                if(data.text && !first){
                    $('#co-resend-hint').html(data.text).show();
                }
                $('#order-confirmation-modal').modal('show');
                $btn.show();
                $load.hide();
            }else{
                show_order_error(data.error_text, data.t);
                $btn.show();
                $load.hide();
            }
            //console.log(data);
        }, "json");

    }else{
        var text = '';
        if($info['error']=='ztime'){
            text = 'Необходимо указать дату и время приготовления';
        }
        if($info['error']=='fio'){
            text = 'Необходимо указать, как к Вам обращаться';
        }
        if($info['error']=='phone'){
            text = 'Необходимо указать контактный телефон';
        }
        if($info['error']=='phone_eight'){
            text = 'Номер телефона указан неверно';
        }
        show_order_error(text, $info['error']);
        $btn.show();
        $load.hide();
    }
}

function show_order_error(text, t){
    $('input[name=persons]').removeClass('error');
    $('.oehint').html('');

    $('#order-error').html(text).show();
    if(t=='person'){
        $('input[name=persons]').addClass('error');
        $('.oe-person').html(text);
        if($( window ).width()<991){
            $('html,body').stop().animate({ scrollTop: $('input[name=persons]').offset().top-100 }, 800);
        }
    }else{
        $('html,body').stop().animate({ scrollTop: $('#scroll').offset().top }, 800);
    }
}

function other_address(elem) {
    var btn = $(elem);
    var al = btn.closest('.adresses_list');
    var wrap = btn.closest('.cho-row');
    var form = wrap.find('.cho-aform');

    $('.btn-order').prop('disabled', true);
    al.hide();
    form.show();
}

function my_address(elem) {
    var btn = $(elem);
    var wrap = btn.closest('.cho-row');
    var al = wrap.find('.adresses_list');
    var form = wrap.find('.cho-aform');

    al.show();
    form.hide();
    change_order_address($(".change-order-address input[name=adress]:checked").val());
}

function change_order_address(id){
    //console.log(id);
    var hint = $('.adresses_list .hint');
    hint.html('').removeClass('error').hide();

    var load = $('.adresses_list .address-loading');

    // для страницы оформления заказа
    var order_wrap = $('#cho-wrap');
    var delivery_price = $('#cho-delivery-price');
    var btn_order = $('.btn-order');
    var order_price = $('.cho-receipt-itogo span.sum');
    var pay_block = $('#cho-pay');
    var receipt_block = $('#cho-receipt');
    var price = Number(order_price.data('discount-price'));
    // флаг, что действия на странице заказа
    var order_flag = 0;
    if(delivery_price.length){
        order_flag = 1;
    }


    load.show();
    btn_order.prop('disabled', true);

    $.post("/ajax/check_zone", {address_id: id, order_flag: order_flag}, function(data){
        //console.log(data);
        if(data.result=='zone_error'){
            // пишем ошибку, не входит в зону
            hint.show().addClass('error').html('К сожалению, этот адрес не найден в нашей зоне доставки. Пожалуйста укажите другой адрес.');
            if(order_flag){
                // ставим метку, что зона не определена
                order_wrap.data('zone', 0).attr('data-zone', 0);
                // ставим кнопку disabled
                $('#delivery-time').hide();
                delivery_price.html('-');
                // общая сумма заказа
                number_animation(order_price.find('em'), order_price.data('order-price'), price);
                delivery_price.data('order-price', price).attr('data-order-price', price);
            }
        }else{
            // пишем инфо о зоне
            hint.show().html(data.f_title);
            if(order_flag){
                // ставим метку, что зона определена
                order_wrap.data('zone', 1).attr('data-zone', 1);
                // считаем стоимость доставки (возможно бесплатно)
                var d_price = Number(data.price);
                var free = Number(data.free);
                if(free <= price){
                    // стоимоть доставки в чеке
                    delivery_price.html('<em style="color:#00a517;">бесплатно</em>');
                    delivery_price.data('delivery-price', 0).attr('data-delivery-price', 0);
                    d_price = 0;
                }else{
                    // стоимоть доставки в чеке
                    delivery_price.html('<em>'+delivery_price.data('delivery-price')+'</em><i class="rub">q</i>');
                    number_animation(delivery_price.find('em'), delivery_price.data('delivery-price'), d_price);
                    delivery_price.data('delivery-price', d_price).attr('data-delivery-price', d_price);
                }
                // общая сумма заказа
                var new_price = price + d_price;
                number_animation(order_price.find('em'), order_price.data('order-price'), new_price);
                order_price.data('order-price', new_price).attr('data-order-price', new_price);

                // убираем прозрачность
                receipt_block.removeClass('opacity__');
                pay_block.removeClass('opacity__');

                // обновляем время при оформлении заказа ко времени
                update_time_zome();

                // меняем среднее времядоставки (если есть)
                if(data.time){
                    $('#delivery-time').show();
                    if(data.time2){
                        console.log(data.time2);
                        $('#delivery-time em').html(data.time2+' мин.');
                    }else{
                        if(data.time3){
                            console.log(data.time3);
                            $('#delivery-time em').html(data.time3+' мин.');
                        }else{
                            console.log(data.time);
                            $('#delivery-time em').html(data.time+' мин.');
                        }
                    }
                }else{
                    $('#delivery-time').hide();
                }

                // проверяем минимальную сумму заказа
                if(Number(data.min_order) > price){
                    order_wrap.data('min-sum', 0).attr('data-min-sum', 0);
                    $('#order-error').html('Минимальная сумма заказа, для Вашего адреса (без учета стоимости доставки): '+data.min_order+' руб.').show();
                    btn_order.prop('disabled', true);
                    $('html,body').stop().animate({ scrollTop: $('#scroll').offset().top }, 800);
                }else{
                    order_wrap.data('min-sum', 1).attr('data-min-sum', 1);
                    $('#order-error').html('').hide();
                }

                // если выполнены все условия, убираем disabled
                if(order_wrap.data('min-sum') && order_wrap.data('zone') && order_wrap.data('rules')){
                    btn_order.prop('disabled', false);
                }
            }

        }
        load.hide();
    }, "json");
}

$(document).on('click','.cho-ul-select li',function(e){
    var ul = $(this).closest('ul');
    var li = ul.find('li');

    li.removeClass('active');
    $(this).addClass('active');
    if($(this).data('show')){
        $(this).closest('.cho-inp-line').addClass('show_hwrap');
    }else{
        $(this).closest('.cho-inp-line').removeClass('show_hwrap');
    }

    var name = 'cook_time';
    var val = $(this).data('id');

    $.post('/ajax/order_info', {name:name, val:val});
});

$(document).on('click','.filtr .ttl',function(e){
    $('.filtr-block').toggle();
});

function run_filtr(elem){
    var btn = $(elem);
    var wrap = btn.closest('.filtr-block');
    var like = wrap.find('li.like');
    var not = wrap.find('li.not');
    var load = $('#filter-load');

    var query = '';
    if(like.length || not.length){
        query = '?';
        $('.filtr .ttl').addClass('active');
    }else{
        $('.filtr .ttl').removeClass('active');
    }
    if(like.length){
        query += 'like=;';
        $(like).each(function(i){
            query += $(this).data('id')+';';
        });
    }
    if(like.length && not.length){
        query += '&';
    }
    if(not.length){
        query += 'not=;';
        $(not).each(function(i){
            query += $(this).data('id')+';';
        });
    }

    if(history.pushState){
        // обновляем url
        var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        var newUrl = baseUrl + query;
        history.pushState(null, null, newUrl);
    }

    var w = $('#products-wrap');
    var murl = window.location.pathname.substr(1);
    w.data('url', murl + query).attr('data-url', murl + query);
    var url = w.data('url');
    w.addClass('opacity');
    load.show();

    $.post('/ajax/'+url, function(d) {
        w.html(d).removeClass('opacity');
        load.hide();
        wrap.hide();
        $('.subcategories').addClass('none');
    });

}

function clear_filtr(elem){
    var btn = $(elem);
    var wrap = btn.closest('.filtr-block');
    var li  = wrap.find('li');
    var load = $('#filter-load');

    $(li).each(function(i){
        $(this).removeClass('not').removeClass('like');
    });

    if(history.pushState){
        // обновляем url
        var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        history.pushState(null, null, baseUrl);
    }

    var w = $('#products-wrap');
    var murl = window.location.pathname.substr(1);
    w.data('url', murl).attr('data-url', murl);
    var url = w.data('url');
    w.addClass('opacity');
    load.show();

    $.post('/ajax/'+url, function(d) {
        w.html(d).removeClass('opacity');
        load.hide();
        wrap.hide();
        $('.filtr .ttl').removeClass('active');
        $('.subcategories').removeClass('none');
    });

    event.preventDefault();
}

$(document).on('click','.filtr-block li i.mdi-close, .filtr-block li span',function(e){
    var li = $(this).closest('li');
    if(li.hasClass($(this).data('type'))){
        li.removeClass('not').removeClass('like');
    }else{
        li.removeClass('not').removeClass('like');
        li.addClass($(this).data('type'));
    }
});

$(document).on('click','.property_list label',function(e){
    property_click($(this).attr('for'));
});

$(document).on('change','.save_info',function(e){
    var name = $(this).attr('name');
    var val = $(this).val();

    $.post('/ajax/order_info', {name:name, val:val});
});

$(document).on('change','#cho-rules',function(e){
    if($(this).is(":checked")) {
        var $deliv = $('#change-delivery-btns li.active').data('type');
        if($('#cho-wrap').data('zone') && $('#cho-wrap').data('min-sum') || $deliv == 1){
            $('.btn-order').prop('disabled', false);
            $('#cho-wrap').data('rules', 1).attr('data-rules', 1);
        }
    }else{
        $('.btn-order').prop('disabled', true);
        $('#cho-wrap').data('rules', 0).attr('data-rules', 0);
    }
});

$(document).on('change','#is_private',function(e){
    if($(this).is(":checked")) {
        $('.non_private').slideUp();
    }else{
        $('.non_private').slideDown();
    }
});

$(document).on('change','#is_private_cab',function(e){
    if($(this).is(":checked")) {
        $('.non_private_cab').slideUp();
        $('.non_private_cab').find('input').removeClass('required');
    }else{
        $('.non_private_cab').slideDown();
        $('.non_private_cab').find('input').addClass('required');
    }
});

$(document).on('click','.cho-pay .hl-prices li',function(e){
    var ul = $(this).closest('ul');
    var li = ul.find('li');
    var pay = $(this).data('pay');

    li.removeClass('active');
    $(this).addClass('active');
    if(pay=='online'){
        $('.btn-order').html('Оплатить');
    }else{
        $('.btn-order').html('Заказать');
    }

    var name = 'pay';
    var val = $(this).attr('id');
    var sum = Number($('#price-total').attr('data-discount-price'));
    console.log(sum);

    if(val!='pay_1'){
        $('#banknote-block').hide();
        $('#banknote-block input').prop('disabled', true);
        if($('#is_cash').val() == 1 && sum >= $('#min-gift-cash').val()){
            $('#del-cash-gift').modal({
                show: 'show',
                backdrop: 'static',
                keyboard: false
            });
        }
    }else{
        $('#banknote-block').show();
        $('#banknote-block input').prop('disabled', false);
        if($('#is_cash').val() == 1 && sum >= $('#min-gift-cash').val()){
            add_gift_cash('add', 'pay_1');
        }
    }

    $.post('/ajax/order_info', {name:name, val:val});
});

function run_points(elem, flg) {
    var wrap = $('#ch-d-points');
    var input = wrap.find('input');
    if(flg){
        // если баллы используются из модалки, устанавливаем в инпут новое значение
        var new_points = $(elem).find('span').text();
        input.val(new_points);
    }
    var error = wrap.find('.error');
    var pay_points = wrap.find('.ch-pay-points');
    var total_price = $('#ch-cart-total-price');
    var head_total_price = $('#cart-total-price');
    var next_block = $('.ch-next');
    var check_total_price = Number($('#cart-total-price').data('price'));
    var points_sum = Number(input.val());
    var user_points = Number($('#g-user-points').val());
    var discount_promo = Number($('#g-promo-discount').val());

    var load = wrap.find('.checkout-load');
    load.show();

    // js проверки
    if(!input.val() || Number(input.val())<1){
        load.hide();
        input.val('');
        //console.log('344');
        return;
    }
    if(input.data('quest')==1){
        error.html('пожалуйста <a href="#auth-modal" data-toggle="modal">авторизуйтесь</a>');
        load.hide();
        input.val('');
        return;
    }
    if($('#g-promo-is-not-points').val()==1){
        error.html('не сочетается с промокодом: '+$('.ch-promocode em').html().toUpperCase()+'<br>(удалите промкод чтобы списать баллы)');
        load.hide();
        input.val('');
        return;
    }


    var temp = (check_total_price-discount_promo)/2;
    //console.log(discount_promo);
    if(points_sum > temp){
        error.html('максимум 50% от суммы заказа');
        load.hide();
        input.val('');
        return;
    }
    if(points_sum > user_points){
        error.html('недостаточно баллов');
        load.hide();
        input.val('');
        return;
    }

    // если есть минималка на промокод, то показываем предупреждение
    var promo_min_sum = Number($('body').data('promo-min-sum'));

    // учитывать скидку по промокоду при выявлении максимального количества возможно/потраченных баллов
    // var check_total_price = check_total_price - discount_promo;

    // отключаем проверку на минимальную сумму при добавлении промокода
    // if(promo_min_sum > check_total_price-points_sum && !flg){
    //     var max_points_sum = check_total_price - promo_min_sum;
    //     $('#del-promo-action-name').html('Использование баллов');
    //     $('#del-promo-btns').addClass('none');
    //     $('#del-promo-btns2').removeClass('none');
    //     if(max_points_sum>0){
    //         $('#del-promo-dop-text').html('<br>Чтобы сохранить промокод, вы можете использовать максимум '+max_points_sum+' балл(а/ов).').show();
    //         $('#del-promo-btns2 .recomended').html(max_points_sum);
    //         $('#del-promo-btns2 .fact').html(points_sum);
    //     }else{
    //         $('#del-promo-btns2 button:eq(1)').addClass('none');
    //         $('#del-promo-btns2 button:eq(2)').html('Подтвердить <span class="none">'+points_sum+'</span>');
    //     }
    //
    //     $('#del-promo-modal').modal('show');
    //
    //     input.val('');
    //     load.hide();
    //     return;
    // }

    // информация об удалении подарков за ДР
    var gift_check = $('#cart-products section[data-property="gift_dr"]');
    if(gift_check.length && !flg){
        $('#del-dr-gifts-info button.btn-red-auth span').html(points_sum);
        $('#del-dr-gifts-info').modal('show');
        input.val('');
        load.hide();
        return;
    }
    // информация об удалении подарков за ДР

    if(flg) {
        $('#del-promo-modal').modal('hide');
        $('#del-dr-gifts-info').modal('hide');
    }

    $.post('/ajax/run_points', {points:input.val()}, function(data) {
        //console.log(data);
        if(data.result=='error'){
            error.html(data.text);
        }else{
            wrap.find('div').hide();
            wrap.addClass('block_important');
            pay_points.html('<em id="points-em" data-points="'+input.val()+'">'+input.val()+'</em><i class="rub">q</i><i class="mdi mdi-close" title="Отменить" onclick="cancel_points(this);"></i>').show();

            var new_price = Number(total_price.data('price')) - input.val();
            number_animation(total_price, total_price.data('price'), new_price);
            total_price.data('price', new_price).attr('data-price', new_price);
            head_total_price.data('discount-price', new_price).attr('data-discount-price', new_price);

            next_block.find('small.points_span em').html(input.val());
            next_block.find('small.points_span').addClass('block_important');
            $('#g-points').val(input.val());

            // удаляем подарки за др, если есть
            $('.type_gift_dr').slideUp();
            remove_gifts('gift_dr', 'sclear');
            // удаляем подарки за др, если есть

            if(flg){
                // нужно ли удалять промокод
                var picked_class = $(elem).find('span').attr('class');
                if(picked_class == 'fact'){
                    cancel_promo();
                }
            }
        }
        input.val('');
        load.hide();

        // добавление/удаление подарка за оплату наличными
        add_gift_cash('add_points', '');

        // показать/скрыть подарки за самовывоз
        toggle_gift_pickup();

    }, "json");

}

function cancel_points(elem) {
    var wrap = $(elem).closest('.ch-d');
    var error = wrap.find('.error');
    var pay_points = wrap.find('.ch-pay-points');
    var total_price = $('#ch-cart-total-price');
    var head_total_price = $('#cart-total-price');
    var next_block = $('.ch-next');

    $.post('/ajax/run_points', {points:0});

    wrap.find('div').show();
    wrap.removeClass('block_important');
    pay_points.hide().removeClass('block_important');
    error.html('');

    //console.log(Number(total_price.data('price')));
    //console.log(Number(total_price.html()));

    var new_price = Number(total_price.data('price')) + Number(next_block.find('small.points_span em').html());
    number_animation(total_price, total_price.data('price'), new_price);
    total_price.data('price', new_price).attr('data-price', new_price);
    head_total_price.data('discount-price', new_price).attr('data-discount-price', new_price);

    next_block.find('small.points_span').removeClass('block_important');
    next_block.find('small.points_span em').html('');
    $('#g-points').val(0);

    // добавление/удаление подарка за оплату наличными
    add_gift_cash('del_points', '');

    // показать/скрыть подарки за самовывоз
    toggle_gift_pickup();
}

function run_promo(){
    var wrap = $('#run-promo-button').closest('.ch-d');
    var input = wrap.find('input');
    var error = wrap.find('.error');
    var promo = wrap.find('.ch-promocode');
    var total_price = $('#ch-cart-total-price');
    var head_total_price = $('#cart-total-price');
    var next_block = $('.ch-next');
    var ch_cart_wrap = $('#ch-cart-wrap');
    var cart_wrap = $('#cart-products');
    var points = Number($('#g-points').val());
    var birthday = ($('#g-promo-birthday').val());

    var load = wrap.find('.checkout-load');
    load.show();

    // js проверки
    if(!input.val()){
        load.hide();
        input.val('');
        return;
    }

    $.post('/ajax/run_promo', {promo:input.val()}, function(data) {
        console.log(data);
        if(data.result=='error'){
            error.html(data.text);
        }else{
            // если нужно показать модалку о несочетаемости
            //if(data.show_modal){
            //    $('#promo_not_points').modal('show');
            //}

            wrap.find('div').hide();
            wrap.addClass('block_important');
            promo.html('<em>'+input.val()+'</em><i id="cancel-promo" class="mdi mdi-close" title="Отменить" onclick="cancel_promo(this);"></i>').show();
            $('body').data('promo-min-sum', data.min_sum).attr('data-promo-min-sum', data.min_sum);
            $('body').data('promo-delivery', data.delivery).attr('data-promo-delivery', data.delivery);
            $('body').data('promo-type', data.type).attr('data-promo-type', data.type);

            $('#g-promo-type').val(data.type);
            $('#g-promo-discount-type').val(data.discount_type);
            $('#g-promo-required-products').val(data.required_products);
            $('#g-promo-week-days').val(data.week_days);
            $('#g-promo-is-not-points').val(data.is_not_points);
            $('#g-promo-is-not-dr').val(data.is_not_dr);
            $('#g-promo-is-not-delivery').val(data.is_not_delivery);
            $('#g-promo-exceptions-dates').val(data.exceptions_dates);
            $('#g-promo-birthday').val(data.birthday);

            if(data.delivery!=0 && data.delivery){
                // если промокод доступен только на самовывоз или доставку, добавляем текст в модалку
                var prtg = 'самовывоз';
                if(data.delivery==2){
                    prtg = 'доставку';
                }
                $('#change_delivery div.mctxctx').append('<p class="clear_promo_text"><u>Промокод <strong class="text-uppercase">'+data.title+'</strong> будет удален</u>. Этот промокод доступен только на '+prtg+'</p>');
            }

            $('.modal-title-promo').html(data.title);
            $('#modal-minsum-promo').html(data.min_sum);

            //день рождение
            if(data.birthday == 1){
                drbtn();
            }

            // если промокод - подарок
            if(data.type==3){
                if(data.others){
                    $('#other-modal').modal('show');
                    $.post('/ajax/other_modal_gift', {id:data.others_array}, function(data) {
                        $('#other-modal .modal-body').html(data);
                    });
                }

                ch_cart_wrap.html(data.ch_cart);
                cart_wrap.html(data.cart);
                // меняем количество в шапке корзине
                number_animation($('#cart-total-count'), $('#cart-total-count').data('value'), data.header_cart_total_price);
                $('#cart-total-count').data('value', data.header_cart_total_price).attr('data-value', data.header_cart_total_price);
                $('#xs-cart-count').html(data.total_count);
            }

            // если промокод - скидка на весь заказ руб.
            if(data.type==1 && data.discount_type==0){
                next_block.find('small.promo_span em').html(data.value);
                // отображение скидки от промокода внизу страницы
                next_block.find('small.promo_span').addClass('block_important');
                $('#g-promo-discount').val(data.value);

                // пересчитываем баллы, если необходимо
                var points = Number($('#g-points').val());
                if(points){
                    var no_points_price = Number(head_total_price.data('price')) - Number(data.value);
                    var temp = no_points_price/2;
                    if(temp < points){
                        var new_points = Math.floor(temp);
                        number_animation($('#points-em'), points, new_points);
                        number_animation(next_block.find('small.points_span em'), points, new_points);
                        $('#g-points').val(new_points);
                        points = new_points;
                    }
                }

                var new_price = Number(head_total_price.data('price')) - Number(data.value) - points;
                number_animation(total_price, total_price.data('price'), new_price);
                total_price.data('price', new_price).attr('data-price', new_price);
                head_total_price.data('discount-price', new_price).attr('data-discount-price', new_price);
            }

            // если промокод - скидка на определенные товары руб.
            if(data.type==1 && data.discount_type==1){

                ch_cart_wrap.html(data.ch_cart);
                cart_wrap.html(data.cart);

                next_block.find('small.promo_span em').html(data.products_discount);
                // отображение скидки от промокода внизу страницы
                // next_block.find('small.promo_span').addClass('block_important');
                $('#g-promo-discount').val(data.products_discount);
                $('#g-promo-products').val(data.products);
                $('#g-promo-products-limit').val(data.products_limit);
                $('#g-promo-products-group').val(data.products_group);

                // пересчитываем баллы, если необходимо
                var points = Number($('#g-points').val());
                var new_points = Number($('#g-points').val());
                if(points){
                    var no_points_price = Number(head_total_price.data('price')) - Number(data.products_discount);
                    var temp = no_points_price/2;
                    if(temp < points){
                        var new_points = Math.floor(temp);
                        number_animation($('#points-em'), points, new_points);
                        number_animation(next_block.find('small.points_span em'), points, new_points);
                        $('#g-points').val(new_points);
                    }
                }

                var new_price = Number(head_total_price.data('price')) - Number(data.products_discount) - new_points;
                number_animation(total_price, total_price.data('price'), new_price);
                total_price.data('price', new_price).attr('data-price', new_price);
                head_total_price.data('discount-price', new_price).attr('data-discount-price', new_price);

                // меняем в хедер карт стоимость
                var head_new_price = Number(head_total_price.data('price')) - Number(data.products_discount);
                head_total_price.html(head_new_price);
                head_total_price.data('promo-discount', data.products_discount).attr('data-promo-discount', data.products_discount);
            }

            // если промокод - скидка на весь заказ %
            if(data.type==2){

                ch_cart_wrap.html(data.ch_cart);
                cart_wrap.html(data.cart);

                next_block.find('small.promo_span em').html(data.promo_sum);
                // отображение скидки от промокода внизу страницы
                next_block.find('small.promo_span').addClass('block_important');
                $('#g-promo-discount').val(data.promo_sum);
                $('#g-promo-percent').val(data.percent);
                $('#g-promo-percent-round').val(data.round);

                // пересчитываем баллы, если необходимо
                var points = Number($('#g-points').val());
                if(points){
                    var no_points_price = Number(head_total_price.data('price')) - Number(data.promo_sum);
                    var temp = no_points_price/2;
                    if(temp < points){
                        var new_points = Math.floor(temp);
                        number_animation($('#points-em'), points, new_points);
                        number_animation(next_block.find('small.points_span em'), points, new_points);
                        $('#g-points').val(new_points);
                        points = new_points;
                    }
                }

                var new_price = Number(head_total_price.data('price')) - Number(data.promo_sum) - points;
                number_animation(total_price, total_price.data('price'), new_price);
                total_price.data('price', new_price).attr('data-price', new_price);
                head_total_price.data('discount-price', new_price).attr('data-discount-price', new_price);
            }

            // удаляем подарок за оплату наличными если общаю сумма меньше минимальной суммы для подарка
            add_gift_cash('add_promo', '');

            // показать/скрыть подарки за самовывоз
            toggle_gift_pickup();
        }
        input.val('');
        load.hide();
    }, "json");

}

function cancel_promo() {
    var wrap = $('#cancel-promo').closest('.ch-d');
    var error = wrap.find('.error');
    var promo = wrap.find('.ch-promocode');
    var total_price = $('#ch-cart-total-price');
    var head_total_price = $('#cart-total-price');
    var next_block = $('.ch-next');
    var points = Number($('#g-points').val());
    var promo_type = $('#g-promo-type').val();
    var discount_promo_type = $('#g-promo-discount-type').val();
    var birthday = $('#g-promo-birthday').val();

    var ch_cart_wrap = $('#ch-cart-wrap');
    var cart_wrap = $('#cart-products');

    // флаг об обновлении корзины, по умолчанию, не обновлять
    var gc = 0;

    wrap.find('div').show();
    wrap.removeClass('block_important');
    promo.hide().removeClass('block_important');
    error.html('');
    $('.clear_promo_text').remove();
    $('body').data('promo-min-sum', '').attr('data-promo-min-sum', '');
    $('body').data('promo-delivery', '').attr('data-promo-delivery', '');
    $('body').data('promo-type', '').attr('data-promo-type', '');
    $('#g-promo-type').val('');
    $('#g-promo-discount-type').val('');
    $('#g-promo-required-products').val('');
    $('#g-promo-week-days').val('');
    $('#g-promo-is-not-points').val('');
    $('#g-promo-is-not-dr').val('');
    $('#g-promo-is-not-delivery').val('');
    $('#g-promo-exceptions-dates').val('');
    $('#g-promo-birthday').val('');

    // изменения по корзине
    var elements = $('section');
    // если промокод - подарок
    if(promo_type==3){
        $('#cart-products section').each(function(){
            if($(this).data('property') == 'promo') {
                if ($(this).data('autoadd') != '') {
                    var auto_id = $(this).data('autoadd').split(",");
                    $.each(auto_id, function(index, value) {
                        var aid = value.split("|");
                        aid[0] = aid[0].replace('_', '__');
                        var auto_add_id = '';
                        $('#cart-products section').each(function () {
                            auto_add_id = $(this).attr('id');
                            auto_add_id = auto_add_id.replace("ch_", "");
                            if (auto_add_id == aid[0]) {
                                var aa_ch_section = $('#ch_' + auto_add_id);
                                var aa_section = $('#' + auto_add_id);
                                var aa_oldcount = Number(aa_section.find('.cart-cnt-line span').html());
                                var aa_newcount = aa_oldcount - Number(aid[1]);
                                if(aa_newcount == 0){
                                    aa_ch_section.remove();
                                }else{
                                    aa_section.find('.cart-cnt-line span').html(aa_newcount);
                                    aa_ch_section.find('.cart-cnt-line span').html(aa_newcount);
                                }
                            }
                        });
                    });
                }
            }
        });
        elements.each(function(){
            if($(this).data('property') == 'promo'){
                $(this).remove();
            }
        });
    }
    // если промокод - скидка на весь заказ (руб) и на определенные товары
    if(promo_type==1){
        // меняем оплату баллами, если есть
        if(points){
            var no_points_price = Number(head_total_price.data('price'));
            var temp = no_points_price/2;
            if(temp > points){
                var new_points = Math.floor(temp);
                var max = Number($('#points-em').data('points'));
                temp = Math.floor(temp);
                if(new_points > max){
                    new_points = max;
                }else{
                    while(new_points < temp && new_points < max) {
                        new_points++;
                    }
                }
                number_animation($('#points-em'), points, new_points);
                number_animation(next_block.find('small.points_span em'), points, new_points);
                $('#g-points').val(new_points);
                points = new_points;
            }
        }

        //var discount_sum = Number(next_block.find('small.promo_span em').html());
        var new_price = Number(head_total_price.data('price'));
        $('#g-promo-discount').val(0);

        next_block.find('small.promo_span em').html('')
        next_block.find('small.promo_span').removeClass('block_important');
        number_animation(total_price, total_price.data('price'), new_price-points);
        total_price.data('price', new_price-points).attr('data-price', new_price-points);

        var ds = Number(head_total_price.data('price')) - points;
        head_total_price.html(head_total_price.data('price'));
        head_total_price.data('discount-price', ds).attr('data-discount-price', ds);

        // если скидка на определенные товары, ставим метку, что нужно обновить корзину
        if(discount_promo_type==1){
            var gc = 1;
            head_total_price.html(new_price);
            head_total_price.data('promo-discount', 0).attr('data-promo-discount', 0);
            $('#g-promo-products').val('');
            $('#g-promo-products-limit').val('');
            $('#g-promo-products-group').val('');
        }
    }

    // проверякем  день рождение
    if(birthday == 1){
        var birthday = 1;
        $.post('/ajax/cancel_birthday', {birthday: birthday});
    }

    // если промокод - скидка на весь заказ %
    if(promo_type==2){
        // меняем оплату баллами, если есть
        if(points){
            var no_points_price = Number(head_total_price.data('price'));
            var temp = no_points_price/2;
            if(temp > points){
                var new_points = Math.floor(temp);
                var max = Number($('#points-em').data('points'));
                temp = Math.floor(temp);
                if(new_points > max){
                    new_points = max;
                }else{
                    while(new_points < temp && new_points < max) {
                        new_points++;
                    }
                }
                number_animation($('#points-em'), points, new_points);
                number_animation(next_block.find('small.points_span em'), points, new_points);
                $('#g-points').val(new_points);
                points = new_points;
            }
        }

        var new_price = Number(head_total_price.data('price'));
        $('#g-promo-discount').val(0);
        $('#g-promo-percent').val(0);
        $('#g-promo-percent-round').val('');

        next_block.find('small.promo_span em').html('')
        next_block.find('small.promo_span').removeClass('block_important');
        number_animation(total_price, total_price.data('price'), new_price-points);
        total_price.data('price', new_price-points).attr('data-price', new_price-points);

        var ds = Number(head_total_price.data('price')) - points;
        head_total_price.html(head_total_price.data('price'));
        head_total_price.data('discount-price', ds).attr('data-discount-price', ds);

        console.log('2 '+new_price);
        console.log($('#min-gift-cash').val());
        if(new_price >= $('#min-gift-cash').val()){
            console.log('add_2');
            add_gift_cash('add', '');
        }

        // ставим метку, что нужно обновить корзину
        var gc = 1;
    }

    $.post('/ajax/run_promo', {promo:0,promo_clear:1, get_cart:gc}, function(data){
        if(gc==1){
            ch_cart_wrap.html(data.ch_cart);
            cart_wrap.html(data.cart);

            // показать/скрыть подарки за самовывоз
            toggle_gift_pickup();
        }
        // добавляем или удаляем подарок за оплату наличными
        add_gift_cash('del_promo', '');
    }, "JSON");
}

function property_click(id){
    var ch = $('#'+id);
    var wrap = ch.closest('.js-product');
    if(!ch.prop("checked")){
        ch.prop("checked", 'checked');
        var price = ch.data('price');
        var weight = ch.data('weight');
        var weight_type = ch.data('weight-type');
        var property = ch.val();
        var span_price = wrap.find('.ac-price span');
        var old_price = span_price.html();
        var p = wrap.find('span.p');

        var input_price = wrap.find('input[name=price]');
        var input_property = wrap.find('input[name=property]');

        input_price.val(price);
        p.html(price+'<i class="rub">q</i>')
        input_property.val(property);

        // если есть характеристику у свойства, то меняем их
        if(weight && weight_type){
            var w = wrap.find('.product-weight');
            w.html('<i class="mdi mdi-weight-gram"></i> '+weight+' '+weight_type);
        }

        number_animation(span_price, old_price, price);
    }
}

// кнопка добавления в корзину
function addcart(elem){
    var button = $(elem);
    var wrap = button.closest('.js-product');

    var id = wrap.find('input[name=id]');
    var title = wrap.find('input[name=title]');
    var property = wrap.find('input[name=property]');
    var price = wrap.find('input[name=price]');
    var image = wrap.find('input[name=image]');
    var category = wrap.find('input[name=category]');
    var list = wrap.find('input[name=list]');
    var items = '';

    if($('#other-modal').is(':visible')){
        // в окне есть бесплатные допы, значит проверяем выбраны ли они и даем подсазку если нет
        if(button.data('freeitems')==1){
            var other_blocks = $('#other-modal').find('.other-cw');
            var check_freeitems = 0;
            other_blocks.each(function(){
                if($(this).hasClass('disabled'))return;
                if($(this).data('is_remove')==1)return;
                if($(this).data('max') == 1 && $(this).find('div.checked').length)return;

                var check_price = $(this).find('.other-price');
                check_price.each(function(){
                    var price = Number($(this).data('price'));
                    if(price == 0){
                        check_freeitems = 1;
                    }
                });
            });
            if(check_freeitems){
                button.data('freeitems', 0).attr('data-freeitems', 0);
                button.tooltip({
                   trigger: 'manual'
                });
                button.tooltip('show');
                return false;
            }

        }

        $('#other-modal').modal('hide');
        var def_price = wrap.find('input[name=def_price]');
        $('#product_'+id.val()).find('.p-price .sp-price').html(def_price.val()+' <i class="rub">q</i>');
        items = wrap.find('input[name=items]').val();
    }


    ec_addcart(title.val().split('"').join('').split("'").join(""), id.val(), price.val(), category.val(), 1, list.val());

    $.post('/ajax/change_cart', {type:'add', id:id.val(), property:property.val(), items:items}, function(data) {
        // визуальные изменения
        visual_change_cart('add', id.val(), title.val(), property.val(), price.val(), image.val());
        //console.log(property.val());
    });
}

// +1
function pluscart(elem){
    var button = $(elem);
    var section = button.closest('section');
    var block_id = section.attr('id');
    block_id = block_id.replace("ch_", "");

    var wrap = button.closest('section');
    var title = wrap.find('h4').html().split('"').join('').split("'").join("");
    var id = wrap.data('id');
    var price = Number(wrap.find('.price').data('price'))+Number(wrap.find('.price').data('item-price'));
    var category = "";
    var list = "";

    // если защита от умников
    var sclass = section.attr('class');
    if(!sclass)sclass = '';
    var stitle = section.find('.p_title').html();
    if(!section.find('.p_title').length)stitle = '';
    if(sclass.indexOf('gift_pickup')>=0 || sclass.indexOf('gift_dr')>=0 || stitle.indexOf('подарок')>=0){
        return;
    }

    ec_addcart(title, id, price, category, 1, list);

    $.post('/ajax/change_cart', {type:'plus', id:block_id});

    // визуальные изменения
    visual_change_cart('plus', block_id);
    add_gift_cash('add', '');
}

// -1
function minuscart(elem){
    var button = $(elem);
    var section = button.closest('section');
    var block_id = section.attr('id');
    block_id = block_id.replace("ch_", "");
    var del_promo = 0;


    // отключаем проверку на минимальную сумму при добавлении промокода
    // проверка, на минимальную сумму промокода
    // var check_next = check_promo_minsum(elem);
    // console.log('check_next: '+check_next);
    // if(check_next != 3){
    //     if(check_next==1){
    //         return;
    //     }
    //     if(check_next==2){
    //         del_promo = 1
    //     }
    // }


    // защита от отрицательной суммы, если промокод скидка в руб на весь заказ
    var check_next = check_promo_zero(elem, '');
    //console.log('check_next: '+check_next);
    if(check_next != 3){
        if(check_next==1){
            return;
        }
        if(check_next==2){
            del_promo = 1
        }
    }

    // проверка, на удаление обязательного товара
    var check_req = check_promo_required(elem, 'minuscart');
    //console.log('check_req: '+check_req);
    if(check_req != 3 && !del_promo){
        if(check_req==1){
            return;
        }
        if(check_req==2){
            del_promo = 1
        }
    }

    var title = section.find('h4').html().split('"').join('').split("'").join("");
    var id = section.data('id');
    var price = Number(section.find('.price').data('price'))+Number(section.find('.price').data('item-price'));
    var category = "";
    var cnt = 1;
    ec_delcart(title, id, price, category, cnt);

    $.post('/ajax/change_cart', {type:'minus', id:block_id});
    // визуальные изменения
    visual_change_cart('minus', block_id, '', '', '', '', del_promo);
    add_gift_cash('delete', '');
}

// удаление из корзины
function removecart(elem){
    var button = $(elem);
    var section = button.closest('section');
    var block_id = section.attr('id');
    block_id = block_id.replace("ch_", "");
    var del_promo = 0;

    // отключаем проверку на минимальную сумму при добавлении промокода
    // проверка, на минимальную сумму промокода
    // var check_next = check_promo_minsum(elem, 'remove');
    // console.log('check_next: '+check_next);
    // if(check_next != 3){
    //     if(check_next==1){
    //         return;
    //     }
    //     if(check_next==2){
    //         del_promo = 1
    //     }
    // }

    // защита от отрицательной суммы, если промокод скидка в руб на весь заказ
    var check_next = check_promo_zero(elem, 'remove');
    //console.log('check_next: '+check_next);
    if(check_next != 3){
        if(check_next==1){
            return;
        }
        if(check_next==2){
            del_promo = 1
        }
    }

    // проверка, на удаление обязательного товара
    var check_req = check_promo_required(elem);
    if(check_req != 3 && !del_promo){
        if(check_req==1){
            return;
        }
        if(check_req==2){
            del_promo = 1
        }
    }

    var title = section.find('h4').html().split('"').join('').split("'").join("");
    var id = section.data('id');
    var price = Number(section.find('.price').data('price'))+Number(section.find('.price').data('item-price'));
    var category = "";
    var cnt = section.find('.cart-cnt-line span').html();
    ec_delcart(title, id, price, category, cnt);
    console.log(block_id);
    $.post('/ajax/change_cart', {type:'delete', id:block_id});
    // визуальные изменения
    visual_change_cart('delete', block_id, '', '', '', '', del_promo);
    //add_gift_cash('delete', '');
}

// визуальные изменения корзины
function visual_change_cart(type, id, title, property, price, image, del_promo){
    var message = $('#add-cart-message');
    var cart_wrap = $('#cart-products');
    var ch_cart_wrap = $('#ch-cart-wrap');
    var cart_total_price = $('#cart-total-price');
    var ch_cart_total_price = $('#ch-cart-total-price');
    var cart_total_count = $('#cart-total-count');
    var zakazat_btn = $('.cart-total-line div a');
    var ch_zakazat_btn = $('#ch-zakazat-btn');
    var xs_count = $('#xs-cart-count');
    var product = $('#product_'+id);
    var next_block = $('.ch-next');
    var points = Number($('#g-points').val());
    var promo_discount = Number($('#g-promo-discount').val());
    var promo_type = $('#g-promo-type').val();


    if(type=='add'){
        // сообщение о добавлении в корзину
        message.fadeIn(100);
        setTimeout(function(){
            message.fadeOut(700);
        }, 1000);

        $('div.cart').addClass('show_cart_btn');

        product.find('.p-price').addClass('none');
        product.find('.p-price-incart').removeClass('none');

        // если открыто окно с опциями, скрываем его
        if(product.find('.p-ps-wrap').is(':visible')){
            product.find('.p-ps-wrap').hide();
            $('.js-pv-info').show();
        }

        // активность кнопки Заказать
        zakazat_btn.removeAttr('disabled');
        ch_zakazat_btn.removeAttr('disabled');

        // делаем кнопку добавления disabled
        product.find('.p-price').prop('disabled', true);

        var old_price = cart_total_price.data('price');

        // добавление нового элемента в корзину
        $.post('/ajax/cart_list', function(data) {
            cart_wrap.html(data.list);
            ch_cart_wrap.html(data.ch_list);

            // если есть скидка в процентах, то пересчитываем ее
            if(data.discount_percent){
                $('#g-promo-discount').val(data.discount_promocode);
                // если акция скидка в процентах
                $('.ch-next small.promo_span').addClass('block_important');
                $('.ch-next small.promo_span em').html(data.discount_promocode);
            }

            // меняем оплату баллами, если есть
            if(points){
                var temp = data.price_not_points/2;
                if(temp > points){
                    var new_points = Math.floor(temp);
                    var max = Number($('#points-em').data('points'));
                    temp = Math.floor(temp);
                    if(new_points > max){
                        new_points = max;
                    }else{
                        while(new_points < temp && new_points < max) {
                            new_points++;
                        }
                    }
                    number_animation($('#points-em'), points, new_points);
                    number_animation(next_block.find('small.points_span em'), points, new_points);
                    $('#g-points').val(new_points);
                    points = new_points;
                }
            }

            var header_cart_price = Number(data.total_price) - Number(data.header_discount_promocode); // с учетом только персональной скидки
            cart_total_price.html(header_cart_price);
            cart_total_price.data('price', data.total_price).attr('data-price', data.total_price);
            cart_total_price.data('discount-price', data.discount_price).attr('data-discount-price', data.discount_price);
            cart_total_price.data('promo-discount', data.header_discount_promocode).attr('data-promo-discount', data.header_discount_promocode);

            number_animation(ch_cart_total_price, ch_cart_total_price.data('price'), data.discount_price);
            ch_cart_total_price.data('price', data.discount_price).attr('data-price', data.discount_price);

            number_animation(cart_total_count, cart_total_count.data('value'), data.header_cart_total_price);
            cart_total_count.data('value', data.header_cart_total_price).attr('data-value', data.header_cart_total_price);


            $('#g-promo-discount').val(data.discount_promocode);

            // убираем disabled
            product.find('.p-price').prop('disabled', false);

            // показать/скрыть подарки за самовывоз
            toggle_gift_pickup();
        }, "json");

        xs_count.html(Number(xs_count.html())+1);
        $('.xs-cart-btn').removeClass('none');

        // console.log('Add: '+title+' ('+price+' руб.)')
    }

    if(type=='plus'){
        var section = $('#'+id);
        var ch_section = $('#ch_'+id);

        // если у товара есть авто-товары
        var autoadd = section.data('autoadd');
        // количество добавлеяемых автотоваров
        var aa_count = 0;
        // стоимость добавлеяемых автотоваров
        var aa_price = 0;
        if(autoadd){
            var aa = autoadd.split(',');
            $.each(aa,function(k, v){
                // bb[0] - id, bb[1] - count
                var bb = v.split('|');
                var aa_id = bb[0].replace('_', '__');
                var aa_ch_section = $('#ch_'+aa_id);
                var aa_section = $('#'+aa_id);
                var aa_oldcount = Number(aa_section.find('.cart-cnt-line span').html());
                var aa_newcount = Number(bb[1])+aa_oldcount;
                var aa_oldprice = Number(aa_section.find('.price b').data('price'));
                var aa_newprice = Number(aa_section.find('.price').data('price'))*aa_newcount;

                // анимация цифр (стоимость автотоваров)
                number_animation(aa_section.find('.price b'), aa_oldprice, aa_newprice);
                aa_section.find('.price b').data('price', aa_newprice).attr('data-price', aa_newprice);
                number_animation(aa_ch_section.find('.price b'), aa_oldprice, aa_newprice);
                aa_ch_section.find('.price b').data('price', aa_newprice).attr('data-price', aa_newprice);

                // анимация цифр (количество автотоваров)
                aa_section.find('.cart-cnt-line span').html(aa_newcount);
                aa_ch_section.find('.cart-cnt-line span').html(aa_newcount);

                aa_count += Number(bb[1]);
                aa_price += Number(bb[1])*Number(aa_section.find('.price').data('price'));
            });
        }

        // меняем общее количество
        var cart_count = Number(cart_total_count.data('count'));
        var count_result = cart_count + 1 + aa_count;
        cart_total_count.data('count', count_result).attr('data-count', count_result);
        xs_count.html(Number(xs_count.html())+1);

        // меняем общую стоимость корзины
        var npf_count = Number(section.find('.cart-cnt-line span').html()); // старое кол-во блюд
        var item_price = Number(section.find('.price').data('item-price'));
        var item_price_for_one = item_price/npf_count; // стоимость допов за 1
        var new_item_price = item_price_for_one*(npf_count+1); // новая полная стоимость допов

        var price = Number(section.find('.price').data('price'));
        var full_price = Number(section.find('.price').data('price'));

        // если у товара есть персональная скидка
        var p_discount = Number(section.data('promo-discount'));
        var tp_discount = Number(section.data('total-promo-discount'));
        var new_p_discount = Number(cart_total_price.data('promo-discount'));

        if(p_discount){
            // проверяем, позволяют ли условия еще добавить блюдо со скидкой
            var check = 1;

            // если акция не скидка в процентах
            if(promo_type != 2) {
                var p_max_count = Number(section.data('promo-count'));
                var p_used = Number(section.data('promo-used'));
                if (p_max_count <= p_used) check = 0;

                var temp_order_max_count = $('#g-promo-products-limit').val();
                var cc = temp_order_max_count.split(',');
                var p_order_group = Number($('#g-promo-products-group').val());
                var t = '';
                var p_order_max_count = 9999;
                $(cc).each(function (i, v) {
                    t = v.split('_');
                    if (t[1] == p_order_group) {
                        p_order_max_count = t[0];
                    }
                });
                var p_order_used = 0;
                $('#cart-products section').each(function () {
                    p_order_used += Number($(this).data('promo-used'));
                });
                if (p_order_max_count <= p_order_used) check = 0;

                // проверям максимум для одинаковых блюд с разными допами
                var pprdcts = $('#g-promo-products').val().split(',');
                var pfrp = $('#g-promo-products-group').val();
                var pprdcts_arr = {};
                $(pprdcts).each(function (i, v) {
                    var tmp = v.split('|');
                    if (tmp[3] == pfrp) {
                        pprdcts_arr[tmp[0]] = tmp[1];
                        if(!tmp[1])pprdcts_arr[tmp[0]] = 9999;
                    }
                });
                var fid = id.split('_');
                fid.pop();
                fid = fid.join('_');
                var broteer_s = $('#cart-products section[id ^= ' + fid + '_]');
                var ch_broteer_s = $('#ch-cart-wrap section[id ^= ch_' + fid + '_]');
                if (pprdcts_arr[fid] && broteer_s.length > 1) {
                    var brother_cnt = 0;
                    broteer_s.each(function () {
                        brother_cnt += $(this).data('promo-used');
                    });
                    var bcnt_max = pprdcts_arr[fid] - brother_cnt;
                    if(bcnt_max == 1) {
                        broteer_s.each(function () {
                            if(id != $(this).attr('id')){
                                var used = $(this).data('promo-used');
                                $(this).data('promo-count', used).attr('data-promo-count', used);
                            }
                        });
                        ch_broteer_s.each(function () {
                            if('ch_'+id != $(this).attr('id')){
                                var used = $(this).data('promo-used');
                                $(this).data('promo-count', used).attr('data-promo-count', used);
                            }
                        });
                    }
                    if(bcnt_max > 1) {
                        broteer_s.each(function () {
                            if(id != $(this).attr('id')) {
                                var count = $(this).data('promo-count') - 1;
                                $(this).data('promo-count', count).attr('data-promo-count', count);
                            }
                        });
                        ch_broteer_s.each(function () {
                            if('ch_'+id != $(this).attr('id')) {
                                var count = $(this).data('promo-count') - 1;
                                $(this).data('promo-count', count).attr('data-promo-count', count);
                            }
                        });
                    }
                    if (bcnt_max < 1) {
                        check = 0;
                        broteer_s.each(function () {
                            var used = $(this).data('promo-used');
                            $(this).data('promo-count', used).attr('data-promo-count', used);
                        });
                        ch_broteer_s.each(function () {
                            var used = $(this).data('promo-used');
                            $(this).data('promo-count', used).attr('data-promo-count', used);
                        });
                    }
                }
            }

            // если мы прошли все проверки, корректируем стоимость
            if(check){
                price -= p_discount;
                section.data('total-promo-discount', tp_discount+p_discount).attr('data-total-promo-discount', tp_discount+p_discount);
                ch_section.data('total-promo-discount', tp_discount+p_discount).attr('data-total-promo-discount', tp_discount+p_discount);
                new_p_discount = promo_discount + p_discount;
                $('#g-promo-discount').val(new_p_discount);

                if(promo_type != 2){
                    section.data('promo-used', p_used+1).attr('data-promo-used', p_used+1);
                    ch_section.data('promo-used', p_used+1).attr('data-promo-used', p_used+1);
                }
            }
        }


        var total_price = Number(cart_total_price.data('price')); // старая полная стоимость
        var result = total_price + full_price + item_price_for_one + aa_price; // новая полная стоимость



        // если акция скидка в процентах
        if(promo_type == 2){
            $('.ch-next small.promo_span em').html(new_p_discount);
        }

        // меняем оплату баллами, если есть
        if(points){
            var rt = result - Number($('#g-promo-discount').val()); // новая стоимтость без баллов но со скидкой по промокоду
            var temp = rt/2;
            if(temp > points){
                var new_points = Math.floor(temp);
                var max = Number($('#points-em').data('points'));
                temp = Math.floor(temp);
                if(new_points > max){
                    new_points = max;
                }else{
                    while(new_points < temp && new_points < max) {
                        new_points++;
                    }
                }
                number_animation($('#points-em'), points, new_points);
                number_animation(next_block.find('small.points_span em'), points, new_points);
                $('#g-points').val(new_points);
                points = new_points;
            }
        }


        // анимация цифр
        var discount_price = result - points - Number($('#g-promo-discount').val()); // с учетом ВСЕХ скидок
        var discount_price_fh = result - new_p_discount; // учитывается только персональная скидка у товаров
        var old_price =  Number(cart_total_price.data('price')) - Number(cart_total_price.data('promo-discount'));  // старая стоимость в корзине в хедере

        number_animation(cart_total_price, old_price, discount_price_fh);
        cart_total_price.data('price', result).attr('data-price', result);
        cart_total_price.data('discount-price', discount_price).attr('data-discount-price', discount_price);
        cart_total_price.data('promo-discount', new_p_discount).attr('data-promo-discount', new_p_discount);

        number_animation(cart_total_count, cart_total_count.data('value'), discount_price_fh);
        cart_total_count.data('value', discount_price_fh).attr('data-value', discount_price_fh);

        number_animation(ch_cart_total_price, ch_cart_total_price.data('price'), discount_price);
        ch_cart_total_price.data('price', discount_price).attr('data-price', discount_price);

        // меняем сумму этого товара
        var t_price = Number(section.find('.price b').data('price'));
        var r = t_price+price+item_price_for_one;
        //console.log('t_price'+t_price);
        //console.log('price'+price);
        var old_price = section.find('.price b').data('price');
        // анимация цифр
        number_animation(section.find('.price b'), old_price, r);
        section.find('.price b').data('price', r).attr('data-price', r);
        number_animation(ch_section.find('.price b'), old_price, r);
        ch_section.find('.price b').data('price', r).attr('data-price', r);

        section.find('.price').data('item-price', new_item_price).attr('data-item-price', new_item_price); // новая стоимость допов
        ch_section.find('.price').data('item-price', new_item_price).attr('data-item-price', new_item_price); // новая стоимость допов

        // меняем количество элемента
        var m_count = Number(section.find('.cart-cnt-line span').html());
        m_count = m_count+1;
        section.find('.cart-cnt-line span').html(m_count);
        ch_section.find('.cart-cnt-line span').html(m_count);

        // анимация цифр зачеркнутой цены
        var oldpriceblock = ch_section.find('.old-price span');
        var opb = m_count*Number(section.find('.price').data('price'))+new_item_price;
        number_animation(oldpriceblock, oldpriceblock.data('tp'), opb);
        oldpriceblock.data('tp', opb).attr('data-tp', opb);

        // меняем количество допов
        section.find('i.io-count').each(function(){
             $(this).html(Number($(this).data('count'))*m_count);
        });
        ch_section.find('i.io-count').each(function(){
             $(this).html(Number($(this).data('count'))*m_count);
        });

        // показать/скрыть подарки за самовывоз
        toggle_gift_pickup();
    }

    if(type=='minus'){
        var section = $('#'+id);
        var ch_section = $('#ch_'+id);

        // если у товара есть авто-товары
        var autoadd = section.data('autoadd');
        // количество добавлеяемых автотоваров
        var aa_count = 0;
        // стоимость добавлеяемых автотоваров
        var aa_price = 0;
        if(autoadd){
            var aa = autoadd.split(',');
            $.each(aa,function(k, v){
                // bb[0] - id, bb[1] - count
                var bb = v.split('|');
                var aa_id = bb[0].replace('_', '__');
                var aa_ch_section = $('#ch_'+aa_id);
                var aa_section = $('#'+aa_id);
                var aa_oldcount = Number(aa_section.find('.cart-cnt-line span').html());
                var aa_newcount = aa_oldcount - Number(bb[1]);
                var aa_oldprice = Number(aa_section.find('.price b').data('price'));
                var aa_newprice = Number(aa_section.find('.price').data('price'))*aa_newcount;

                // анимация цифр (стоимость автотоваров)
                number_animation(aa_section.find('.price b'), aa_oldprice, aa_newprice);
                aa_section.find('.price b').data('price', aa_newprice).attr('data-price', aa_newprice);
                number_animation(aa_ch_section.find('.price b'), aa_oldprice, aa_newprice);
                aa_ch_section.find('.price b').data('price', aa_newprice).attr('data-price', aa_newprice);

                // анимация цифр (количество автотоваров)
                aa_section.find('.cart-cnt-line span').html(aa_newcount);
                aa_ch_section.find('.cart-cnt-line span').html(aa_newcount);

                // если автотовар был последним
                if(aa_newcount < 1){
                    aa_section.slideUp('normal');
                    aa_ch_section.slideUp('normal');
                }

                aa_count += Number(bb[1]);
                aa_price += Number(bb[1])*Number(aa_section.find('.price').data('price'));
            });
        }

        var f_id = id.split('_')[0];
        product = $('#product_'+f_id);

        // допы
        var npf_count = Number(section.find('.cart-cnt-line span').html()); // старое кол-во блюд
        var item_price = Number(section.find('.price').data('item-price'));
        var item_price_for_one = item_price/npf_count; // стоимость допов за 1
        var new_item_price = item_price_for_one*(npf_count-1); // новая полная стоимость допов

        // меняем количество элемента
        var m_count = Number(section.find('.cart-cnt-line span').html());
        m_count = m_count-1;
        section.find('.cart-cnt-line span').html(m_count);
        ch_section.find('.cart-cnt-line span').html(m_count);

        // меняем общую стоимость корзины
        var price = Number(section.find('.price').data('price'));
        var full_price = Number(section.find('.price').data('price'));

        // если у товара есть персональная скидка
        var p_discount = Number(section.data('promo-discount'));
        var tp_discount = Number(section.data('total-promo-discount'));
        var new_p_discount = Number(cart_total_price.data('promo-discount'));
        if(p_discount){
            // проверяем, позволяют ли условия убрать товар со скидкой
            var check = 0;

            // колчиество, до того как нажали кнопку -
            var y_count = m_count+1;
            var p_used = Number(section.data('promo-used'));
            if(y_count == p_used)check = 1;
            // если акция скидка в процентах
            if(promo_type == 2)check = 1;

            // удаляем товар со скидкой
            if(check){
                price -= p_discount;
                section.data('total-promo-discount', tp_discount-p_discount).attr('data-total-promo-discount', tp_discount-p_discount);
                ch_section.data('total-promo-discount', tp_discount-p_discount).attr('data-total-promo-discount', tp_discount-p_discount);
                var new_p_discount = promo_discount - p_discount;
                $('#g-promo-discount').val(new_p_discount)

                if(promo_type != 2){

                    var fid = id.split('_');
                    fid.pop();
                    fid = fid.join('_');
                    var broteer_s = $('#cart-products section[id ^= ' + fid + '_]');
                    var ch_broteer_s = $('#ch-cart-wrap section[id ^= ch_' + fid + '_]');
                    broteer_s.each(function () {
                        if(id != $(this).attr('id')){
                            var count = $(this).data('promo-count')+1;
                            $(this).data('promo-count', count).attr('data-promo-count', count);
                        }
                    });
                    ch_broteer_s.each(function () {
                        if('ch_'+id != $(this).attr('id')) {
                            var count = $(this).data('promo-count') + 1;
                            $(this).data('promo-count', count).attr('data-promo-count', count);
                        }
                    });

                    section.data('promo-used', p_used-1).attr('data-promo-used', p_used-1);
                    ch_section.data('promo-used', p_used-1).attr('data-promo-used', p_used-1);
                }
            }
        }


        var total_price = Number(cart_total_price.data('price')); // старая полная стоимость
        var result = total_price - full_price - item_price_for_one - aa_price; // новая полная стоимость

        // если акция скидка в процентах
        if(promo_type == 2){
            $('.ch-next small.promo_span em').html(new_p_discount);
            if(!new_p_discount)$('.ch-next small.promo_span').removeClass('block_important');
        }

        // меняем оплату баллами, если есть
        if(points){
            var rt = result - Number($('#g-promo-discount').val()); // новая стоимтость без баллов но со скидкой по промокоду
            var temp = rt/2;
            if(temp < points){
                var new_points = Math.floor(temp);
                number_animation($('#points-em'), points, new_points);
                number_animation(next_block.find('small.points_span em'), points, new_points);
                $('#g-points').val(new_points);
                points = new_points;
            }
        }

        // анимация цифр
        var discount_price = result - points - Number($('#g-promo-discount').val()); // с учетом ВСЕХ скидок
        var discount_price_fh = result - new_p_discount; // учитывается только персональная скидка у товаров
        var old_price =  Number(cart_total_price.data('price')) - Number(cart_total_price.data('promo-discount'));  // старая стоимость в корзине в хедере

        number_animation(cart_total_price, old_price, discount_price_fh);
        cart_total_price.data('price', result).attr('data-price', result);
        cart_total_price.data('discount-price', discount_price).attr('data-discount-price', discount_price);
        cart_total_price.data('promo-discount', new_p_discount).attr('data-promo-discount', new_p_discount);

        number_animation(cart_total_count, cart_total_count.data('value'), discount_price_fh);
        cart_total_count.data('value', discount_price_fh).attr('data-value', discount_price_fh);

        number_animation(ch_cart_total_price, ch_cart_total_price.data('price'), discount_price);
        ch_cart_total_price.data('price', discount_price).attr('data-price', discount_price);

        // меняем сумму этого товара
        var t_price = Number(section.find('.price b').data('price'));
        var r = t_price - price - item_price_for_one;
        var old_price = section.find('.price b').data('price');

        section.find('.price').data('item-price', new_item_price).attr('data-item-price', new_item_price); // новая стоимость допов
        ch_section.find('.price').data('item-price', new_item_price).attr('data-item-price', new_item_price); // новая стоимость допов

        // анимация цифр
        number_animation(section.find('.price b'), old_price, r);
        section.find('.price b').data('price', r).attr('data-price', r);
        number_animation(ch_section.find('.price b'), old_price, r);
        ch_section.find('.price b').data('price', r).attr('data-price', r);

        if(m_count<1){
            // если стоимость товар был последним
            section.slideUp('normal', function() {
                if(result<1){
                    cart_wrap.html('<p class="cart-empty">Корзина пуста</p>');
                    $('.xs-cart-btn').addClass('none');
                    $('div.cart').removeClass('show_cart_btn');
                }
            });
            ch_section.slideUp('normal', function() {
                if(result<1){
                    ch_cart_wrap.html('<p class="cart-empty">Корзина пуста</p>');
                    $('#points-em').data('points', 0).attr('data-points', 0);
                    $('div.cart').removeClass('show_cart_btn');
                }
            });
            if(result<1){
                zakazat_btn.attr('disabled', 'disabled');
                ch_zakazat_btn.attr('disabled', 'disabled');

                $.post('/ajax/remove_gift_dr', {r:1});
            }

            var brother = cart_wrap.find('section[data-id="'+f_id+'"]:not(#'+id+'):visible:first');
            if(brother.length){
                // если в корзине есть такой же товар с другим свойством
                property_click('property_'+brother.data('property'));
            }else{
                // меняем кнопку у товра (на "Не в корзине")
                product.find('.p-price').removeClass('none');
                product.find('.p-price-incart').addClass('none');
            }
        }

        // меняем общее количество
        var cart_count = Number(cart_total_count.data('count'));
        var count_result = cart_count - 1 - aa_count;
        cart_total_count.data('count', count_result).attr('data-count', count_result);
        xs_count.html(Number(xs_count.html())-1);

        // изменение было из модалки, промокод нужно удалить
        if(del_promo){
            $('#del-promo-modal').modal('hide');
            cancel_promo();
        }

        // анимация цифр зачеркнутой цены
        var oldpriceblock = ch_section.find('.old-price span');
        var opb = m_count*Number(section.find('.price').data('price'))+new_item_price;
        number_animation(oldpriceblock, oldpriceblock.data('tp'), opb);
        oldpriceblock.data('tp', opb).attr('data-tp', opb);

        // меняем количество допов
        section.find('i.io-count').each(function(){
            $(this).html(Number($(this).data('count'))*m_count);
        });
        ch_section.find('i.io-count').each(function(){
            $(this).html(Number($(this).data('count'))*m_count);
        });

        // показать/скрыть подарки за самовывоз
        toggle_gift_pickup();
    }

    if(type=='delete'){

        var section = $('#'+id);
        var ch_section = $('#ch_'+id);

        // если у товара есть авто-товары
        var autoadd = section.data('autoadd');
        // количество добавлеяемых автотоваров
        var aa_count = 0;
        // стоимость добавлеяемых автотоваров
        var aa_price = 0;
        if(autoadd){
            var aa = autoadd.split(',');
            $.each(aa,function(k, v){
                // bb[0] - id, bb[1] - count
                var bb = v.split('|');
                var aa_id = bb[0].replace('_', '__');
                var aa_ch_section = $('#ch_'+aa_id);
                var aa_section = $('#'+aa_id);
                var aa_oldcount = Number(aa_section.find('.cart-cnt-line span').html());
                var aa_newcount = aa_oldcount - Number(bb[1])*Number(section.find('.cart-cnt-line span').html());
                var aa_oldprice = Number(aa_section.find('.price b').data('price'));
                var aa_newprice = Number(aa_section.find('.price').data('price'))*aa_newcount;

                // анимация цифр (стоимость автотоваров)
                number_animation(aa_section.find('.price b'), aa_oldprice, aa_newprice);
                aa_section.find('.price b').data('price', aa_newprice).attr('data-price', aa_newprice);
                number_animation(aa_ch_section.find('.price b'), aa_oldprice, aa_newprice);
                aa_ch_section.find('.price b').data('price', aa_newprice).attr('data-price', aa_newprice);

                // анимация цифр (количество автотоваров)
                aa_section.find('.cart-cnt-line span').html(aa_newcount);
                aa_ch_section.find('.cart-cnt-line span').html(aa_newcount);

                // если автотовар был последним
                if(aa_newcount < 1){
                    aa_section.slideUp('normal');
                    aa_ch_section.slideUp('normal');
                }

                aa_count += Number(bb[1]);
                aa_price += Number(bb[1])*Number(aa_section.find('.price').data('price'));
            });
        }

        var f_id = id.split('_')[0];
        product = $('#product_'+f_id);

        // допы
        var item_price = Number(section.find('.price').data('item-price'));

        // считаем общую стоимость корзины
        var price = Number(section.find('.price').data('price'));

        // если у товара есть персональная скидка
        var tp_discount = Number(section.data('total-promo-discount'));
        var new_pers_pd = Number(cart_total_price.data('promo-discount')) - tp_discount; // новая персональная скидка от промокода
        var new_p_discount = promo_discount - tp_discount; // новая полная скидка от промокода

        var fid = id.split('_');
        fid.pop();
        fid = fid.join('_');
        var broteer_s = $('#cart-products section[id ^= ' + fid + '_]');
        var ch_broteer_s = $('#ch-cart-wrap section[id ^= ch_' + fid + '_]');
        broteer_s.each(function () {
            if(id != $(this).attr('id')){
                var used = Number(section.data('promo-used')) + Number($(this).data('promo-count'));
                $(this).data('promo-count', used).attr('data-promo-count', used);
            }
        });
        ch_broteer_s.each(function () {
            if('ch_'+id != $(this).attr('id')) {
                var used = Number(section.data('promo-used')) + Number($(this).data('promo-count'));
                $(this).data('promo-count', used).attr('data-promo-count', used);
            }
        });
        section.data('promo-used', 0).attr('data-promo-used', 0);
        ch_section.data('promo-used', 0).attr('data-promo-used', 0);
        $('#g-promo-discount').val(new_p_discount)

        // если акция скидка в процентах
        if(promo_type == 2){
            $('.ch-next small.promo_span em').html(new_p_discount);
            if(!new_p_discount)$('.ch-next small.promo_span').removeClass('block_important');
        }

        var count = Number(section.find('.cart-cnt-line span').html());
        var total_price = Number(cart_total_price.data('price')); // старая полная стоимость
        var result = total_price - price*count - item_price - aa_price; // новая полная стоимость


        // меняем оплату баллами, если есть
        if(points){
            var rt = result - Number($('#g-promo-discount').val()); // новая стоимтость без баллов но со скидкой по промокоду
            var temp = rt/2;
            if(temp < points){
                var new_points = Math.floor(temp);
                number_animation($('#points-em'), points, new_points);
                number_animation(next_block.find('small.points_span em'), points, new_points);
                $('#g-points').val(new_points);
                points = new_points;
            }
        }


        // анимация цифр
        var discount_price = result - points - Number($('#g-promo-discount').val()); // с учетом ВСЕХ скидок
        var discount_price_fh = result - new_pers_pd; // учитывается только персональная скидка у товаров
        var old_price =  Number(cart_total_price.data('price')) - Number(cart_total_price.data('promo-discount'));  // старая стоимость в корзине в хедере

        number_animation(cart_total_price, old_price, discount_price_fh);
        cart_total_price.data('price', result).attr('data-price', result);
        cart_total_price.data('discount-price', discount_price).attr('data-discount-price', discount_price);
        cart_total_price.data('promo-discount', new_pers_pd).attr('data-promo-discount', new_pers_pd);

        number_animation(cart_total_count, cart_total_count.data('value'), discount_price_fh);
        cart_total_count.data('value', discount_price_fh).attr('data-value', discount_price_fh);

        number_animation(ch_cart_total_price, ch_cart_total_price.data('price'), discount_price);
        ch_cart_total_price.data('price', discount_price).attr('data-price', discount_price);

        // считаем количество
        var cart_count = Number(cart_total_count.data('count'));
        var count_result = cart_count - count - aa_count;
        cart_total_count.data('count', count_result).attr('data-count', count_result);
        xs_count.html(count_result);

        section.slideUp('normal', function() {
            if(result<1){
                cart_wrap.html('<p class="cart-empty">Корзина пуста</p>');
                $('.xs-cart-btn').addClass('none');
                $('div.cart').removeClass('show_cart_btn');
            }
        });
        ch_section.slideUp('normal', function() {
            if(result<1){
                ch_cart_wrap.html('<p class="cart-empty">Корзина пуста</p>');
                $('#points-em').data('points', 0).attr('data-points', 0);
                $('div.cart').removeClass('show_cart_btn');
            }
        });
        if(result<1){
            zakazat_btn.attr('disabled', 'disabled');
            ch_zakazat_btn.attr('disabled', 'disabled');

            $.post('/ajax/remove_gift_dr', {r:1});
        }

        var brother = cart_wrap.find('section[data-id="'+f_id+'"]:not(#'+id+'):visible:first');
        if(brother.length){
            // если в корзине есть такой же товар с другим свойством
            property_click('property_'+brother.data('property'));
        }else{
            // меняем кнопку у товра (на "Не в корзине")
            product.find('.p-price').removeClass('none');
            product.find('.p-price-incart').addClass('none');
        }

        // если удаляем подарок по акции ДР, убираем disabled у кнопок
        if(section.data('property')=='gift_dr' || section.data('property')=='gift_pickup'){
            var prop = section.data('property');
            $('.type_'+prop+' button.add-gift').prop('disabled', false);
        }

        // изменение было из модалки, промокод нужно удалить
        if(del_promo){
            $('#del-promo-modal').modal('hide');
            cancel_promo();
        }

        // показать/скрыть подарки за самовывоз
        toggle_gift_pickup();
    }

}

function toggle_gift_pickup(){
    var sm = 0;
    // 1 часть: проверяем минимальную сумму для подарков на ДР
    var products_wrap = $('#cart-products');
    var gift_dr_minsum = Number($('#g-gift_dr_minsum').val());
    var cp = Number($('#ch-cart-total-price').data('price'));
    var gift_dr_check = products_wrap.find('section[data-property="gift_dr"]').length;
    if(gift_dr_check && gift_dr_minsum > cp){
        // показываем модалку об удалении подарков и удаляем их
        sm = 1;
        $('#p-dr-gifts').show();
        remove_gifts('gift_dr');
    }
    if(gift_dr_minsum > cp){
        // скрыть подарки
        $('.type_gift_dr').slideUp();
    }
    // 1.1: проверяем сочетаемость промокода с подарками на ДР
    if($('#g-promo-is-not-dr').val()==1){
        // скрыть подарки
        $('.type_gift_dr').slideUp();
    }


    // 2 часть: проверяем минимальную сумму для подарков за самовывоз
    // тип доставки
    var d_type = $('#change-delivery-btns li.active').data('type');
    // тип акции для самовывоза 1 - подарки, 0 - скидка
    var dd_type = $('#g-dd_type').val();
    if(d_type==1 && dd_type==1){
        var dd_gifts_ms = Number($('#g-dd_gifts_ms').val());
        var sum = Number($('#cart-total-price').data('discount-price'));
        if(sum >= dd_gifts_ms && $('#g-promo-is-not-delivery').val()!=1 && $('#g-promo-birthday').val()!=1){
            // показать подарки
            $('.type_gift_pickup').slideDown();
        }else{
            // скрыть подарки
            $('.type_gift_pickup').slideUp();
            // показываем модалку, если в корзине были подарки за самовывоз
            if($('#cart-products section[data-property="gift_pickup"]').length){
                sm = 1;
                $('#p-pickup-gifts').show();
            }
            // удаляем выбранные подарки из корзины, если есть
            remove_gifts('gift_pickup');
        }
    }

    // 2.1: проверяем сочетаемость промокода с подарками за самовывоз
    if($('#g-promo-is-not-delivery').val()==1){
        // скрыть подарки
        $('.type_gift_pickup').slideUp();
    }
    // 2.2: скрываем подарки для промокода ДР
    if($('#g-promo-birthday').val()==1){
        // скрыть подарки
        $('.type_gift_pickup').slideUp();
    }

    if(sm){
        $('#del-gifts-info').modal('show');
    }
}

function number_animation(block, old_price, result){
    block.prop('number', old_price).animateNumber({
        number: result,
        easing: 'easeInQuad', // требуется jquery.easing
        numberStep: function(now, tween) {
            var floored_number = Math.floor(now),
                target = $(tween.elem);
            target.text(floored_number);
        }
    },500);
}
// восстановление пароля
function forgot_password(){
    event.preventDefault();

    var input = $('#auth-phone');
    var input_password = $('#auth-password');
    var input_login = $('input[name="hidden_login"]');
    var load = $('#auth-loading');
    var hint = $('#auth-hint');
    var error = $('#auth-error');
    var auth_block = $('#auth-block');
    var reg_block = $('#reg-block');
    var reg_pblock = $('#reg-password-block');
    load.show();
    hint.show();
    input_password.val('');
    input_login.val('');

    auth_block.hide();
    reg_block.hide();
    reg_pblock.hide();

    $('#confirm-phone-block').hide();
    $('#confirm-phone').show();

    error.html('');
    error.hide();

    $.post('/ajax/check_user', {phone:input.val()}, function(data) {
        if(data=='auth'){
            reg_block.show();
            // метка, что восстановление пароля
            $('#forgot-password').val('1');
            hint.html('для восстановления пароля необходимо подтвердить телефон').show();
        }
        if(data=='reg'){
            error.html('пользователь с таким телефоном не найден');
            error.show();
        }
        if(data=='error'){
            input.val('+7');
            error.html('ошибка сервера, попробуйте ввести номер телефона еще раз');
            error.show();
        }
        load.hide();
    });
}

function change_reg_texts() {
    var m = $('#forgot-password').val();
    if(m==1){
        $('#registration').html('Сохранить');
        $('#ofertaline').addClass('none');
        $('#reg-password').attr('placeholder', 'Новый пароль');
        $('#reg-confirm-password').attr('placeholder', 'Подтвердите новый пароль');
    }else{
        $('#registration').html('Зарегистрироваться');
        $('#ofertaline').removeClass('none');
        $('#reg-password').attr('placeholder', 'Пароль');
        $('#reg-confirm-password').attr('placeholder', 'Подтвердите пароль');
    }
}

// reCaptcha
function run_recaptcha(action, first){
    grecaptcha.ready(function() {
        var client_key = $('input[name="rc_client_key"]').val();
        grecaptcha.execute(client_key, {action: action})
            .then(function(token) {
                var data = new FormData();
                data.append("action", action);
                data.append("token", token);
                if(action=='phone_confirmation' || action=='phone_reconfirmation'){
                    data.append("phone", $('#auth-phone').val());
                }
                if(action=='writeguide'){
                    data.append("text", $('#wg-text').val());
                    data.append("contact", $('#wg-contact').val());
                    if($('#wg-file')[0].files[0]){
                        data.append("file", $('#wg-file')[0].files[0].name);
                    }
                    if($('#wg-file2')[0].files[0]){
                        data.append("file2", $('#wg-file2')[0].files[0].name);
                    }
                    if($('#wg-file3')[0].files[0]){
                        data.append("file3", $('#wg-file3')[0].files[0].name);
                    }
                }
                if(action=='details_order'){
                    data.append("phone", $('#order-phone').val());
                }
                if(action=='cancel_order'){
                    data.append("phone", $('#order-phone').val());
                }
                    fetch('/ajax/recaptcha?first='+first, {
                    method: 'POST',
                    body: data
                }).then(function(response) {
                    response.json().then(function(data) {
                        console.log(data);
                        if(data.action=='phone_confirmation'){
                            $('#auth-loading').hide();
                            if(first != 1){
                                $('#other-conf-link').addClass('none');
                                $('#resend-link').removeClass('none');
                            }else{
                                $('#other-conf-link').removeClass('none');
                                $('#resend-link').addClass('none');
                            }
                            if(data.result==1){
                                // все ок
                                $('#confirm-phone').hide();
                                $('#confirm-phone-block').show();
                                var sc_type = $('body').data('sc-type');
                                if(first == 1){
                                    $('#auth-hint').show().html('Что-бы получить "Код подтверждения" пожалуйста запустите нашего бота в Telegram и следуйте дальнейшим инструкциям<br><br><a href="https://t.me/wowpizzaru_bot" target="_blank" class="btn btn-tg">Telegram-бот "WOW! Pizza"</a>');
                                }else{
                                    if(sc_type==1){
                                        $('#auth-hint').show().html('на указанный номер отправлено смс с кодом подтверждения');
                                    }else{
                                        $('#auth-hint').show().html('<em style="color: #ff5500;font-style: normal;">Внимание!</em> На Ваш телефон поступит авто-звонок с неизвестного номера. <em style="color: #ff5500;font-style: normal;">Последние 4 цифры номера - это Код подтверждения.</em>');
                                    }
                                }
                                $('#conf-code').focus();
                                $('#error-code-hint').hide();
                                $('#resend-hint').hide();
                            }else if(data.result=='conf'){
                                if($('#auth-modal').data('change-phone')==0) {
                                    // смена текста, если нужно
                                    change_reg_texts();
                                    // телефон уже подтвержден
                                    $('#confirm-phone').hide();
                                    $('#reg-password-block').show();
                                    $('#auth-hint').html('пожалуйста введите пароль').show();
                                    $('#reg-password').focus();
                                }else{
                                    $('#auth-modal').modal('hide');
                                    var phone = $('#auth-phone').val().replace(/[^0-9]/g,'');
                                    $('#lk-phone-number').html(phone);
                                    $('#lk-success-alert').show().html('Номер телефона успешно изменен');
                                    $.post('/ajax/change_personal', {type:'phone', data:phone});
                                }
                            }else{
                                // ошибка
                                $('#auth-error').html(data.text).show().removeClass('mt-0');
                            }
                        }
                        if(data.action=='phone_reconfirmation'){
                            $('#auth-loading').hide();
                            if(data.result==1){
                                // все ок
                                $('#conf-code').val('').focus();
                                $('#resend-hint').html(data.text).show();
                                $('#auth-loading').hide();
                                if(data.sms==1){
                                    $('#auth-hint').show().html('<em style="color: #ff5500;font-style: normal;">Внимание! На указанный номер отправлено смс с кодом подтверждения</em>');
                                }
                            }else{
                                // ошибка
                                $('#resend-hint').hide();
                                $('#auth-loading').hide();
                                $('#resend-hint').html(data.text).show();
                            }
                        }
                        if(data.action=='writeguide'){
                            $('#wg-alert').removeClass('alert-danger').hide().html('');
                            if(data.result==1){
                                // все ок
                                $('#wg-alert').addClass('alert-success').show().html(data.text);
                            }else{
                                // ошибка
                                $('#wg-alert').addClass('alert-danger').show().html(data.text);
                            }
                            $('#wg-load').hide();
                            $('#wg-submit').show();
                            $('#wg-text').val('');
                            $('#wg-contact').val('');
                            $('#wg-file').val('');
                            $('#wg-file2').val('');
                            $('#wg-file3').val('');
                        }
                        if(data.action=='details_order'){
                            //console.log(data.result);
                            if(data.result==1){
                                // все ок
                                $('#conf-code-details-order').focus();
                                $('#error-code-hint').hide();
                            }else{
                                // ошибка
                                $('#resend-hint').html(data.text).show().removeClass('mt-0');
                            }
                        }
                        if(data.action=='cancel_order'){
                            console.log(data.result);
                            if(data.result==1){
                                // все ок
                                $('#conf-code-cansel-order').focus();
                                $('#cancels-error').hide();
                            }else{
                                // ошибка
                                $('#resend-hint-2').html(data.text).show().removeClass('mt-0');
                            }
                        }
                    });
                });
            });
    });
}

// проверка подтверждения телефона
function check_conf_code() {
    var load = $('#auth-loading');
    load.show();
    var conf_block = $('#confirm-phone-block');
    var pass_block = $('#reg-password-block');
    var conf_input = $('#conf-code');
    var conf_load = $('#auth-loading');
    var input_password = $('#reg-password');
    var error = $('#resend-hint');
    var hint = $('#auth-hint');
    var phone = $('#auth-phone');
    console.log(phone);

    error.hide();

    // проверка кода
    $.post('/ajax/registration?action=checkcode', {code:conf_input.val(), phone:phone.val()}, function(data) {
        // console.log(data);
        if(data.result==1){
            // код верный
            if($('#auth-modal').data('change-phone')==0) {
                conf_input.val('');
                conf_load.hide();
                conf_block.hide();
                pass_block.show();
                hint.html('пожалуйста введите пароль').show();
                input_password.focus();
                // меняем текст если нужно
                change_reg_texts();
            }else{
                $('#auth-modal').modal('hide');
                var phone = $('#auth-phone').val().replace(/[^0-9]/g,'');
                $('#lk-phone-number').html(phone);
                $('#lk-success-alert').show().html('Номер телефона успешно изменен');
                $.post('/ajax/change_personal', {type:'phone', data:phone});
            }
        }else{
            conf_load.hide();
            error.show();
            error.html(data.text);
        }
    }, "json");
}


function check_conf_code_order() {
    var load = $('#co-loading');
    load.show();
    //var conf_block = $('#confirm-phone-block');
    //var pass_block = $('#reg-password-block');
    var conf_input = $('#conf-code-order');
    var phone = $('#order-phone');
    //var input_password = $('#reg-password');
    var error = $('#co-resend-hint');
    var hint = $('#co-hint');

    error.hide();

    // проверка кода
    $.post('/ajax/check_order_conf', {code:conf_input.val(), phone:phone.val()}, function(data) {
        console.log(data);
        //return;
        if(data.result==1){
            // код верный
            //$('#order-confirmation-modal').modal('hide');
            run_order($('.btn-order'));
        }else{
            load.hide();
            error.html(data.text).show();
        }
    }, "json");
}

function check_conf_code_details_order(){
    var load = $('#details-loading');
    load.show();

    var conf_input = $('#conf-code-details-order');
    var phone = $('#order-phone');

    var error = $('#details-error');
    var hint = $('#details-hint');
    error.hide();

    // проверка кода
    $.post('/ajax/check_details_order_conf', {code:conf_input.val(), phone:phone.val()}, function(data) {
        console.log(data);
        //return;
        if(data.result==1){
            // код верный
            load.hide();
            $('#confirm-phone-block').hide();
            $('.details-items').show();
            $('#order-result').val(data.result);
        }else{
            load.hide();
            error.html(data.text).show();
        }
    }, "json");
}

function check_conf_code_cancel_order(){
    var load = $('#cancels-loading');
    load.show();

    var conf_input = $('#conf-code-cansel-order');
    var phone = $('#order-phone');
    var order = $('#order-id').val();
    var lk_order = $('#lk-order-id').val();

    var error = $('#cancels-error');
    var hint = $('#cancel-hint');
    error.hide();

    // проверка кода
    $.post('/ajax/check_cancels_order_conf', {code:conf_input.val(), phone:phone.val()}, function(data) {
        console.log(data);
        //return;
        if(data.result==1){
            // код верный
            load.hide();
            $('#confirm-cancel-block').hide();
            $('#cancel-result').val(data.result);
            cancel_orders(lk_order, data.result);
        }else{
            load.hide();
            error.html(data.text).show();
        }
    }, "json");
}

// подтверждение телефона
function confirm_phone(first=0) {
    $('#auth-loading').show();
    $('#auth-error').hide();
    $('#conf-code').val('').focus();
    // цель ЯМ
    if(typeof ym !== 'undefined') {
        ym($('#yandex_counter').val(), 'reachGoal', 'my-sendcode');
    }

    // запуск reCaptha
    run_recaptcha('phone_confirmation', first);
}

// подьверждение просмотра деталей заказа
function confirm_details_order(first=0) {
    $('#confirm-phone-block').show();
    $('.confirm').hide();

    // запуск reCaptha
    run_recaptcha('details_order', first);
}

// подьверждение отказа заказа
function confirm_cancel_order(first=0) {
    $('#confirm-cancel-block').show();
    $('.confirm-cancel').hide();

    // запуск reCaptha
    run_recaptcha('cancel_order', first);
}

// отправка кода повторно
function resend_code() {
    var load = $('#auth-loading');
    load.show();
    $('#resend-hint').hide();
    $('#error-code-hint').hide();
    var first = 0;

    // запуск reCaptha
    run_recaptcha('phone_reconfirmation', first);
}

// auth-phone заполнен
function auth_phone_complete(){
    var input = $('#auth-phone');
    var input_password = $('#auth-password');
    var input_login = $('input[name="hidden_login"]');
    var hint = $('#auth-hint');
    var auth_block = $('#auth-block');
    var load = $('#auth-loading');
    var reg_block = $('#reg-block');
    var reg_pblock = $('#reg-password-block');
    load.show();
    input_password.val('');
    input_login.val('');



    hint.removeClass('error-color');
    auth_block.hide();
    reg_block.hide();
    reg_pblock.hide();

    $('#confirm-phone-block').hide();
    $('#confirm-phone').show();

    $.post('/ajax/check_user', {phone:input.val()}, function(data) {
        if(data=='auth'){
            if($('#auth-modal').data('change-phone')==0){
                auth_block.show();
                hint.html('для авторизации, пожалуйста введите пароль').show();
                input_login.val(input.val());
                input_password.focus();
            }else{
                $('#auth-error').addClass('mt-0').html('этот номер уже зарегистрирован на сайте').show();
                hint.hide();
            }
        }
        if(data=='reg'){
            if($('#auth-modal').data('change-phone')==0){
                hint.show().html('пользователь с таким телефоном не найден, для регистрации подтвердите номер');
                reg_block.show();
            }else{
                $('#auth-error').hide();
                hint.show().html('для продолжения подтвердите номер');
                reg_block.show();
            }
        }
        if(data=='error'){
            input.val('+7');
            hint.html('ошибка сервера, попробуйте ввести номер телефона еще раз');
            hint.addClass('error-color');
            hint.show();
        }
        load.hide();
    });
}

function auth_phone_incomplete(){
    var load = $('#auth-loading');
    var hint = $('#auth-hint');
    var auth_block = $('#auth-block');
    var reg_block = $('#reg-block');
    var input_password = $('#auth-password');

    $('#confirm-phone-block').hide();
    $('#confirm-phone').show();

    auth_block.hide();
    reg_block.hide();
    load.hide();
    if($('#auth-modal').data('change-phone')==0){
        hint.show().html('пожалуйста введите свой номер телефона, что-бы авторизоваться или зарегистрироваться');
    }else{
        $('#auth-error').hide();
        hint.show().html('пожалуйста введите новый номер телефона');
    }
    input_password.val('').removeClass('m_error');
}

function order_phone_complete(){
    var load = $('#ofi-loading').show();
    load.show();
    var input = $('#order-phone');
    var hint = $('#ofi-hint');
    $.post('/ajax/check_user', {phone:input.val()}, function(data) {
        if(data=='auth'){
            //hint.html('<a href="#auth-modal" data-toggle="modal">авторизуйтесь</a> для получения/списания баллов').show();
            hint.html('<a href="#auth-modal" data-toggle="modal">авторизуйтесь</a>').show();
        }else{
            //hint.html('<a href="#auth-modal" data-toggle="modal">зарегистрируйтесь</a> для получения/списания баллов').show();
            hint.html('<a href="#auth-modal" data-toggle="modal">зарегистрируйтесь</a>').show();
        }
        load.hide();
    });
}

function order_phone_incomplete(){
    $('#ofi-hint').hide();
}

// анимация модального окна
$(".modal").each(function(l){$(this).on("show.bs.modal",function(l){var o=$(this).attr("data-easein");"shake"==o?$(".modal-dialog").velocity("callout."+o):"pulse"==o?$(".modal-dialog").velocity("callout."+o):"tada"==o?$(".modal-dialog").velocity("callout."+o):"flash"==o?$(".modal-dialog").velocity("callout."+o):"bounce"==o?$(".modal-dialog").velocity("callout."+o):"swing"==o?$(".modal-dialog").velocity("callout."+o):$(".modal-dialog").velocity("transition."+o)})});

function cityopen(elem){
    $(elem).hide();
    $('.find-show').show();
    $('.head-menu-line form').addClass('width-30');
    $('.head-menu-line form input').focus();
    $('.menu').addClass('width-70');
}

function delivery_change(type, elem, nm=0){
    // nm - флаг, что было подтверждение о смене цен из модалки

    var ul = $('#change-delivery-btns').closest('ul');
    var li = ul.find('li');
    var gpd = Number($('#g-pickup-discount').val());

    if(!type){
        // изменение доставки из модалки (подтверждение)
        var type = $(elem).data('type');
    }else{
        // клик по активному табу
        var this_type = ul.find('li.active').data('type');
        if(this_type==type){
            return;
        }
    }

    // проверяем, нет ли ограничения на самовывоз
    var wt_pickup = $('#wt-pickup').val();
    if(wt_pickup == "1" && type == "1"){
        $('#wt-pickup-modal').modal('show');
        return;
    }

    // проверяем, если есть промокод с условием доставки и оно не выполняется, показываем текст в модалке
    var promo_delivery = $('body').data('promo-delivery');
    if(type != promo_delivery && promo_delivery>0 && !nm){
        // запускаем модалку
        $('#conf-change-delivery').data('type', type).attr('data-type', type);
        $('#change_delivery .mctxctx p').hide();
        $('#change_delivery .mctxctx p.clear_promo_text').show();
        $('#change_delivery .modal-title').html('Удаление промокода');
        $('#change_delivery').modal('show');
        return;
    }

    var cartsum = Number($('#cart-total-price').data('price'));
    if(cartsum>0 && !nm && gpd>0){
        // проверяем, есть ли промокод с минималкой

        // отключаем проверку на минимальную сумму при добавлении промокода
        // var promo_min_sum = Number($('body').data('promo-min-sum'));
        var promo_min_sum = 0;

        var pickup_discount_p = Number($('#g-pickup-discount-p').val());
        // если есть минимальная сумма и переключаемся на самовывоз и установлен процент скидки за самовывоз, считаем возможную скидку
        if(promo_min_sum && type==1 && pickup_discount_p){
            // возможная скидка
            var pd = 0;
            $('#cart-products section').each(function(){
                var price = Number($(this).find('.price').data('dd-price'))*Number($(this).find('.cart-cnt-line span').html());
                pd += price;
            });
            // учитываем баллы
            var points = Number($('#g-points').val());
            var max_points = Math.floor(pd/2);
            if(points > max_points){
                points = max_points;
            }
            pd = pd - points;
            // учитываем баллы
            if(pd<promo_min_sum){
                // добавлям в модалку текст об удалении промокода
                var promo_title = $('.modal-title-promo').html();
                var mctxctx = $('#change_delivery .mctxctx');
                if(!mctxctx.find('.clear_promo_text').length){
                    mctxctx.append('<p class="clear_promo_text">Промокод <strong class="text-uppercase">'+promo_title+'</strong> будет удален, т.к. условие минимальной суммы заказа ('+promo_min_sum+' руб.) будет не выполнено.</p>');
                }else{
                    mctxctx.find('.clear_promo_text').show();
                }
            }
        }

        // добавляем проверку, если сумма с учетом промокода будет меньше 0
        var p_type = $('#g-promo-type').val();
        var p_discount_type = $('#g-promo-discount-type').val();
        var p_value = Number($('#g-promo-discount').val());
        if(type==1 && pickup_discount_p && p_type=="1" && p_discount_type=="0"){

            // возможная скидка
            var pd = 0;
            $('#cart-products section').each(function(){
                var price = Number($(this).find('.price').data('dd-price'))*Number($(this).find('.cart-cnt-line span').html());
                pd += price;
            });
            // учитываем баллы
            var points = Number($('#g-points').val());
            var max_points = Math.floor(pd/2);
            if(points > max_points){
                points = max_points;
            }
            pd = pd - points;
            // учитываем скидку по промокоду
            pd = pd - p_value;

            if(pd<1){
                // добавлям в модалку текст об удалении промокода
                var promo_title = $('.modal-title-promo').html();
                var mctxctx = $('#change_delivery .mctxctx');
                if(!mctxctx.find('.clear_promo_text').length){
                    mctxctx.append('<p class="clear_promo_text">Промокод <strong class="text-uppercase">'+promo_title+'</strong> будет удален, очень маленькая сумма заказа.</p>');
                }else{
                    mctxctx.find('.clear_promo_text').show();
                }
            }
        }

        // запускаем модалку
        $('#conf-change-delivery').data('type', type).attr('data-type', type);
        $('#change_delivery').modal('show');
        return;
    }

    if(nm){
        // закрываем модалку
        $('#change_delivery').modal('hide');
    }

    li.removeClass('active');
    $("#delivery_"+type).addClass('active');
    var load = $('#d-loading');
    load.show();

    // показываем инфо о невыбранных подарках
    var a = window.location.href.indexOf('/checkout/order');
    var gift_check = $('#picked-deliv-gifts').val();
    var deliv_gift_incart = $('#deliv-gift-incart').val();
    if(a>-1 && gift_check==1 && type==1){
        $('#gift-notification-modal').modal('show');
    }
    if(a>-1 && deliv_gift_incart==1 && type==2){
        $('#del-deliv-gift').modal('show');
        $('#deliv-gift-incart').val('0');
    }

    // отправляем значение доставки в php
    $.post('/ajax/delivery_change', {type:type,change_cart:nm}, function(data) {

        var wrap = $('#products-wrap');
        if (!wrap.length) {
            wrap = $('#ch-wrap');
        }
        if (!wrap.length) {
            wrap = $('#cho-wrap');
        }
        var url = wrap.data('url');
        wrap.addClass('opacity');

        // удаление промокода
        if(data.del_promo) {
            cancel_promo();
        }

        if(nm){
            // запускаем функцию пересчета корзины
            refresh_cart(data.newcart, data.total, data.count, data.header_cart_total_price);
            // меняем значение points, если необходимо
            if($('#points-em').length){
                number_animation($('#points-em'), $('#points-em').html(), data.points);
            }
            $('#g-points').val(data.points);

            $('#g-promo-discount').val(data.discount_promocode);
        }

        // меняем g-pickup-discount
        $('#g-pickup-discount').val(data.pickup_discount);
        $('#cart-total-price').data('discount-price', data.cart_discount_price).attr('data-discount-price', data.cart_discount_price).html(data.header_cart_total_price);

        // если установили доставку, удаляем подарки за самовывоз
        if(type!=1){
            remove_gifts('gift_pickup');
        }

        // обнуляем параметры загруженных товаров
        if($('#products-wrap').length){
            var wrp = $('#products-wrap');
            wrp.data('loadmore', 1).attr('data-loadmore', 1);
            wrp.data('start', 8).attr('data-start', 8);
        }

        $.post('/ajax/'+url, function(d) {
            wrap.html(d);
            load.hide();
            wrap.removeClass('opacity');

            if($('#cho-wrap').length){
                address_sug();
                zebra_cho_run();
            }
        });
    }, "json");

}

function address_sug(){
    $("#address-street").suggestions({
        token: $('body').data('dadata'),
        type: "ADDRESS",
        count: 6,
        bounds: "street-house",
        constraints: {
            label: "",
            // ограничиваем поиск городам
            locations: {
                region: $('body').data('region'),
                //city: $('body').data('city')
            },
            // даем пользователю возможность снять ограничение
            deletable: false
        },
        // в списке подсказок не показываем область
        restrict_value: true,
        // фильтруем выборку значений
        //onSuggestionsFetch: filterAddress,
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: check_address,
        onSelectNothing: selectNone
    });
}

function refresh_cart(cart_list='', cart_total='', count='', header_cart_total_price='') {
    var cart_wrap = $('.cart-wrap');
    var cart_block = $('#cart-products');
    var cart_total_price = $('#cart-total-price');
    var cart_count = $('#cart-total-count');

    if(cart_list){
        cart_total_price.data('price', cart_total).attr('data-price', cart_total).html(cart_total);
        cart_block.html(cart_list);

        cart_count.data('count', count).attr('data-count', count);
        $('#xs-cart-count').html(count);
        number_animation(cart_count, cart_count.data('value'), header_cart_total_price);
        cart_count.data('value', header_cart_total_price).attr('data-value', header_cart_total_price);
    }
}

function tgl_composition(elem){

    var wrap = $(elem).closest('.js-product');
    var block = wrap.find('.p-cb-wrap');
    var ps = wrap.find('.p-ps-wrap');
    var btn = wrap.find('.p-composition');
    if(block.is(':hidden')){
        block.show();
        btn.addClass('active');
    }else{
        block.hide();
        btn.removeClass('active');
    }
    ps.hide();
}
function close_composition(elem){
    var block = $(elem).closest('.p-cb-wrap');
    var wrap = $(elem).closest('.p-info');
    var btn = wrap.find('.p-composition');
    block.hide();
    btn.removeClass('active');
}


function property_select(elem, id){
    $('#other-modal').modal('show');
    $.post('/ajax/other_modal', {id:id}, function(data) {
        $('#other-modal .modal-body').html(data);
    });
}

function product_cart(elem, id){
    $('#other-modal').modal('show');
    $.post('/ajax/product_cart', {id:id}, function(data) {
        $('#other-modal .modal-body').html(data);
    });
}

function close_ps(elem){
    var block = $(elem).closest('.p-ps-wrap');
    var wrap = $(elem).closest('.js-product');
    var p_block = wrap.find('.property_list');
    var chs = wrap.find('input[type=radio]');
    var ch = p_block.find('input[data-default=1]:first');
    var span_price = wrap.find('.ac-price span');
    var input_price = wrap.find('input[name=price]');
    var input_property = wrap.find('input[name=property]');
    var pv_info = $('.js-pv-info');


    //chs.prop('checked', false);
    //ch.prop('checked', true);
    //span_price.html(ch.data('price'));
    //input_price.val(ch.data('price'));
    //input_property.val(ch.val());

    block.hide();
    pv_info.show();
}


function tgl_citylist(elem){
    var wrap = $(elem).closest('.nav-mobile');
    var block = wrap.find('.nm-citylist');
    if(block.is(':hidden')){
        block.show();
    }else{
        block.hide();
    }
}

// диалоговое окно удаления адреса
function del_address(elem, id){
    event.preventDefault();
    if(confirm("Вы действительно хотите удалить этот адрес?")) {
        $(elem).closest('li').remove();
        $.post('/ajax/del_address', {id:id});
    }
}

// диалоговое окно сохранения дня рождения
function show_birthday_alert(elem){
    event.preventDefault();
    if(confirm("Вы действительно хотите сохранить эту дату рождения? Изменить дату рождения в будущем, невозможно.")) {
        save_lk_info(elem, 'birthday');
    }
}

function lk_change(elem){
    event.preventDefault();
    var block = $(elem).closest('.lk-pers-data');
    var edit_block = block.find('.lk-edit-block');
    var info_block = block.find('.lk-pers-info');

    info_block.hide();
    edit_block.show();
    edit_block.find('input').focus();
}

function save_lk_info(elem, type){
    var block = $(elem).closest('.lk-edit-block');
    var wrap = $(elem).closest('.lk-pers-data');
    var input = block.find('input');
    var load = wrap.find('.load');
    var info = wrap.find('.lk-pers-info');

    if(input.length){
        var val = input.val();
        var word = input.val();
    }else{
        var select = block.find('select');
        var val = select.val();
        var word = block.find('select option:selected').text();
    }

    block.hide();
    load.show();

    $.post('/ajax/change_personal', {type:type, data:val}, function(data) {
        console.log(data);
        load.hide();
        $('#lk-error-alert').hide();
        if(val && val!=0){
            if(type=='birthday'){
                info.html('<span title="День рождения изменить невозможно">'+word+'</span>');
            }else if(type=='email' && data!='nochangeemail' && data!='novalidemail' && data!='dbyesemail'){
                info.html('<a href="#" onclick="lk_change(this)" title="Редактировать"><span class="gray">'+word+'</span><i title="E-mail не подтвержден" class="mdi mdi-lock-clock"></i><i class="mdi mdi-pencil-outline"></i></a>');
                $('#conf-email-modal').modal('show');

                // ЯМ цель
                if(typeof ym !== 'undefined') {
                    ym($('#yandex_counter').val(), 'reachGoal', 'subscribe-done');
                }

            }else if(data=='novalidemail'){

                var email = wrap.find('a span').html();
                if(email=='Не указано') email = '';

                input.val(email);
                $('#lk-error-alert').html('E-mail введен некорректно').show();
                info.show();
            }else if(data=='dbyesemail'){
                var email = wrap.find('a span').html();
                if(email=='Не указано') email = '';

                input.val(email);
                $('#lk-error-alert').html('Указаный E-mail уже существует').show();
                info.show();
            }else{
                info.html('<a href="#" onclick="lk_change(this)" title="Редактировать"><span>'+word+'</span><i class="mdi mdi-pencil-outline"></i></a>');
            }
        }else{
            info.html('<a href="#" onclick="lk_change(this)" title="Редактировать"><span class="gray">Не указано</span><i class="mdi mdi-pencil-outline"></i></a>');
        }
        info.show();
    });
}

// редактирование телефона из ЛК
function change_phone() {
    var modal_block = $('#auth-modal');

    modal_block.find('.modal-header h4').html('Редактирование телефона');
    modal_block.find('.modal-body small#auth-hint').html('пожалуйста введите новый номер телефона');

    modal_block.data('change-phone', 1).attr('data-change-phone', 1);
    modal_block.data('change-password', 0).attr('data-change-password', 0);

    modal_block.modal('show');
}

// редактирование пароля из ЛК
function change_password() {
    var modal_block = $('#auth-modal');
    var phone = $('#lk-phone-number').html();

    modal_block.find('.modal-header h4').html('Сменить пароль');
    $('#auth-phone').val(phone.slice(1)).prop('readonly', true);
    // метка что восстановление пароля
    $('#forgot-password').val('1');
    change_reg_texts();
    $('#reg-block').show();
    $('#reg-password-block').show();
    $('#auth-hint').html('для смены пароля, заполните поля ниже');
    $('#confirm-phone').hide();
    $('#reg-password').focus();

    $.post('/ajax/set_reg_phone', {action:'set_reg_phone'});

    modal_block.data('change-phone', 0).attr('data-change-phone', 0);
    modal_block.data('change-password', 1).attr('data-change-password', 1);

    modal_block.modal('show');
}

// Показать еще
function cab_loadmore(elem){
    var btn = $(elem);

    var table = btn.data('table');
    var limit = btn.data('limit');
    var total = btn.data('total');

    var load = btn.closest('.loadmore-wrap').find('img');

    btn.hide();
    load.show();
    $.post('/ajax/get_more', {table:table, limit:limit, total:total}, function (data) {

        $('#'+table+'-block').append(data.result);

        if(data.more == 1){
            btn.data('total', data.total).attr('data-total', data.total).show();
        }
        load.hide();
    }, "json");
}

// dadata
function check_address(suggestion){

    var hint = $(this).closest('.dadata-wrap').find('.hint');
    hint.html('').removeClass('error').hide();
    $(this).removeClass('error').removeClass('success');
    var city = $('body').data('code');
    var btn = $(this).closest('.dadata-wrap').find('button');
    var input_lat = $(this).closest('.dadata-wrap').find('input[name="lat"]');
    var input_lon = $(this).closest('.dadata-wrap').find('input[name="lon"]');
    var load = $('#address-loading');

    // для страницы оформления заказа
    var order_wrap = $('#cho-wrap');
    var delivery_price = $('#cho-delivery-price');
    var btn_order = $('.btn-order');
    var order_price = $('.cho-receipt-itogo span.sum');
    var pay_block = $('#cho-pay');
    var receipt_block = $('#cho-receipt');
    var price = Number(order_price.data('discount-price'));
    // флаг, что действия на странице заказа
    var order_flag = 0;
    if(delivery_price.length){
        order_flag = 1;
    }


    load.show();
    btn.prop('disabled', true);

    //console.log(suggestion.data);

    if(suggestion.data.house){
        $(this).addClass('success');

        var adr = suggestion.data.country+', '+suggestion.data.region_with_type+', ';
        if(suggestion.data.city_with_type){
            adr += suggestion.data.city_with_type+', ';
        }
        if(suggestion.data.settlement_with_type){
            adr += suggestion.data.settlement_with_type+', ';
        }
        if(suggestion.data.street_with_type){
            adr += suggestion.data.street_with_type;
            if(suggestion.data.history_values != null){
                adr += ' (бывш. '+suggestion.data.history_values[0]+')';
            }
            adr += ', ';
        }
        adr += suggestion.data.house_type+' '+suggestion.data.house;

        var small_adr = '';
        if(suggestion.data.city_with_type){
            small_adr += suggestion.data.city_with_type+', ';
        }
        if(suggestion.data.settlement_with_type){
            small_adr += suggestion.data.settlement_with_type+', ';
        }
        if(suggestion.data.street_with_type){
            small_adr += suggestion.data.street_with_type+', ';
        }

        small_adr += suggestion.data.house_type+' '+suggestion.data.house;
        if(suggestion.data.block){
            small_adr += '/'+suggestion.data.block;
        }

        console.log('suggestion.data');
        console.log(suggestion.data);
        console.log('small_adr');
        console.log(small_adr);
        // console.log('adr');
        // console.log(adr);
        // console.log('suggestion');
        // console.log(suggestion);

        $.post("/ajax/check_zone", {adr: adr, small_adr: small_adr, ll: suggestion.data.geo_lon+','+suggestion.data.geo_lat, order_flag: order_flag}, function(data){
            console.log(data);
            if(data.result=='zone_error'){
                btn.prop('disabled', true);
                $('#address-street').addClass('error').removeClass('success');
                // пишем ошибку, не входит в зону
                //hint.show().addClass('error').html('К сожалению, этот адрес не найден в нашей зоне доставки. Пожалуйста укажите другой адрес. '+data.coords);
                hint.show().addClass('error').html('К сожалению, этот адрес не найден в нашей зоне доставки.<br>Уверены, что адрес входит в зону доставки? Cледуйте указаниям <a href="#address-instruction" data-toggle="modal">этой инструкции</a>. ');
                //console.log(data.coords);
                input_lat.val('');
                input_lon.val('');

                if(order_flag){
                    // действия на странице оформления заказа
                    // ставим метку, что зона не определена
                    order_wrap.data('zone', 0).attr('data-zone', 0);

                    // ставим кнопку disabled
                    btn_order.prop('disabled', true);
                    $('#delivery-time').hide();
                    delivery_price.html('-');
                    // общая сумма заказа
                    number_animation(order_price.find('em'), order_price.data('order-price'), price);
                    delivery_price.data('order-price', price).attr('data-order-price', price);
                }

            }else{
                btn.prop('disabled', false);
                $('#address-street').addClass('success').removeClass('error');
                // пишем инфо о зоне
                hint.show().html(data.f_title+'<button class="btn btn-link" onclick="show_details();">подробнее</button>');
                //console.log(data);

                var coords = data.coords.split(',');
                input_lat.val(coords[0]);
                input_lon.val(coords[1]);

                if(order_flag){
                    // действия на странице оформления заказа

                    // ставим метку, что зона определена
                    order_wrap.data('zone', 1).attr('data-zone', 1);

                    // считаем стоимость доставки (возможно бесплатно)
                    var d_price = Number(data.price);
                    var free = Number(data.free);

                    if(free <= price){
                        // стоимоть доставки в чеке
                        delivery_price.html('<em style="color:#00a517;">бесплатно</em>');
                        delivery_price.data('delivery-price', 0).attr('data-delivery-price', 0);
                        d_price = 0;
                    }else{
                        // стоимоть доставки в чеке
                        delivery_price.html('<em>'+delivery_price.data('delivery-price')+'</em><i class="rub">q</i>');
                        number_animation(delivery_price.find('em'), delivery_price.data('delivery-price'), d_price);
                        delivery_price.data('delivery-price', d_price).attr('data-delivery-price', d_price);
                    }

                    // общая сумма заказа
                    var new_price = price + d_price;
                    number_animation(order_price.find('em'), order_price.data('order-price'), new_price);
                    order_price.data('order-price', new_price).attr('data-order-price', new_price);

                    // убираем прозрачность
                    receipt_block.removeClass('opacity__');
                    pay_block.removeClass('opacity__');

                    // обновляем время при оформлении заказа ко времени
                    update_time_zome();

                    // меняем среднее времядоставки (если есть)
                    if(data.time){
                        $('#delivery-time').show();
                        if(data.time2){
                            $('#delivery-time em').html(data.time2+' мин.');
                        }else{
                            if(data.time3){
                                $('#delivery-time em').html(data.time3+' мин.');
                            }else{
                                $('#delivery-time em').html(data.time+' мин.');
                            }
                        }
                    }else{
                        $('#delivery-time').hide();
                    }

                    // проверяем минимальную сумму заказа
                    if(Number(data.min_order) > price){
                        order_wrap.data('min-sum', 0).attr('data-min-sum', 0);
                        $('#order-error').html('Минимальная сумма заказа, для Вашего адреса (без учета стоимости доставки): '+data.min_order+' руб.').show();
                        btn_order.prop('disabled', true);
                        $('html,body').stop().animate({ scrollTop: $('#scroll').offset().top }, 800);
                    }else{
                        order_wrap.data('min-sum', 1).attr('data-min-sum', 1);
                        $('#order-error').html('').hide();
                    }

                    // если выполнены все условия, убираем disabled
                    if(order_wrap.data('min-sum') && order_wrap.data('zone') && order_wrap.data('rules')){
                        btn_order.prop('disabled', false);
                    }
                }

            }
            load.hide();
        }, "json");
    }else{
        btn.prop('disabled', true);
        $(this).focus().addClass('error');
        hint.show().html('пожалуйста укажите <strong>номер дома</strong>').addClass('error');
        load.hide();
        input_lat.val('');
        input_lon.val('');
        $.ajax('/ajax/clear_zone');

        if(order_flag){
            // действия на странице оформления заказа

            // ставим метку, что зона не определена
            order_wrap.data('zone', 0).attr('data-zone', 0);
            // ставим кнопку disabled
            btn_order.prop('disabled', true);
            $('#delivery-time').hide();
            delivery_price.html('-');
            // общая сумма заказа
            number_animation(order_price.find('em'), order_price.data('order-price'), price);
            delivery_price.data('order-price', price).attr('data-order-price', price);
        }

    }
    selectedAddress = suggestion.data;
}

function selectNone() {
    selectedAddress = null;
    $(this).focus().addClass('error');
    var btn = $(this).closest('form').find('button');
    var hint = $(this).closest('form').find('.hint');

    hint.html('необходимо выбрать адрес <strong>из списка</strong>').addClass('error');
    btn.prop('disabled', true);
}

function change_address(id, address, title, floor, entrance, room_number, is_private) {
    event.preventDefault();

    var modal = $('#add-address-modal');
    var form = modal.find('form');
    var h = modal.find('h4');
    var hint = modal.find('.hint');
    var btn = form.find('button[type="submit"]');

    var input_edit = form.find('input[name="edit"]');
    var input_address = $(form).find('input[name="address"]');
    var input_room_number = $(form).find('input[name="room-number"]');
    var input_entrance = $(form).find('input[name="entrance"]');
    var input_floor = $(form).find('input[name="floor"]');
    var input_title = $(form).find('input[name="title"]');
    var input_is_private = $(form).find('input[name="is_private_cab"]');

    input_edit.val(id);
    input_address.val(address);
    input_room_number.val(room_number);
    input_entrance.val(entrance);
    input_floor.val(floor);
    input_title.val(title);
    if(is_private==1){
        $('.non_private_cab').hide();
        $('.non_private_cab').find('input').removeClass('required');
        input_is_private.prop('checked', true);
    }else{
        $('.non_private_cab').show();
        $('.non_private_cab').find('input').addClass('required');
        input_is_private.prop('checked', false);
    }
    h.html('Редактирование адреса');
    hint.html('для редактирования адреса, измените поля ниже');
    btn.prop('disabled', false);

    modal.modal('show');
}

// проверка, на минимальную сумму промокода
function check_promo_minsum(elem, check='') {
    var button = $(elem);
    var section = button.closest('section');

    var promo_min_sum = $('body').data('promo-min-sum');
    // оригинальная стоимость 1 блюда
    var price = Number(section.find('.price').data('price'));
    if(check=='remove'){
        // стоимость всех блюд, но нужно вернуть скидку, потому что подсчет от суммы без скидок
        var d = Number(section.find('.price b').data('tp-discount'));
        price = Number(section.find('.price b').data('price'))+d;
    }


    // если удаление на странице, где меняются баллы
    var points = Number($('#g-points').val());
    var cart_price = Number($('#cart-total-price').data('price')) - price;

    // учитываем баллы
    var max_points = Math.floor((cart_price)/2);
    if(points > max_points){
        points = max_points;
    }
    cart_price = cart_price - points;// если удаление на странице, где меняются баллы

    //console.log('cart_price '+cart_price);

    // если нужно вызвать предупреждение, открываем его
    if(promo_min_sum > cart_price && elem != '#tempid'){
        button.attr('id', 'tempid');
        if(check=='remove'){
            $('#del-promo-btns3').removeClass('none');
            $('#del-promo-btns').addClass('none');
        }
        $('#del-promo-modal').modal('show');
        return 1;
    }

    if(elem == '#tempid'){
        return 2;
    }

    return 3;
}

// проверка, на отрицательную сумму после промокода
function check_promo_zero(elem, check='') {
    var button = $(elem);
    var section = button.closest('section');

    // проверка что промокод это скидка в руб на весь заказ
    var p_type = $('#g-promo-type').val();
    var p_discount_type = $('#g-promo-discount-type').val();
    if(p_type != "1" || p_discount_type != "0"){
        return 3;
    }

    // оригинальная стоимость 1 блюда
    var price = Number(section.find('.price').data('price'));
    if(check=='remove'){
        // стоимость всех блюд, но нужно вернуть скидку, потому что подсчет от суммы без скидок
        var d = Number(section.find('.price b').data('tp-discount'));
        price = Number(section.find('.price b').data('price'))+d;
    }


    // если удаление на странице, где меняются баллы
    var points = Number($('#g-points').val());
    var cart_price = Number($('#cart-total-price').data('discount-price')) - price;

    // учитываем баллы
    var max_points = Math.floor((cart_price)/2);
    if(points > max_points){
        points = max_points;
    }
    cart_price = cart_price - points;// если удаление на странице, где меняются баллы

    //console.log('cart_price '+cart_price);

    // если нужно вызвать предупреждение, открываем его
    if(cart_price < 1 && elem != '#tempid'){
        button.attr('id', 'tempid');
        if(check=='remove'){
            $('#del-promo-btns3').removeClass('none');
            $('#del-promo-btns').addClass('none');
        }
        $('#del-promo-modal').modal('show');
        return 1;
    }

    if(elem == '#tempid'){
        return 2;
    }

    return 3;
}

// проверка, на удаление обязательного товара
function check_promo_required(elem, check) {
    var button = $(elem);
    var section = button.closest('section');

    var required = $('#g-promo-required-products').val();
    if(!required){
        return 3;
    }

    var id = section.attr('id').replace('ch_', '');
    var count = 1;
    if(check=='minuscart'){
        count = section.find('.cart-cnt-line span').html();
    }

    // проверяем, есть ли этот товар в списке обязательных
    var pos = required.indexOf('|'+id+'|');

    // если товар будет удаляться и он обязательный, показываем предупреждение
    if(pos != "-1" && count==1 && elem != '#tempid'){
        button.attr('id', 'tempid');

        $('.dpm-classic-text').addClass('none');
        $('.dpm-text2').removeClass('none');

        if(check!='minuscart'){
            $('#del-promo-btns3').removeClass('none');
            $('#del-promo-btns').addClass('none');
        }

        $('#del-promo-modal').modal('show');
        return 1;
    }

    if(pos != "-1" && count==1 && elem == '#tempid'){
        return 2;
    }

    return 3;
}

function get_percent_discount(full_price=''){
    // full_price - полная стоимость
    var percent = Number($('#g-promo-percent').val());
    var round = Number($('#g-promo-percent-round').val());
    var d = full_price/100*percent;
    // скидку округляем в меньшую сторону
    switch(round) {
        case 0:
            d = Math.floor(d);
            break;
        case 1:
            d = Math.floor(d/5) * 5;
            break;
        case 2:
            d = Math.floor(d/10) * 10;
            break;
    }
    return d;
}

function ec_click(name, id, price, list, category) {
    event.preventDefault();
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        'ecommerce': {
            'currencyCode': 'RUB',
            'click': {
                'actionField': {'list': list},
                'products': [{
                    'name': name,
                    'id': id,
                    'price': price,
                    'brand': '',
                    'category': category,
                    'variant': '',
                    'position': ''
                }]
            }
        },
        'event': 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Product Clicks',
        'gtm-ee-event-non-interaction': 'False',
    });
}

// добавление товара в корзину
function ec_addcart(name, id, price, category, cnt, list) {
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        'ecommerce': {
            'currencyCode': 'RUB',
            'add': {
                'actionField': {'list': list},
                'products': [{
                    'name': name,
                    'id': id,
                    'price': price,
                    'brand': '',
                    'category': category,
                    'variant': '',
                    'quantity': cnt
                }]
            }
        },
        'event': 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Adding a Product to a Shopping Cart',
        'gtm-ee-event-non-interaction': 'False',
    });

    // цель ЯМ
    if(typeof ym !== 'undefined') {
        ym($('#yandex_counter').val(), 'reachGoal', 'addcart');
    }

    // событие для пикселя
    if(typeof fbq !== 'undefined'){
        fbq('track', 'AddToCart');
    }
}

// удаление товара из корзины
function ec_delcart(name, id, price, category, cnt) {
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        'ecommerce': {
            'currencyCode': 'RUB',
            'remove': {
                'products': [{
                    'name': name,
                    'id': id,
                    'price': price,
                    'brand': '',
                    'category': category,
                    'variant': '',
                    'quantity': cnt
                }]
            }
        },
        'event': 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Removing a Product from a Shopping Cart',
        'gtm-ee-event-non-interaction': 'False',
    });
}

// шаг оформление заказа
function ec_checkout(arr){
    var obj = jQuery.parseJSON(arr);
    var p = new Array;
    $.each(obj,function(key,v){
        p.push({'name': v.name,'id': v.id,'price': v.price,'brand': '','category': '','variant': '','quantity': v.quantity});
    });

    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        'ecommerce': {
            'currencyCode': 'RUB',
            'checkout': {
                'actionField': {'step': 1},
                'products': p
            }
        },
        'event': 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Checkout Step 1',
        'gtm-ee-event-non-interaction': 'False',
    });

    // событие для пикселя
    if(typeof fbq !== 'undefined') {
        fbq('track', 'InitiateCheckout');
    }

    //console.log(p);
    //console.log(position);
}

function modal_page(u, f) {
    $.post("/ajax_page/"+u+'/', {check: 1}, function(data){
        var info_modal = $('#info-modal');
        info_modal.find('.modal-title').html(data.title);
        info_modal.find('.modal-body div').html(data.text);
        if(f){
            info_modal.find('.modal-content').addClass('fffopacity');
        }
        info_modal.modal('show');
    }, "JSON");
    event.preventDefault();
}

function ajax_slick_slider_products(){
    if(!$('.slider-products').length)return;
    const slider_init_productss = tns({
        container: '.slider-products',
        loop: true,
        items: 2,
        slideBy: 1,
        nav: false,
        autoplay: false,
        speed: 400,
        autoplayButtonOutput: false,
        mouseDrag: true,
        gutter: 10,
        controls: true,
        autoplayTimeout: 2000,
        autoplayHoverPause: true,
        responsive: {
            420: {
                items: 2,
                mouseDrag: true,
                gutter: 15,
            },
            760: {
                items: 3,
                mouseDrag: false,
                gutter: 15,
            },
            1200: {
                items: 4,
                mouseDrag: false,
                gutter: 15,
            }
        }

    });
}

function init_mobile_slider(){
    if(!$('.mobile-slider').length)return;
    const mobile_slider_init = tns({
        container: '.mobile-slider',
        loop: true,
        items: 2,
        slideBy: 1,
        nav: true,
        navPosition: "bottom",
        autoplay: true,
        speed: 900,
        autoplayButtonOutput: false,
        mouseDrag: true,
        gutter: 10,
        controls: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        responsive: {
            430: {
                items: 2,
                mouseDrag: true,
                gutter: 15,
            },
            760: {
                items: 3,
                mouseDrag: false,
                gutter: 15,
            },
            1200: {
                items: 4,
                mouseDrag: false,
                gutter: 15,
            }
        }

    });
}

function init_main_slider(){
    if(!$('.main-slider').length)return;
    const main_slider_init = tns({
        container: '.main-slider',
        loop: true,
        items: 1,
        slideBy: 1,
        nav: true,
        navPosition: "bottom",
        autoplay: true,
        speed: 900,
        autoplayButtonOutput: false,
        mouseDrag: true,
        gutter: 25,
        controls: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        responsive: {
            400: {
                items: 2,
                mouseDrag: true,
            },
            760: {
                items: 3,
                mouseDrag: false,
            },
            1200: {
                items: 4,
                mouseDrag: false,
            }
        }

    });
}

function lk_menu_link(elem){
    var lnk = $(elem).attr('href');
    var wrap = $(elem).closest('.lk-menu');
    var li = wrap.find('li');
    var lk_wrap = $('.lk-wrap');
    li.removeClass('active');
    $(elem).closest('li').addClass('active');
    history.pushState(null, null, lnk);
    event.preventDefault();

    $('footer').addClass('opacity0');
    console.log(lnk);
    lk_wrap.html('<img src="/images/loading.gif" style="width: 25px;margin: 0 auto;display: block;" alt="" />');
    $.post("/ajax"+lnk, {check: 1}, function(data){
        lk_wrap.html(data);
        $('footer').removeClass('opacity0');
    });
}

function shlk_cart_detail(e) {
    var ttl = $(e);
    var wrap = ttl.closest('.lk-o-lnk');
    var block = wrap.find('.lk-cart-detail');
    if(block.hasClass('show')){
        $('.lk-cart-detail').removeClass('show');
    }else{
        $('.lk-cart-detail').removeClass('show');
        block.addClass('show');
    }
}

function like(e, id){
    var block = $(e);
    var span = block.find('span');
    var cnt = Number(span.html());
    if(block.hasClass('liked')){
        block.removeClass('liked');
        span.html(cnt-1);
    }else{
        block.addClass('liked');
        span.html(cnt+1);
    }
    $.post("/ajax/likes", {id: id});
}

function drbtn(){
    var gift_dr_minsum = Number($('#g-gift_dr_minsum').val());
    var cp = Number($('#ch-cart-total-price').data('price'));

    // проверка на наличие промокода с антисочетаемостью
    if($('#g-promo-is-not-dr').val()==1){
        $('#dr-gift-er p.clserr').hide();
        $('#dr-gift-er p.temperr').show().html('Промокод <strong>'+$('.ch-promocode em').html().toUpperCase()+'</strong> не сочетается с акцией "Подарки на День рождения".');
        //$('#dr-gift-er').modal('show');
        return;
    }

    //if(gift_dr_minsum <= cp){
        $('#notdr-modal').modal({
            show: 'show',
            backdrop: 'static',
            keyboard: false
        });
    //}else{
        //$('#dr-gift-er').modal('show');
    //}
    event.preventDefault();
}

// Показать еще
function loadmore(){
    var wrap = $('#products-wrap');
    var loadmore = Number(wrap.data('loadmore'));
    if(!loadmore) return;

    var start = Number(wrap.data('start'));
    var load = $('.loadmore-img');
    var url = wrap.data('url');
    load.show();

    console.log("url "+url);
    console.log("start "+start);

    $.post('/ajax/'+url, {start:start}, function (data) {
        console.log(data);
        wrap.append(data.result);
        wrap.data('loadmore', data.loadmore).attr('data-loadmore', data.loadmore);
        wrap.data('start', data.start).attr('data-start', data.start);
        load.hide();
    }, "json");
}

// История роллов - тест
function quiz_history_rolls(){
    // считаем все вопросы
    var quest_cnt = $('.quiz-quest').length;
    $('#quiz-cnt').html('1/'+quest_cnt);
    // показываем первый вопросов
    $('.quiz-quest[data-complete="0"]:first').show();

    $('.quiz-btn').on('click', function() {
        var val = Number($(this).data('value'));
        var input = $('#quiz-result');
        var input_val = Number(input.val());
        var block = $(this).closest('.quiz-quest');

        input.val(input_val+val);
        //console.log(input_val+val);
        // показываем следующий вопрос
        block.data('complete', 1).attr('data-complete', 1);
        block.hide();
        if($('.quiz-quest[data-complete="0"]').length){
            // считаем остаток вопросов
            var ost = $('.quiz-quest[data-complete="1"]').length+1;
            $('#quiz-cnt').html(ost+'/'+quest_cnt);

            $('.quiz-quest[data-complete="0"]:first').show();
        }else{
            $('.quiz-finish').show();
            $('#quiz-cnt').hide();
            var v = Number($('#quiz-result').val());
            var check = 0;
            $('.quiz-res').each(function(i){
                var sv = Number($(this).data('s-value'));
                var fv = Number($(this).data('f-value'));

                if(v >= sv && v < fv){
                    $(this).show();
                    return false;
                }
            });

            // цель Метрика
            // ym(54403210, 'reachGoal', 'quizcomplete');
        }

    });
}

// Какой ты ролл - тест
function quiz(){
    // считаем все вопросы
    var quest_cnt = $('.quiz-quest').length;
    $('#quiz-cnt').html('1/'+quest_cnt);
    // показываем первый вопросов
    $('.quiz-quest[data-complete="0"]:first').show();

    $('.quiz-btn').on('click', function() {
        var val = Number($(this).data('value'));
        var input = $('#quiz-result');
        var input_val = Number(input.val());
        var block = $(this).closest('.quiz-quest');

        input.val(input_val+val);
        // показываем следующий вопрос
        block.data('complete', 1).attr('data-complete', 1);
        block.hide();
        if($('.quiz-quest[data-complete="0"]').length){
            // считаем остаток вопросов
            var ost = $('.quiz-quest[data-complete="1"]').length+1;
            $('#quiz-cnt').html(ost+'/'+quest_cnt);

            $('.quiz-quest[data-complete="0"]:first').show();
        }else{
            $('.quiz-finish').show();
            $('#quiz-cnt').hide();
            var v = Number($('#quiz-result').val());
            var check = 0;
            $('.quiz-res').each(function(i){
                if(check){
                    $(this).show();
                    return false;
                }
                if(v <= Number($(this).data('value'))){
                    check = 1;
                    var this_i = i+1;
                    // если варинат ответа последний
                    if(this_i==$('.quiz-res').length){
                        $(this).show();
                        return false;
                    }
                    // если варинат ответа последний
                    if(this_i==1 && v < Number($(this).data('value'))){
                        $(this).show();
                        return false;
                    }
                }
            });

            // ЯМ цель
            if(typeof ym !== 'undefined') {
                ym($('#yandex_counter').val(), 'reachGoal', 'quizcomplete');
            }
        }
    });
}

function gift_others(e){
    var btn = $(e);
    var wrap = btn.closest('.other-mb');
    var product_div = wrap.find('.js-product');
    var ch_cart = $('#ch-cart-wrap');
    var cart = $('#cart-products');
    var ids = '';
    var items = '';
    var types = '';
    product_div.each(function(){
        ids += $(this).data('product')+',';
        items += $(this).find('input[name=items]').val()+'-';
        types += $(this).find('input[name=type]').val()+',';
    });
    ids = ids.substring(0, ids.length - 1);
    items = items.substring(0, items.length - 1);
    types = types.substring(0, types.length - 1);

    $.post('/ajax/gift_others', {ids:ids, items:items, types:types}, function (data) {
        ch_cart.html(data.ch_cart);
        cart.html(data.cart);
        $('#other-modal').modal('hide');
    }, "JSON");

}

// отправка кода повторно
function resend_code_order() {
    var load = $('#co-loading');
    var phone = $('#order-phone')
    load.show();
    $('#co-resend-hint').hide();
    $('#co-error').hide();

    $.post('/ajax/resend_order_conf', {phone:phone.val()}, function (data) {
        load.hide();
        $('#co-resend-hint').html(data.text).show();
        if(data.sms == 1){
            $('.auth-hint').html('На указанный номер отправлено смс с кодом подтверждения');
        }
    }, "JSON");
}
// отправка кода повторно
function resend_code_order() {
    var load = $('#co-loading');
    var phone = $('#order-phone')
    load.show();
    $('#co-resend-hint').hide();
    $('#co-error').hide();

    $.post('/ajax/resend_order_conf', {phone:phone.val()}, function (data) {
        load.hide();
        $('#co-resend-hint').html(data.text).show();
        if(data.sms == 1){
            $('.auth-hint').html('На указанный номер отправлено смс с кодом подтверждения');
        }
    }, "JSON");
}
//cookies banner
function close_cookies_banner(e) {
    var wrap = $(e).closest(".cookies-banner");
    console.log("close");
    wrap.hide();
    $.post('/ajax/check_banner_cookies', {check: 1});
}

function numbers_input(evt){
    var Event = evt || windows.event;
    var key = Event.keyCode || Event.which;
    key = String.fromCharCode( key );
    var regex = /[0-9]|\./;
    if(!regex.test(key)){
        Event.returnValue = false;
        if(Event.preventDefault)Event.preventDefault();
    }
}

function change_image(input){
    var id = $(input).val();
    var n = $(input).attr('name');
    var img = $('#image');
    var block = $('.tableware_cart span');
    var name = n.split('_');
    if(name[0] == 'ppt'){
        $.post('/ajax/change_image', {id: id}, function(data){
            console.log(data);
            if(data != ''){
                var pic = data.split(',');
                if(pic.length > 1){
                    img.attr('src', pic[0]);
                    img.attr('alt', pic[1]);
                    if(pic[2] == 0){
                        $('.tableware_cart').hide();
                    }else{
                        $('.tableware_cart').show();
                    }
                    block.text(pic[2]);
                }else{
                    if(data == 0){
                        $('.tableware_cart').hide();
                    }else{
                        $('.tableware_cart').show();
                    }
                    block.html(data);
                }
            }
        });
    }
}

function in_time(){
    var this_day = $('#this-time').val();
    var next_day = $('#next-time').val();
    var pre_order_tomorrow = $('#pre_order_tomorrow').val();
    var pre_order_today = $('#pre_order_today').val();
    if($('#dopdate-in-cook').length){
        var dop_day = $('#dopdate-in-cook').val();
    }
    var select = $('select[name=days]');
    var select2 = $('select[name=clock]');
    select.empty();
    select.append('<option value="0" disabled>Дата</option>');
    if(pre_order_today != 1){
        select.append('<option value="' + this_day + '">' + this_day + '</option>');
    }
    if(pre_order_tomorrow != 1){
        select.append('<option value="' + next_day + '">' + next_day + '</option>');
    }
    if(dop_day.length){
        select.append('<option value="' + dop_day + '">' + dop_day + '</option>');
    }
    select2.empty();
    select2.append('<option value="0" disabled>Время</option>');
    $('select[name=days] option:first').prop('selected', true);
    $('select[name=clock] option:first').prop('selected', true);
}

function time_in_time(select){
    var sd = select.value;
    var select2 = $('select[name=clock]');
    //var d = new Date();
    //var this_day = d.toLocaleString();
    var this_day = $('#this-time').val();
    var next_day = $('#next-time').val();
    //d.setDate(d.getDate()+1);
    //var next_day = d.toLocaleString();
    if($('#dopdate-in-cook').length){
        var dop_day = $('#dopdate-in-cook').val();
    }
    //var td = this_day.split(',');
    //var nd = next_day.split(',');

    if(sd == 0){
        select2.empty();
        select2.append('<option value="0"> </option>');
    }
    if(sd == this_day){
        if($('#time-limit').length){
            var timelimit = $('#time-limit').val().split(',');
            var tlar = [];
            $.each(timelimit,function(index,value){
                tlar.push(value);
            });
        }else{
            var tlar = ['00:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00'];
        }
        select2.empty();
        $.each(tlar, function(key, value){
            select2.append('<option value="' + value + '">' + value + '</option>');
        });
    }
    if(sd == next_day){
        if($('#time-limit2').length){
            var timelimit2 = $('#time-limit2').val().split(',');
            var tlar2 = [];
            $.each(timelimit2,function(index,value){
                tlar2.push(value);
            });
        }else{
            var tlar2 = ['00:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00'];
        }
        select2.empty();
        $.each(tlar2, function(key, value){
            select2.append('<option value="' + value + '">' + value + '</option>');
        });
    }
    if(sd == dop_day){
        if(dop_day == this_day){
            if($('#time-limit').length){
                var timelimit = $('#time-limit').val().split(',');
                var tlar3 = [];
                $.each(timelimit,function(index,value){
                    tlar3.push(value);
                });
            }else{
                var tlar3 = ['00:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00'];
            }
        }else{
            if($('#time-limit3').length){
                var timelimit3 = $('#time-limit3').val().split(',');
                var tlar3 = [];
                $.each(timelimit3,function(index,value){
                    tlar3.push(value);
                });
            }else{
                var tlar3 = ['00:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00'];
            }
        }
        select2.empty();
        $.each(tlar3, function(key, value){
            select2.append('<option value="' + value + '">' + value + '</option>');
        });
    }
    select_time();
}

function select_time(){
    var days = $('select[name=days] option:selected').text();
    var time = $('select[name=clock] option:selected').text();
    var name = 'cook_time_value';
    if(days == "Дата" && time == "Время"){
        var val = '';
    }else{
        var val = days + ', ' + time;
    }
    $('#select_cook_time').val(val);
    $.post('/ajax/order_info', {name:name, val:val});

    if($('#delivery_1').hasClass('active')){
        if(time === '23:00'){
            $('#info-modal').modal('show');
            var title = "Информация";
            var txt = 'В связи с повышением мер безопасности по нераспространению коронавирусной инфекции, заказы на самовывоз осуществляется до 23:00. Необходимо забрать ваш заказ до этого времени.';
            $('.conf-email-mb').text(txt);
            $('.modal-title').text(title);
        }
    }

    $.post('/ajax/check_time_order', {time:val}, function(data){
        if(data == 'error'){
            $('#info-modal').modal('show');
            var title = "Внимание";
            var txt = 'На '+val+' оформлено много заказов. Пожалуйста укажите другую дату или время.';
            $('.conf-email-mb').text(txt);
            $('.modal-title').text(title);
            $('select[name=clock] option:last').prop('selected', true);
            clear_time();
        }
    });
}

function clear_time(){
    var name = 'cook_time_value';
    var val = '';
    $('#select_cook_time').val('');
    $.post('/ajax/order_info', {name:name, val:val});
}

function update_time_zome(){
    $.ajax({
        url: '/ajax/wtjsline',
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (data){
            console.log(data);
            if(data.length){
                $('#time-limit').val(data);
            }
        }
    });
    in_time();
    $('#select_cook_time').val('');
}

function show_details(){
    $.post('/ajax/show_details', function(data) {
        $('#info-modal').modal('show');
        var title = "Информация";
        var txt = data;
        $('.conf-email-mb').html(txt);
        $('.modal-title').text(title);
    });
}

function yandex_sug(){
    ymaps.ready(suggest);
}
function suggest(){
    var oundedby = $('#ya_oundedby').val();
    var ar = oundedby.replace('[[', '').replace(']', '').replace('[', '').replace(']]', '');
    var s = ar.split(',');
    var y_oundedby = [[s[0], s[1]],[s[2], s[3]]];
    var y_replace = $('#ya_replace').val();
    var city = $('#ya_city').val();

    //console.log(y_oundedby);
    //console.log(y_replace);

    var suggestView = new ymaps.SuggestView('address-street', {
        results: 15,
        container: document.getElementById("map"),
        boundedBy: y_oundedby,
        provider: {
            suggest: function (request, options) {
                delete options['provider'];
                return ymaps.suggest(request, options).then(items => {
                    let arrayResult = [];
                    let arrayPromises = [];

                    function pushGeoData(displayName, value, fa) {
                        arrayResult.push({displayName: displayName, value: value, fullAddress: fa});
                    }
                    var ii = 1;
                    items.forEach(i => {
                        var dn = i.displayName;
                        if(dn.indexOf(city) < 0) return;
                        // проверяем есть ли подъезд в адресе
                        if(dn.indexOf('подъезд ') > -1)return;

                        // убираем лишнее в подсказках
                        var tmpr = y_replace.split(',');
                        tmpr.forEach(rpls => {
                            dn = dn.replace(', '+rpls.trim(), '');
                        });
                        if(ii < 8) {
                            pushGeoData(dn, dn, i.value);
                            ii++;
                        }
                    });

                    return Promise.all(arrayPromises).then(function(){
                        return ymaps.vow.resolve(arrayResult);
                    });
                });
            }
        }
    });
    suggestView.events.add('select', function (e) {
        //console.log("click1");
        var adr = e.originalEvent.item.fullAddress;
        var input = document.getElementById('address-street');
        var input2 = document.getElementById('address-street2');
        input2.value = adr;
        $('#address-street').data('select', 1).attr('data-select', 1);
        setTimeout(() => $('#address-street').data('select', 0).attr('data-select', 0), 5000);
        input.blur();
        ya_check_address();
        //console.log("click2");
    });
}
function ya_check_address(){
    var adr = $('#address-street2').val();
    var smladr = $('#address-street').val();
    var btn = $('.btn-order');
    var hint = $('#address-hint');
    var delivery_price = $('#cho-delivery-price');
    var load = $('#address-loading');
    var address_street = $('#address-street');

    // флаг, что действия на странице заказа
    var order_flag = 0;
    if(delivery_price.length){
        order_flag = 1;
    }
    // флаг, что находится на странице узнать условия доставки
    var terms = $('#delivery-terms').val();

    load.show();
    btn.prop('disabled', true);
    if(adr == ''){
        load.hide();
        hint.html('Пожалуйста выберите адрес из выпадающего списка.').addClass('error').show();
        return;
    }
    // TODO: добавить обработчик зоны error
    $.post('/ajax/check_address', {adr: adr, smladr: smladr, order_flag: order_flag}, function(data){
        if(data.length){
            var result = JSON.parse(data);
            if(result['type'] == 'error-2'){
                load.hide();
                hint.html(result['text']).addClass('error').show();
                address_street.addClass('error').removeClass('success');
                if(terms == 1){
                    $('#t_address').html('');
                    $('#t_title').html('');
                    $('#t_price').html('');
                    $('#t_min').html('');
                    $('#t_free').html('');
                    $('#t_time').html('');
                }
            }else if(result['type'] == 'error-1'){
                geocheck_address();
            }else if(result['result'] == 'zone_error'){
                load.hide();
                console.log('zone_error');
                var text = 'К сожалению, этот адрес не найден в нашей зоне доставки. Пожалуйста укажите другой адрес.'
                hint.html(text).addClass('error').show();
                address_street.addClass('error').removeClass('success');
                if(terms == 1){
                    $('#t_address').html('');
                    $('#t_title').html('');
                    $('#t_price').html('');
                    $('#t_min').html('');
                    $('#t_free').html('');
                    $('#t_time').html('');
                }
            }else{
                if(terms != 1){
                    load.hide();
                    console.log(result);
                    hint.html(result['f_title']+'<button class="btn btn-link" onclick="show_details();">подробнее</button>').show().removeClass('error');
                    check_zone_true(result);
                    btn.prop('disabled', false);
                }else{
                    load.hide();
                    console.log(result);
                    hint.html('');
                    address_street.addClass('success').removeClass('error');
                    $('#t_address').html('Адрес: <strong>'+smladr+'</strong>');
                    $('#t_title').html('Зона: <strong>'+result['title']+'</strong>');
                    $('#t_price').html('Стоимость доставки: <strong>'+result['price']+' руб.</strong>');
                    $('#t_min').html('Минимальная сумма заказа: <strong>'+result['min_order']+' руб.</strong>');
                    $('#t_free').html('Бесплатная доставка от: <strong>'+result['free']+' руб.</strong>');
                    $('#t_time').html('Среднее время доставки: <strong>'+result['time']+' мин.</strong>');
                }
            }
        }
    });
}

function geocheck_address(){
    var adr = $('#address-street2').val();
    var smladr = $('#address-street').val();
    var hint = $('#address-hint');
    //var hint = $('.hint');
    var btn = $('.btn-order');
    var delivery_price = $('#cho-delivery-price');
    var load = $('#address-loading');
    var address_street = $('#address-street');

    // флаг, что действия на странице заказа
    var order_flag = 0;
    if(delivery_price.length){
        order_flag = 1;
    }
    // флаг, что находится на странице узнать условия доставки
    var terms = $('#delivery-terms').val();

    ya_check_geo(adr, smladr).then(resp=>{
        var crds = resp['coords'];
        var type = resp['type'];
        var smladr = resp['smladr'];

        $.post('/ajax/cache_address', {adr: adr, coords: crds, type: type, smladr: smladr, order_flag: order_flag}, function(data){
            var result = JSON.parse(data);
            if(result['type'] == 2){
                load.hide();
                var text = 'Неполный адрес. Укажите номер дома.'
                hint.html(text).addClass('error').show();
                address_street.addClass('error').removeClass('success');
                if(terms == 1){
                    $('#t_address').html('');
                    $('#t_title').html('');
                    $('#t_price').html('');
                    $('#t_min').html('');
                    $('#t_free').html('');
                    $('#t_time').html('');
                }
            }else if(result['result'] == 'zone_error'){
                load.hide();
                var text = 'К сожалению, этот адрес не найден в нашей зоне доставки. Пожалуйста укажите другой адрес.'
                hint.html(text).addClass('error').show();
                address_street.addClass('error').removeClass('success');
                if(terms == 1){
                    $('#t_address').html('');
                    $('#t_title').html('');
                    $('#t_price').html('');
                    $('#t_min').html('');
                    $('#t_free').html('');
                    $('#t_time').html('');
                }
            }else{
                if(terms != 1){
                    console.log(result);
                    load.hide();
                    hint.html(result['f_title']+'<button class="btn btn-link" onclick="show_details();">подробнее</button>').show().removeClass('error');
                    check_zone_true(result);
                    btn.prop('disabled', false);
                }else{
                    load.hide();
                    console.log(result);
                    hint.html('');
                    address_street.addClass('success').removeClass('error');
                    $('#t_address').html('Адрес: <strong>'+smladr+'</strong>');
                    $('#t_title').html('Зона: <strong>'+result['title']+'</strong>');
                    $('#t_price').html('Стоимость доставки: <strong>'+result['price']+' руб.</strong>');
                    $('#t_min').html('Минимальная сумма заказа: <strong>'+result['min_order']+' руб.</strong>');
                    $('#t_free').html('Бесплатная доставка от: <strong>'+result['free']+' руб.</strong>');
                    $('#t_time').html('Среднее время доставки: <strong>'+result['time']+' мин.</strong>');
                }
            }
        });
    });
}

function ya_check_geo(adr, smladr){
    var adr = adr;
    var smladr = smladr;

    var promise = new Promise(function(resolve, reject){
        ymaps.geocode(adr).then(function (res) {
            var r = {};
            var obj = res.geoObjects.get(0),
                error;
            if (obj) {
                // Об оценке точности ответа геокодера можно прочитать тут: https://tech.yandex.ru/maps/doc/geocoder/desc/reference/precision-docpage/
                switch (obj.properties.get('metaDataProperty.GeocoderMetaData.precision')) {
                    case 'exact':
                        break;
                    case 'number':
                    case 'near':
                    case 'range':
                        error = 'Неточный адрес, требуется уточнение';
                        break;
                    case 'street':
                        error = 'Неполный адрес, требуется уточнение';
                        break;
                    case 'other':
                    default:
                        error = 'Неточный адрес, требуется уточнение';
                }
            } else {
                error = 'Адрес не найден';
            }
            // Если геокодер возвращает пустой массив или неточный результат, то показываем ошибку.
            var coords = obj.geometry._coordinates[1]+','+obj.geometry._coordinates[0];
            if (error) {
                r = {
                    type: '2',
                    coords: coords,
                    smladr: smladr,
                };
            } else {
                r = {
                    type: '1',
                    coords: coords,
                    smladr: smladr,
                };
            }
            resolve(r);
        }, function(err){
            resolve(err);
        });
    });
    return promise;
}

function check_zone_true(result){
    var btn = $('#address-submit');
    var input_lat = $('input[name="lat"]');
    var input_lon = $('input[name="lon"]');
    // для страницы оформления заказа
    var order_wrap = $('#cho-wrap');
    var delivery_price = $('#cho-delivery-price');
    var btn_order = $('.btn-order');
    var order_price = $('.cho-receipt-itogo span.sum');
    var pay_block = $('#cho-pay');
    var receipt_block = $('#cho-receipt');
    var price = Number(order_price.data('discount-price'));

    // считаем стоимость доставки (возможно бесплатно)
    var d_price = Number(result.price);
    var free = Number(result.free);

    btn.prop('disabled', false);
    $('#address-street').addClass('success').removeClass('error');
    var coords = result.coords.split(',');
    input_lat.val(coords[0]);
    input_lon.val(coords[1]);

    if(free <= price){
        // стоимоть доставки в чеке
        delivery_price.html('<em style="color:#00a517;">бесплатно</em>');
        delivery_price.data('delivery-price', 0).attr('data-delivery-price', 0);
        d_price = 0;
    }else{
        // стоимоть доставки в чеке
        delivery_price.html('<em>'+delivery_price.data('delivery-price')+'</em><i class="rub">q</i>');
        number_animation(delivery_price.find('em'), delivery_price.data('delivery-price'), d_price);
        delivery_price.data('delivery-price', d_price).attr('data-delivery-price', d_price);
    }

    // общая сумма заказа
    var new_price = price + d_price;
    number_animation(order_price.find('em'), order_price.data('order-price'), new_price);
    order_price.data('order-price', new_price).attr('data-order-price', new_price);

    // убираем прозрачность
    receipt_block.removeClass('opacity__');
    pay_block.removeClass('opacity__');

    // обновляем время при оформлении заказа ко времени
    update_time_zome();

    // меняем среднее времядоставки (если есть)
    if(result.time){
        $('#delivery-time').show();
        if(result.time2){
            $('#delivery-time em').html(result.time2+' мин.');
        }else{
            if(result.time3){
                $('#delivery-time em').html(result.time3+' мин.');
            }else{
                $('#delivery-time em').html(result.time+' мин.');
            }
        }
    }else{
        $('#delivery-time').hide();
    }

    // проверяем минимальную сумму заказа
    if(Number(result.min_order) > price){
        order_wrap.data('min-sum', 0).attr('data-min-sum', 0);
        $('#order-error').html('Минимальная сумма заказа, для Вашего адреса (без учета стоимости доставки): '+result.min_order+' руб.').show();
        btn_order.prop('disabled', true);
        $('html,body').stop().animate({ scrollTop: $('#scroll').offset().top }, 800);
    }else{
        order_wrap.data('min-sum', 1).attr('data-min-sum', 1);
        $('#order-error').html('').hide();
    }

    // если выполнены все условия, убираем disabled
    if(order_wrap.data('min-sum') && order_wrap.data('zone') && order_wrap.data('rules')){
        btn_order.prop('disabled', false);
    }
}
function change_func_hint(id){
    $.post('/ajax/check_hints', {id: id}, function(data) {
        if(data.length){
            if(data == 1){
                // dadata
                change_order_address(id);
            }else{
                // yandex suggest
                change_hint_address(id);
            }
        }
    });
}
function change_hint_address(id){
    var adr = $('#address-street2');
    var hint = $('.adresses_list .hint');
    var btn = $('.btn-order');
    var delivery_price = $('#cho-delivery-price');
    var address_street = $('#address-street');

    // флаг, что действия на странице заказа
    var order_flag = 0;
    if(delivery_price.length){
        order_flag = 1;
    }

    $.post('/ajax/check_address', {address_id: id, order_flag: order_flag}, function(data) {
        if(data.length){
            var result = JSON.parse(data);
            if(result['type'] == 2){
                var text = 'Неполный адрес. Укажите номер дома.'
                hint.html(text).addClass('error').show();
                btn.prop('disabled', true);
                address_street.addClass('error').removeClass('success');
            }else if(result['result'] == 'zone_error'){
                var text = 'К сожалению, этот адрес не найден в нашей зоне доставки. Пожалуйста укажите другой адрес.'
                hint.html(text).addClass('error').show();
                btn.prop('disabled', true);
                address_street.addClass('error').removeClass('success');
            }else if(result['type'] == 'error-1') {
                adr.val(result['street']);
                geocheck_address();
            }else{
                console.log(result);
                hint.html(result['f_title']+'<button class="btn btn-link" onclick="show_details();">подробнее</button>').show().removeClass('error');
                check_zone_true(result);
                btn.prop('disabled', false);
            }
        }
    });
}

function find_orders(){
    var id = $('#order_status_id').val();
    var hint = $('#status-hint');
    var or_id = $('#or_id');
    var or_name = $('#or_name');
    var or_phone = $('#or_phone');
    var or_street = $('#or_street');
    var or_price = $('#or_price');
    var or_status = $('#or_status');
    console.log(id);

    if(id != ''){
        $.post('/ajax/find_orders', {id: id}, function(data){
            console.log(data);
            if(data.length){
                var result = JSON.parse(data);
                if(result['error']){
                    hint.html(result['error']).addClass('alert alert-danger');
                    or_id.html('');
                    or_name.html('');
                    or_phone.html('');
                    or_street.html('');
                    or_price.html('');
                    or_status.html('');
                    $('#order_status_id').addClass('error');
                }else{
                    hint.html('').removeClass('alert alert-danger');
                    $('#order_status_id').removeClass('error');
                    $('#order-phone').val(result['phone']);
                    $('#order-id').val(result['id']);

                    if(result['str_f'] == 3){
                        $('#check_order').addClass('status-icon-active');
                        $('#kitchen_order').removeClass('status-icon-active');
                        $('#courier_order').removeClass('status-icon-active');
                        $('#cancel_order').removeClass('status-icon-cancel');
                    }
                    if(result['str_f'] == 1){
                        $('#check_order').removeClass('status-icon-active');
                        $('#kitchen_order').addClass('status-icon-active');
                        $('#courier_order').removeClass('status-icon-active');
                        $('#cancel_order').removeClass('status-icon-cancel');
                    }
                    if(result['str_f'] == 2){
                        $('#check_order').removeClass('status-icon-active');
                        $('#kitchen_order').removeClass('status-icon-active');
                        $('#courier_order').addClass('status-icon-active');
                        $('#cancel_order').removeClass('status-icon-cancel');
                    }
                    if(result['str_f'] == 4){
                        $('#check_order').addClass('status-icon-active');
                        $('#kitchen_order').removeClass('status-icon-active');
                        $('#courier_order').removeClass('status-icon-active');
                        $('#cancel_order').removeClass('status-icon-cancel');
                    }
                    if(result['str_f'] == 5){
                        $('#check_order').removeClass('status-icon-active');
                        $('#kitchen_order').removeClass('status-icon-active');
                        $('#courier_order').removeClass('status-icon-active');
                        $('#cancel_order').addClass('status-icon-cancel');
                    }
                    $('#hint-message').html(result['str']);

                    if(result['str_f'] == 2 || result['str_f'] == 1){
                        $('.btn-check-status').show();
                        or_id.html('Номер заказа: <strong>'+result['id']+'</strong>');
                        or_name.html('Имя: <strong>'+result['name']+'</strong>');
                        or_phone.html('Телефон: <strong>'+result['phone']+'</strong>');
                        or_street.html('Адрес: <strong>'+result['street']+'</strong>');
                        or_price.html('Сумма: <strong>'+result['price']+'</strong>');
                        or_status.html('Статус: <strong>'+result['status']+'</strong>');
                    }else{
                        $('.btn-check-status').hide();
                    }

                    if(result['status'] == 'Заказ отменен' || result['status'] == 'Заказ доставлен'){
                        console.log(result['status']);
                        $('#cancel_btn_sts').prop( "disabled", true );
                    }else{
                        $('#cancel_btn_sts').prop( "disabled", false );
                    }
                }
                if(result == ''){
                    $('#check_order').removeClass('status-icon-active');
                    $('#kitchen_order').removeClass('status-icon-active');
                    $('#courier_order').removeClass('status-icon-active');
                    $('#cancel_order').removeClass('status-icon-cancel');
                    $('#hint-message').html('');
                }
            }
        });
    }else{
        var text = 'Введите номер заказа';
        hint.html(text).addClass('alert alert-danger');
        or_id.html('');
        or_name.html('');
        or_phone.html('');
        or_street.html('');
        or_price.html('');
        or_status.html('');
        $('#order_status_id').addClass('error');
    }
}

function cancel_orders(e, cnl){
    var mdl = $('#cansel-order');
    var btn_yes = $('#cansel_yes');
    var modal_title = $('.modal-title');
    var btn_cancel = $('.btn-cansel');
    var form_cancel = $('.form-cancel');
    var btn_send = $('#cansel_send');
    var confirm = $('.confirm-cancel');
    var cancel = cnl;
    var lk_order_id = $('#lk-order-id');
    mdl.modal('show');
    confirm.hide();
    lk_order_id.val(e);
    console.log("cnl: "+cnl);
    console.log("lk: "+lk_order_id.val());
    console.log('user_phone: '+$('#lk-phone-number').val());

    btn_yes.click(function(){
        var text = 'Напишите пожалуйста причину отказа';
        modal_title.html(text);
        btn_cancel.hide();
        form_cancel.show();
    });

    btn_send.click(function(){
        var cause = $('#rejection_reason').val();
        console.log('func: '+cause);
        if(cause != ''){
            form_cancel.hide();
            var text = 'Подтвердите отмену заказа';
            modal_title.html(text);
            confirm.show();
        }else{
            var text = 'Напишите причину отмены заказа';
            $('#page-danget-hint').html(text).addClass('alert alert-danger');
        }
    });

    if(cancel == 1){
        console.log('e '+lk_order_id.val());
        console.log('cancel '+cancel);
        mdl.modal('hide');
        var cause = $('#rejection_reason').val();
        if(lk_order_id.val() != 0){
            var id = lk_order_id.val();
            var hint = $('#status-sticker'+id);
            var cl = $('#status-sticker'+id).attr('class').split(' ')[1];
            var alert = $('#lk-danget-hint');
            var lk = 1;

            if(id != ''){
                $.post('/ajax/cancel_orders', {id: id, str: cause, lk: lk}, function(data){
                    if(data.length){
                        var res = JSON.parse(data);
                        if(res['error']){
                            alert.html(res['error']).addClass('alert alert-danger');
                        }else{
                            hint.html(res['status']).removeClass(cl).addClass('b-gray');
                            alert.html('').removeClass('alert alert-danger');
                        }
                    }
                });
            }
        }else{
            var id = $('#order_status_id').val();
            var hint = $('#status-hint');
            var or_status = $('#or_status');

            if(id != ''){
                $.post('/ajax/cancel_orders', {id: id, str: cause}, function(data){
                    console.log(data);
                    if(data.length){
                        var res = JSON.parse(data);
                        if(res['error']){
                            if(res['str_f'] == 5){
                                $('#check_order').removeClass('status-icon-active');
                                $('#kitchen_order').removeClass('status-icon-active');
                                $('#courier_order').removeClass('status-icon-active');
                                $('#complete_order').removeClass('status-icon-active');
                                $('#cancel_order').addClass('status-icon-cancel');
                            }
                            $('#hint-message').html(res['str']);
                            hint.html(res['error']).addClass('alert alert-danger');
                        }else{
                            if(res['str_f'] == 4){
                                $('#check_order').removeClass('status-icon-active');
                                $('#kitchen_order').removeClass('status-icon-active');
                                $('#courier_order').removeClass('status-icon-active');
                                $('#complete_order').addClass('status-icon-active');
                                $('#cancel_order').removeClass('status-icon-cancel');
                            }
                            if(res['str_f'] == 5){
                                $('#check_order').removeClass('status-icon-active');
                                $('#kitchen_order').removeClass('status-icon-active');
                                $('#courier_order').removeClass('status-icon-active');
                                $('#complete_order').removeClass('status-icon-active');
                                $('#cancel_order').addClass('status-icon-cancel');
                            }
                            $('#hint-message').html(res['str']);

                            or_status.html('Статус: <strong>'+res['status']+'</strong>');
                            $('#rejection_reason').val('');
                            $('#cancel_btn_sts').prop( "disabled", true );
                        }
                    }
                });
            }else{
                var text = 'Введите номер заказа';
                hint.html(text).addClass('alert alert-danger');
                $('#order_status_id').addClass('error');
            }
        }
    }
}

function checkGetClient(){
    var clientID = $('#clientID').val();
    if(clientID != null){
        $.post('/ajax/check_clinetID', {clientid: clientID});
    }
}

function delails_order(){
    var m = $('#order-details');
    var info_order = $('.details-items');
    var result = $('#order-result').val();

    m.modal('show');

    if(result == 1){
        info_order.show();
        $('.confirm').hide();
    }else{
        info_order.hide();
    }
}

function del_gift_cash(elem){
    var val = $(elem).attr('id');

    if(val == 'gift-cash'){
        $('#pay_1').addClass('active');
        $('#pay_2').removeClass('active');

        var name = 'pay';
        $.post('/ajax/order_info', {name:name, val:val});
    }else{
        var flag = 2;
        $.post('/ajax/autoadd_gift_cash', {type: 'delete', flag_gift: flag});
    }
}

function add_gift_cash(type, pay){
    var flg = 1;
    var total_price = $('#ch-cart-total-price').data('price');
    var min_gift_price = $('#min-gift-cash').val();

    if(type == 'add'){
        $.post('/ajax/autoadd_gift_cash', {type: 'add', flag_gift: flg, pay: pay}, function(data) {
            var res1 = JSON.parse(data);
            visual_change_cart('add', res1.id, res1.title, res1.property, res1.price, res1.image);
        });
    }

    if(type == 'delete'){
        console.log('test2');
        $.post('/ajax/autoadd_gift_cash', {type: 'delete', flag_gift: flg}, function(data) {
            var del_promo = 0;
            visual_change_cart('minus', data, '', '', '', '', del_promo);
        });
    }

    if(type == 'add_promo'){
        if(total_price < min_gift_price){
            $.post('/ajax/autoadd_gift_cash', {type: 'delete', flag_gift: flg}, function(data) {
                var del_promo = 0;
                visual_change_cart('minus', data, '', '', '', '', del_promo);
            });
        }
    }

    if(type == 'del_promo'){
        if(total_price >= min_gift_price){
            $.post('/ajax/autoadd_gift_cash', {type: 'add', flag_gift: flg, pay: pay}, function(data) {
                var res1 = JSON.parse(data);
                visual_change_cart('add', res1.id, res1.title, res1.property, res1.price, res1.image);
            });
        }
    }
    if(type == 'add_points'){
        if(total_price < min_gift_price){
            $.post('/ajax/autoadd_gift_cash', {type: 'delete', flag_gift: flg}, function(data) {
                var del_promo = 0;
                visual_change_cart('minus', data, '', '', '', '', del_promo);
            });
        }
    }

    if(type == 'del_points'){
        if(total_price >= min_gift_price){
            $.post('/ajax/autoadd_gift_cash', {type: 'add', flag_gift: flg, pay: pay}, function(data) {
                var res1 = JSON.parse(data);
                visual_change_cart('add', res1.id, res1.title, res1.property, res1.price, res1.image);
            });
        }
    }
}
