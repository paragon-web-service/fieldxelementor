<?php
use Elementor\Controls_Manager;

$this->start_controls_section(
	'sorting_section',
	array(
		'label'      => esc_html__( 'Sorting', 'masterstudy-lms-learning-management-system' ),
		'tab'        => Controls_Manager::TAB_CONTENT,
		'conditions' => $this->add_widget_type_conditions( 'courses-archive' ),
	)
);
$this->add_subswitcher_control(
	'show_sorting',
	array(
		'default' => 'yes',
	)
);
$this->add_control(
	'sort_presets',
	array(
		'label'      => esc_html__( 'Preset', 'masterstudy-lms-learning-management-system' ),
		'type'       => Controls_Manager::SELECT,
		'default'    => 'sorting-style-1',
		'options'    => array(
			'sorting-style-1' => esc_html__( 'Buttons', 'masterstudy-lms-learning-management-system' ),
			'sorting-style-2' => esc_html__( 'Tabs', 'masterstudy-lms-learning-management-system' ),
			'sorting-style-3' => esc_html__( 'Select', 'masterstudy-lms-learning-management-system' ),
		),
		'conditions' => $this->add_visible_conditions( 'show_sorting' ),
	)
);
$this->add_control(
	'sort_options',
	array(
		'label'       => esc_html__( 'Options', 'masterstudy-lms-learning-management-system' ),
		'type'        => Controls_Manager::SELECT2,
		'label_block' => true,
		'multiple'    => true,
		'options'     => array(
			'date_high'  => esc_html__( 'Newest', 'masterstudy-lms-learning-management-system' ),
			'date_low'   => esc_html__( 'Oldest', 'masterstudy-lms-learning-management-system' ),
			'price_high' => esc_html__( 'Price High', 'masterstudy-lms-learning-management-system' ),
			'price_low'  => esc_html__( 'Price Low', 'masterstudy-lms-learning-management-system' ),
			'rating'     => esc_html__( 'Overall Rating', 'masterstudy-lms-learning-management-system' ),
			'popular'    => esc_html__( 'Most Viewed', 'masterstudy-lms-learning-management-system' ),
		),
		'default'     => array( 'date_high', 'date_low', 'price_high', 'price_low', 'rating', 'popular' ),
		'conditions'  => $this->add_visible_conditions( 'show_sorting' ),
	),
);
$this->end_controls_section();
