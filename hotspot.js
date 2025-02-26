(function($) {
    'use strict';
    
    var HotspotHandler = function($scope, $) {
        var $container = $scope.find('.elementor-hotspot-container');
        
        // Обработка клика на точке для мобильных устройств
        $container.find('.elementor-hotspot-point').on('click', function() {
            // Для мобильных устройств дополнительная обработка может быть добавлена здесь
        });
        
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
                }
                
                // Если тултип выходит за правый край
                if (tooltipRect.right > containerRect.right) {
                    $tooltip.css({
                        'left': 'auto',
                        'right': '0',
                        'transform': 'translateX(0)'
                    });
                }
            });
        }
        
        // Вызов функции при загрузке и при изменении размера окна
        adjustTooltipPosition();
        $(window).on('resize', adjustTooltipPosition);
    };
    
    // Регистрация обработчика в Elementor
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/hotspot_widget.default', HotspotHandler);
    });
    
})(jQuery);