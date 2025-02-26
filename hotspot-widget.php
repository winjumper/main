<?php
if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Hotspot_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'hotspot_widget';
    }

    public function get_title() {
        return esc_html__('Image Hotspot', 'elementor-hotspot-widget');
    }

    public function get_icon() {
        return 'eicon-image-hotspot';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_script_depends() {
        return ['hotspot-script'];
    }

    public function get_style_depends() {
        return ['hotspot-style'];
    }

    protected function register_controls() {
        // Секция с основными настройками
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Настройки изображения', 'elementor-hotspot-widget'),
            ]
        );

        $this->add_control(
            'background_image',
            [
                'label' => esc_html__('Выберите изображение', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'interactive_editor',
            [
                'label' => esc_html__('Интерактивный редактор', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Вкл', 'elementor-hotspot-widget'),
                'label_off' => esc_html__('Выкл', 'elementor-hotspot-widget'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('Включите для перемещения точек прямо на изображении', 'elementor-hotspot-widget'),
            ]
        );

        $this->end_controls_section();

        // Секция с настройками точек
        $this->start_controls_section(
            'section_hotspots',
            [
                'label' => esc_html__('Горячие точки', 'elementor-hotspot-widget'),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'hotspot_type',
            [
                'label' => esc_html__('Тип маркера', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'point',
                'options' => [
                    'point' => esc_html__('Точка', 'elementor-hotspot-widget'),
                    'area' => esc_html__('Область', 'elementor-hotspot-widget'),
                ],
            ]
        );

        $repeater->add_control(
            'hotspot_title',
            [
                'label' => esc_html__('Заголовок', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Заголовок точки', 'elementor-hotspot-widget'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'hotspot_content',
            [
                'label' => esc_html__('Содержимое', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__('Содержимое точки', 'elementor-hotspot-widget'),
            ]
        );

        // Общие настройки позиции для точек и областей
        $repeater->add_control(
            'hotspot_x_position',
            [
                'label' => esc_html__('Позиция X', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
            ]
        );

        $repeater->add_control(
            'hotspot_y_position',
            [
                'label' => esc_html__('Позиция Y', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
            ]
        );

        // Дополнительные настройки для областей
        $repeater->add_control(
            'area_width',
            [
                'label' => esc_html__('Ширина области', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 20,
                ],
                'condition' => [
                    'hotspot_type' => 'area',
                ],
            ]
        );

        $repeater->add_control(
            'area_height',
            [
                'label' => esc_html__('Высота области', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 20,
                ],
                'condition' => [
                    'hotspot_type' => 'area',
                ],
            ]
        );

        $repeater->add_control(
            'area_border_radius',
            [
                'label' => esc_html__('Скругление углов', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}}.elementor-hotspot-area' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'hotspot_type' => 'area',
                ],
            ]
        );

        // Настройки внешнего вида
        $repeater->add_control(
            'hotspot_color',
            [
                'label' => esc_html__('Основной цвет', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FF0000',
            ]
        );

        $repeater->add_control(
            'hotspot_opacity',
            [
                'label' => esc_html__('Прозрачность', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}}.elementor-hotspot-point' => 'opacity: calc({{SIZE}}/100);',
                    '{{WRAPPER}} {{CURRENT_ITEM}}.elementor-hotspot-area' => 'opacity: calc({{SIZE}}/100);',
                ],
            ]
        );

        $repeater->add_control(
            'hotspot_icon',
            [
                'label' => esc_html__('Иконка', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-plus',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'hotspot_type' => 'point',
                ],
            ]
        );

        $repeater->add_control(
            'pulse_animation',
            [
                'label' => esc_html__('Анимация пульсации', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Вкл', 'elementor-hotspot-widget'),
                'label_off' => esc_html__('Выкл', 'elementor-hotspot-widget'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $repeater->add_control(
            'tooltip_position',
            [
                'label' => esc_html__('Позиция подсказки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'top',
                'options' => [
                    'top' => esc_html__('Сверху', 'elementor-hotspot-widget'),
                    'bottom' => esc_html__('Снизу', 'elementor-hotspot-widget'),
                    'left' => esc_html__('Слева', 'elementor-hotspot-widget'),
                    'right' => esc_html__('Справа', 'elementor-hotspot-widget'),
                ],
            ]
        );

        $this->add_control(
            'hotspots',
            [
                'label' => esc_html__('Горячие точки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'hotspot_type' => 'point',
                        'hotspot_title' => esc_html__('Точка #1', 'elementor-hotspot-widget'),
                        'hotspot_content' => esc_html__('Содержимое точки #1', 'elementor-hotspot-widget'),
                        'hotspot_x_position' => [
                            'unit' => '%',
                            'size' => 20,
                        ],
                        'hotspot_y_position' => [
                            'unit' => '%',
                            'size' => 30,
                        ],
                    ],
                ],
                'title_field' => '{{{ hotspot_title }}}',
            ]
        );

        $this->end_controls_section();

        // Раздел стилей для точек
        $this->start_controls_section(
            'section_point_style',
            [
                'label' => esc_html__('Стили точек', 'elementor-hotspot-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'hotspot_size',
            [
                'label' => esc_html__('Размер точки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-point' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'point_border_width',
            [
                'label' => esc_html__('Толщина границы', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-point' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'point_border_color',
            [
                'label' => esc_html__('Цвет границы', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-point' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'point_border_width[size]!' => 0,
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'point_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-hotspot-point',
            ]
        );

        $this->end_controls_section();

        // Раздел стилей для областей
        $this->start_controls_section(
            'section_area_style',
            [
                'label' => esc_html__('Стили областей', 'elementor-hotspot-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'area_border_width',
            [
                'label' => esc_html__('Толщина границы', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-area' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'area_border_color',
            [
                'label' => esc_html__('Цвет границы', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FF0000',
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-area' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'area_fill_color',
            [
                'label' => esc_html__('Цвет заливки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255, 0, 0, 0.2)',
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-area' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'area_hover_opacity',
            [
                'label' => esc_html__('Прозрачность при наведении', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 40,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-area:hover' => 'opacity: calc({{SIZE}}/100);',
                ],
            ]
        );

        $this->end_controls_section();

        // Раздел стилей для всплывающих подсказок
        $this->start_controls_section(
            'section_tooltip_style',
            [
                'label' => esc_html__('Стили подсказок', 'elementor-hotspot-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tooltip_width',
            [
                'label' => esc_html__('Ширина всплывающей подсказки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 500,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-tooltip' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tooltip_background',
            [
                'label' => esc_html__('Фон подсказки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-tooltip' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-hotspot-tooltip:after' => 'border-top-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tooltip_color',
            [
                'label' => esc_html__('Цвет текста', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-tooltip' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tooltip_title_color',
            [
                'label' => esc_html__('Цвет заголовка', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tooltip_title_typography',
                'label' => esc_html__('Типографика заголовка', 'elementor-hotspot-widget'),
                'selector' => '{{WRAPPER}} .elementor-hotspot-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tooltip_content_typography',
                'label' => esc_html__('Типографика содержимого', 'elementor-hotspot-widget'),
                'selector' => '{{WRAPPER}} .elementor-hotspot-description',
            ]
        );

        $this->add_control(
            'tooltip_border_radius',
            [
                'label' => esc_html__('Скругление углов', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-hotspot-tooltip' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '5',
                    'right' => '5',
                    'bottom' => '5',
                    'left' => '5',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tooltip_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-hotspot-tooltip',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $img_url = $settings['background_image']['url'];
        $id_int = substr($this->get_id_int(), 0, 3);
        $is_editor_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $interactive_editor = $settings['interactive_editor'] === 'yes' && $is_editor_mode;
        
        ?>
        <div class="elementor-hotspot-container" data-interactive="<?php echo esc_attr($interactive_editor ? 'true' : 'false'); ?>">
            <div class="elementor-hotspot-image-container">
                <img src="<?php echo esc_url($img_url); ?>" alt="Hotspot Image" class="elementor-hotspot-image">
                
                <?php foreach ($settings['hotspots'] as $index => $hotspot) : 
                    $hotspot_key = $this->get_repeater_setting_key('hotspot', 'hotspots', $index);
                    $tooltip_key = $this->get_repeater_setting_key('tooltip', 'hotspots', $index);
                    $x_pos = $hotspot['hotspot_x_position']['size'] . $hotspot['hotspot_x_position']['unit'];
                    $y_pos = $hotspot['hotspot_y_position']['size'] . $hotspot['hotspot_y_position']['unit'];
                    $tooltip_position = isset($hotspot['tooltip_position']) ? $hotspot['tooltip_position'] : 'top';
                    $animation_class = isset($hotspot['pulse_animation']) && $hotspot['pulse_animation'] === 'yes' ? 'elementor-pulse' : '';
                    $current_item_class = 'elementor-repeater-item-' . $hotspot['_id'];
                    
                    if ($hotspot['hotspot_type'] === 'point') :
                ?>
                    <div class="elementor-hotspot-point-wrapper <?php echo esc_attr($current_item_class); ?>" 
                         style="left: <?php echo esc_attr($x_pos); ?>; top: <?php echo esc_attr($y_pos); ?>;"
                         data-position-x="<?php echo esc_attr($hotspot['hotspot_x_position']['size']); ?>"
                         data-position-y="<?php echo esc_attr($hotspot['hotspot_y_position']['size']); ?>"
                         data-type="point"
                         data-index="<?php echo esc_attr($index); ?>">
                        <div class="elementor-hotspot-point <?php echo esc_attr($animation_class); ?> <?php echo esc_attr($current_item_class); ?>" 
                             style="background-color: <?php echo esc_attr($hotspot['hotspot_color']); ?>;" 
                             data-tooltip-id="tooltip-<?php echo esc_attr($id_int . $index); ?>">
                            <?php if (!empty($hotspot['hotspot_icon']['value'])) : ?>
                                <i class="<?php echo esc_attr($hotspot['hotspot_icon']['value']); ?>"></i>
                            <?php endif; ?>
                        </div>
                        <div class="elementor-hotspot-tooltip tooltip-<?php echo esc_attr($tooltip_position); ?>" id="tooltip-<?php echo esc_attr($id_int . $index); ?>">
                            <h4 class="elementor-hotspot-title"><?php echo esc_html($hotspot['hotspot_title']); ?></h4>
                            <div class="elementor-hotspot-description"><?php echo wp_kses_post($hotspot['hotspot_content']); ?></div>
                        </div>
                    </div>
                <?php else : // Если тип - область ?>
                    <?php 
                        $area_width = isset($hotspot['area_width']['size']) ? $hotspot['area_width']['size'] . $hotspot['area_width']['unit'] : '20%';
                        $area_height = isset($hotspot['area_height']['size']) ? $hotspot['area_height']['size'] . $hotspot['area_height']['unit'] : '20%';
                    ?>
                    <div class="elementor-hotspot-area-wrapper <?php echo esc_attr($current_item_class); ?>" 
                         style="left: <?php echo esc_attr($x_pos); ?>; top: <?php echo esc_attr($y_pos); ?>;"
                         data-position-x="<?php echo esc_attr($hotspot['hotspot_x_position']['size']); ?>"
                         data-position-y="<?php echo esc_attr($hotspot['hotspot_y_position']['size']); ?>"
                         data-width="<?php echo esc_attr($hotspot['area_width']['size']); ?>"
                         data-height="<?php echo esc_attr($hotspot['area_height']['size']); ?>"
                         data-type="area"
                         data-index="<?php echo esc_attr($index); ?>">
                        <div class="elementor-hotspot-area <?php echo esc_attr($animation_class); ?> <?php echo esc_attr($current_item_class); ?>" 
                             style="border-color: <?php echo esc_attr($hotspot['hotspot_color']); ?>; width: <?php echo esc_attr($area_width); ?>; height: <?php echo esc_attr($area_height); ?>;" 
                             data-tooltip-id="tooltip-<?php echo esc_attr($id_int . $index); ?>">
                            <?php if ($interactive_editor) : ?>
                                <div class="elementor-hotspot-area-resize-handle"></div>
                            <?php endif; ?>
                        </div>
                        <div class="elementor-hotspot-tooltip tooltip-<?php echo esc_attr($tooltip_position); ?>" id="tooltip-<?php echo esc_attr($id_int . $index); ?>">
                            <h4 class="elementor-hotspot-title"><?php echo esc_html($hotspot['hotspot_title']); ?></h4>
                            <div class="elementor-hotspot-description"><?php echo wp_kses_post($hotspot['hotspot_content']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if ($interactive_editor) : ?>
                <div class="elementor-hotspot-control-panel">
                    <button class="elementor-hotspot-add-point">Добавить точку</button>
                    <button class="elementor-hotspot-add-area">Добавить область</button>
                    <div class="elementor-hotspot-coordinates">X: <span class="coord-x">0</span>%, Y: <span class="coord-y">0</span>%</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
