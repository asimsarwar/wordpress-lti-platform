<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'lti-resource/Activity.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'lms/tl-constants.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'lms/class-abstract-tl-post-type.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'lms/class-course-post-type.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'lms/class-lesson-post-type.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'lms/class-trek-post-type.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'lms/class-tl-admin-menu.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

TL_Course_Post_Type::instance();
TL_Lesson_Post_Type::instance();
TL_TREK_Post_Type::instance();
TL_Admin_Menu::instance();
