<?php
use Elementor\Controls_Manager;

$this->start_controls_section(
	'type_section',
	array(
		'label' => esc_html__( 'Type', 'masterstudy-lms-learning-management-system' ),
		'tab'   => Controls_Manager::TAB_CONTENT,
	)
);
$this->add_control(
	'type',
	array(
		'label'   => esc_html__( 'Type', 'masterstudy-lms-learning-management-system' ),
		'type'    => Controls_Manager::SELECT,
		'default' => 'courses-archive',
		'options' => array(
			'courses-archive' => esc_html__( 'Archive', 'masterstudy-lms-learning-management-system' ),
		),
	)
);
$this->end_controls_section();
