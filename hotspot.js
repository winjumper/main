(function($) {
    'use strict';
    
    var HotspotHandler = function($scope, $) {
        var $container = $scope.find('.elementor-hotspot-container');
        var isElementorEditor = Boolean(window.elementorFrontend && window.elementorFrontend.isEditMode());
        
        // Обработка клика на точке и области для мобильных устройств
        $container.find('.elementor-hotspot-point, .elementor-hotspot-area').on('click', function() {
            // Для мобильных устройств: временно показываем тултип
            if (window.innerWidth <= 768) {
                var $tooltip = $(this).siblings('.elementor-hotspot-tooltip');
                $tooltip.css({
                    'opacity': '1',
                    'visibility': 'visible'
                });
                
                // Скрываем тултип через 3 секунды
                setTimeout(function() {
                    $tooltip.css({
                        'opacity': '0',
                        'visibility': 'hidden'
                    });
                }, 3000);
            }
        });
        
        // Функция для настройки перетаскивания элементов в режиме редактора
        function setupDraggableElements() {
            if (!isElementorEditor) return;
            
            // Делаем точки перетаскиваемыми
            $container.find('.elementor-hotspot-point-wrapper').draggable({
                containment: ".elementor-hotspot-image-container",
                stop: function(event, ui) {
                    // Обновляем позицию в процентах относительно родительского контейнера
                    var $parent = $(this).parent();
                    var parentWidth = $parent.width();
                    var parentHeight = $parent.height();
                    
                    var xPosition = (ui.position.left / parentWidth * 100).toFixed(2);
                    var yPosition = (ui.position.top / parentHeight * 100).toFixed(2);
                    
                    // Тут можно добавить код для обновления значений в панели Elementor
                    console.log('Point moved to: X: ' + xPosition + '%, Y: ' + yPosition + '%');
                    
                    // Обновление значений в Elementor (требует интеграции с API Elementor)
                    updateElementorControlValue($(this).data('id'), 'hotspot_x_position', xPosition);
                    updateElementorControlValue($(this).data('id'), 'hotspot_y_position', yPosition);
                }
            });
            
            // Делаем области изменяемыми и перетаскиваемыми
            $container.find('.elementor-hotspot-area').draggable({
                containment: ".elementor-hotspot-image-container",
                stop: function(event, ui) {
                    // Обновляем позицию в процентах
                    var $parent = $(this).parent();
                    var parentWidth = $parent.width();
                    var parentHeight = $parent.height();
                    
                    var xPosition = (ui.position.left / parentWidth * 100).toFixed(2);
                    var yPosition = (ui.position.top / parentHeight * 100).toFixed(2);
                    
                    // Обновление значений в Elementor
                    updateElementorControlValue($(this).data('id'), 'area_x_position', xPosition);
                    updateElementorControlValue($(this).data('id'), 'area_y_position', yPosition);
                }
            }).resizable({
                containment: ".elementor-hotspot-image-container",
                handles: "se",
                stop: function(event, ui) {
                    // Обновляем размеры в процентах
                    var $parent = $(this).parent();
                    var parentWidth = $parent.width();
                    var parentHeight = $parent.height();
                    
                    var width = (ui.size.width / parentWidth * 100).toFixed(2);
                    var height = (ui.size.height / parentHeight * 100).toFixed(2);
                    
                    // Обновление значений в Elementor
                    updateElementorControlValue($(this).data('id'), 'area_width', width);
                    updateElementorControlValue($(this).data('id'), 'area_height', height);
                }
            });
        }
        
        // Функция для обновления значений в панели управления Elementor
        function updateElementorControlValue(id, control, value) {
            if (window.elementorFrontend && window.elementorFrontend.isEditMode()) {
                var model = elementor.getPanelView().getCurrentPageView().getOption('editedElementView').getEditModel();
                var settings = model.get('settings');
                var settingName = control;
                
                // Для полей типа slider нужно обновить объект со свойствами size и unit
                if (control.includes('_position') || control.includes('_width') || control.includes('_height')) {
                    settings.setExternalChange(settingName, {
                        size: value,
                        unit: '%'
                    });
                } else {
                    settings.setExternalChange(settingName, value);
                }
            }
        }
        
        // Проверка позиции всплывающих подсказок, чтобы они не выходили за пределы экрана
        function adjustTooltipPosition() {
            $container.find('.elementor-hotspot-tooltip').each(function() {
                var $tooltip = $(this);
                var tooltipRect = this.getBoundingClientRect();
                var containerRect = $container[0].getBoundingClientRect();
                
                // Если тултип выходит за левый край
                if (tooltipRect.left < containerRect.left) {
                    $tooltip.css({
                        'left': '0',
                        'transform': 'translateX(0)'
                    });
                } else if (tooltipRect.right > containerRect.right) {
                    // Если тултип выходит за правый край
                    $tooltip.css({
                        'left': 'auto',
                        'right': '0',
                        'transform': 'translateX(0)'
                    });
                } else {
                    // Возвращаем стандартное положение
                    $tooltip.css({
                        'left': '50%',
                        'transform': 'translateX(-50%)'
                    });
                }
                
                // Если тултип выходит сверху, показываем его внизу
                if (tooltipRect.top < containerRect.top) {
                    $tooltip.css({
                        'bottom': 'auto',
                        'top': '100%',
                        'margin-bottom': '0',
                        'margin-top': '15px'
                    });
                    
                    // Изменяем стрелку
                    $tooltip.find(':after').css({
                        'top': 'auto',
                        'bottom': '100%',
                        'border-top': 'none',
                        'border-bottom': '10px solid white'
                    });
                }
            });
        }
        
        // Вызов функций при загрузке
        adjustTooltipPosition();
        if (isElementorEditor) {
            setupDraggableElements();
        }
        
        // Вызов функций при изменении размера окна
        $(window).on('resize', adjustTooltipPosition);
    };
    
    // Регистрация обработчика в Elementor
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/hotspot_widget.default', HotspotHandler);
    });
    
})(jQuery);
