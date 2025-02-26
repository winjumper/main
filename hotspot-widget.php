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

        $repeater->add_control(
            'hotspot_color',
            [
                'label' => esc_html__('Цвет точки', 'elementor-hotspot-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FF0000',
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

        // Раздел стилей
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Стили', 'elementor-hotspot-widget'),
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

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $img_url = $settings['background_image']['url'];
        $id_int = substr($this->get_id_int(), 0, 3);
        
        ?>
        <div class="elementor-hotspot-container">
            <div class="elementor-hotspot-image-container">
                <img src="<?php echo esc_url($img_url); ?>" alt="Hotspot Image" class="elementor-hotspot-image">
                
                <?php foreach ($settings['hotspots'] as $index => $hotspot) : 
                    $hotspot_key = $this->get_repeater_setting_key('hotspot', 'hotspots', $index);
                    $tooltip_key = $this->get_repeater_setting_key('tooltip', 'hotspots', $index);
                    $x_pos = $hotspot['hotspot_x_position']['size'] . $hotspot['hotspot_x_position']['unit'];
                    $y_pos = $hotspot['hotspot_y_position']['size'] . $hotspot['hotspot_y_position']['unit'];
                ?>
                    <div class="elementor-hotspot-point-wrapper" style="left: <?php echo esc_attr($x_pos); ?>; top: <?php echo esc_attr($y_pos); ?>;">
                        <div class="elementor-hotspot-point" style="background-color: <?php echo esc_attr($hotspot['hotspot_color']); ?>;" data-tooltip-id="tooltip-<?php echo esc_attr($id_int . $index); ?>"></div>
                        <div class="elementor-hotspot-tooltip" id="tooltip-<?php echo esc_attr($id_int . $index); ?>">
                            <h4 class="elementor-hotspot-title"><?php echo esc_html($hotspot['hotspot_title']); ?></h4>
                            <div class="elementor-hotspot-description"><?php echo wp_kses_post($hotspot['hotspot_content']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            </div>
        </div>
        <?php
    }
}