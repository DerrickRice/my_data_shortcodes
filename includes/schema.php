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
        'tba' => false,
        'print_name' => 'Teachers',
      ),
      'teacher_tags' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Teacher IDs',
      ),
      'description' => array(
        'type' => 'varchar(2048)',
        'input' => 'richtext',
        'attrs' => '',
        'tba' => true,
        'print_name' => 'Class Description',
      ),
      'tags' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Tags',
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
      'shortname' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Short name',
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
        'input' => 'richtext',
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
  'venue' => array(
    'print_name' => 'Venues',
    'schema' => array(
      // 'id' is always included implicity as varchar(64) / text input.
      'name' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => true,
        'print_name' => 'Name',
      ),
      'shortname' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Short name',
      ),
      'events' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Events/Uses',
      ),
      'img' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => true,
        'print_name' => 'Image URL',
      ),
      'content' => array(
        'type' => 'varchar(2048)',
        'input' => 'richtext',
        'attrs' => '',
        'tba' => true,
        'print_name' => 'Content',
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
  'tags' => array(
    'print_name' => 'Tags',
    'schema' => array(
      // 'id' is always included implicity as varchar(64) / text input.
      'name' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => true,
        'print_name' => 'Name',
      ),
      'shortname' => array(
        'type' => 'varchar(256)',
        'input' => 'text',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Short name',
      ),
      'description' => array(
        'type' => 'varchar(2048)',
        'input' => 'richtext',
        'attrs' => '',
        'tba' => false,
        'print_name' => 'Content',
      ),
    ),
  ),
);
