<?php

namespace uncanny_advance_trainings;


class WpDataTables {
	public function __construct() {
		/*add_action( 'wpdatatables_before_create_columns', array( __CLASS__, 'wdt_before_create_columns_func' ), 99, 3 );
		add_action( 'wpdatatables_before_row', array( __CLASS__, 'wpdatatables_before_row_func' ), 99, 2 );
		add_filter( 'wpdatatables_filter_table_metadata', array(
			__CLASS__,
			'wpdatatables_filter_table_metadata_func',
		), 99, 3 );
		add_filter( 'wpdatatables_filter_rendered_table', array(
			__CLASS__,
			'wpdatatables_filter_rendered_table_func',
		), 99, 3 );*/
		//add_action( 'wpdatatables_after_frontent_edit_row', array( __CLASS__, 'wdt_after_frontent_edit_row' ), 99, 3 );
		add_filter( 'wpdatatables_filter_column_before_save', array(
			__CLASS__,
			'wpdatatables_filter_column_before_save_func',
		) );

	}


	function wpdatatables_filter_column_before_save_func( $column, $tableId ) {
		Boot::log( $column, '$column--wpdatatables_filter_column_before_save', 'wdt' );
		Boot::log( $tableId, '$tableId--wpdatatables_filter_column_before_save', 'wdt' );
	}

	/**
	 * @param $table
	 * @param $tableId
	 * @param $frontendColumns
	 */
	public static function wdt_before_create_columns_func( $table, $tableId, $frontendColumns ) {
		Boot::log( $table, '$table', 'wdt' );
		Boot::log( $tableId, '$tableId', 'wdt' );
		Boot::log( $frontendColumns, '$frontendColumns', 'wdt' );
	}

	public static function wpdatatables_before_row( $tableId, $rowIndex ) {
		Boot::log( $rowIndex, '$rowIndex', 'wdt' );
		Boot::log( $tableId, '$tableId', 'wdt' );
	}

	public static function wpdatatables_filter_table_metadata_func( $tableMetadata, $tableId ) {
		Boot::log( $tableMetadata, '$tableMetadata', 'wdt' );
		Boot::log( $tableId, '$tableId', 'wdt' );
	}

	public static function wpdatatables_filter_rendered_table_func( $tableContent, $tableId ) {
		Boot::log( $tableContent, '$tableContent', 'wdt' );
		Boot::log( $tableId, '$tableId', 'wdt' );
	}
}