<?php
/*
Plugin Name: Uncanny &mdash; Advanced Trainings Plugin
Description: Advanced Trainings Changes
Version: 1.1
Author: Uncanny Owl
Author URI: www.uncannyowl.com
Text Domain: uncanny-owl
*/


define( 'UO_AT_MAIN_FILE', __FILE__ );

include_once( dirname( __FILE__ ) . '/src/boot.php' );
$uo_adv_trainings = new \uncanny_advance_trainings\Boot();
$uo_adv_trainings::init();
