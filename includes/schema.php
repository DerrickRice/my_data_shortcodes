<?php

if ( ! defined( 'ABSPATH' ) ) exit;

global $MDSC_schema;

$MDSC_schema = array(
	'class' => array(
		'print_name' => 'Classes',
		'schema' => array(
			// 'id' is always included implicity as varchar(64) / text input.
			'title' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => true,
				'print_name' => 'Class Title',
			),
			'teacher_text' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => true,
				'print_name' => 'Teachers',
			),
			'description' => array(
				'type' => 'varchar(2048)',
				'input' => 'textarea',
				'attrs' => '',
				'tba' => true,
				'print_name' => 'Class Description',
			),
			'notes' => array(
				'type' => 'varchar(512)',
				'input' => 'textarea',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Internal Notes',
			),
		),
	),
	'room' => array(
		'print_name' => 'Rooms',
		'schema' => array(
			// 'id' is always included implicity as varchar(64) / text input.
			'name' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Room Name',
			),
			'building' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Building or Address',
			),
			'theme' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => true,
				'print_name' => 'Theme',
			),
			'notes' => array(
				'type' => 'varchar(512)',
				'input' => 'textarea',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Internal Notes',
			),
		),
	),
	'person' => array(
		'print_name' => 'People',
		'schema' => array(
			// 'id' is always included implicity as varchar(64) / text input.
			'name' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Name',
			),
			'role' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Role or Title',
			),
			'img' => array(
				'type' => 'varchar(256)',
				'input' => 'text',
				'attrs' => '',
				'tba' => true,
				'print_name' => 'Image URL',
			),
			'bio' => array(
				'type' => 'varchar(2048)',
				'input' => 'textarea',
				'attrs' => '',
				'tba' => true,
				'print_name' => 'Bio',
			),
			'notes' => array(
				'type' => 'varchar(512)',
				'input' => 'textarea',
				'attrs' => '',
				'tba' => false,
				'print_name' => 'Internal Notes',
			),
		),
	),
);