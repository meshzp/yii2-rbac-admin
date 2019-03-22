$(function () {
    // Таймаут для всплывающих подсказок
    var toolTipTimeOut;
    var initSwitch = function () {
        $('.material-switch').change(function (e) {
            // Это - текущий тумблер (по которому нажали)
            var currentSwitch = $(this);
            var enabled;
            var switchClass;
            // Статус текущего тумблера после нажатия (по которому нажали)
            var checked = $(this).find('input').prop('checked');
            // Правило
            var permission = $(this).find('input').attr('name');
            if ($(this).hasClass('direct')) {
                switchClass = 'inverted';
            }
            if ($(this).hasClass('inverted')) {
                switchClass = 'direct';
            }
            //Изменение индивидуальной настройки (если статус текущего тумблера перевели в состояние Вкл.)
            if (checked == true) {
                if (switchClass == 'direct') {
                    enabled = 1;
                } else {
                    enabled = 0;
                }
                // Это - зеркальный тумблер, состояние которого зависит от состояния текущего тумблера
                var mirrorSwitch = $(".material-switch." + switchClass + " > input[name='" + permission + "']");
                // Это - состояние зеркального тумблера до нажатия на текущий тумблер
                var mirrorSwitchOldStatus = mirrorSwitch.prop('checked');
                // Меняем статус зеркального тумблера на Выкл.
                mirrorSwitch.prop('checked', false);
                $.ajax({
                    type: 'POST',
                    url: c_url,
                    data: {
                        permission: permission,
                        enabled: enabled,
                        user_id: user_id
                    },
                    // Обработка ошибок
                    error: function (err) {
                        // Чистим консоль, скрываем ошибку
                        console.clear();
                        console.log('Something goes wrong...');
                        // Если нет права на изменение настройки - придёт ошибка #403, обрабатываем её
                        if (err.status == '403') {
                            // меняем положение тумблера (по которому нажали) на первоначальное (выключаем)
                            currentSwitch.find('input').prop('checked', false);
                            // Если зеркальный тумблер был включен до нажатия на текущий тумблер
                            if (mirrorSwitchOldStatus == true) {
                                // то включаем зеркальный тумблер снова (возвращаем первоначальное состояние)
                                mirrorSwitch.prop('checked', true);
                            }
                            // Выдаём плавно всплывающую подсказку о недостатке прав доступа
                            $('div#access-tooltip').html('You have no rights to change permission status').fadeIn('slow');
                            // Через 2 секунды плавно убираем подсказку
                            toolTipTimeOut = setTimeout(function () {
                                $('body div#access-tooltip').fadeOut('slow');
                            }, 2000);
                        }
                    },
                    // Если ответ от сервера вернулся
                    success: function (res) {
                        // Но произошла ошибка при изменении статуса правила
                        if (res == 0) {
                            // меняем положение тумблера (по которому нажали) на первоначальное (выключаем)
                            currentSwitch.find('input').prop('checked', false);
                            // Если зеркальный тумблер был включен до нажатия на текущий тумблер
                            if (mirrorSwitchOldStatus == true) {
                                // то включаем зеркальный тумблер снова (возвращаем первоначальное состояние)
                                mirrorSwitch.prop('checked', true);
                            }
                        }
                    }
                });
            }
            else {
                //Удаление индивидуальной настройки (если статус текущего тумблера перевели в состояние Выкл)
                $.ajax({
                    type: 'POST',
                    url: d_url,
                    data: {
                        permission: permission,
                        user_id: user_id
                    },
                    // Обработка ошибок
                    error: function (err) {
                        // Чистим консоль, скрываем ошибку
                        console.clear();
                        console.log('Something goes wrong...');
                        // Если нет права на изменение настройки - придёт ошибка #403, обрабатываем её
                        if (err.status == '403') {
                            // меняем положение тумблера (по которому нажали) на первоначальное (включаем)
                            currentSwitch.find('input').prop('checked', true);
                            // Выдаём плавно всплывающую подсказку о недостатке прав доступа
                            $('div#access-tooltip').html('You have no rights to flush permission status').fadeIn('slow');
                            // Через 2 секунды плавно убираем подсказку
                            toolTipTimeOut = setTimeout(function () {
                                $('body div#access-tooltip').fadeOut('slow');
                            }, 2000);
                        }
                    },
                    success: function (res) {
                        if (res == 0) {
                            currentSwitch.find('input').prop('checked', true);
                        }
                    }
                });
            }
        });
    };

    initSwitch();

    $(document).ajaxComplete(function () {
        $(".material-switch").unbind('change');
        initSwitch();
    });


});

