<?php
/**
 * RedUNIT_Mysql_Mix
 *
 * @file    RedUNIT/Mysql/Mix.php
 * @desc    Tests mixing SQL with PHP, SQLHelper class.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedUNIT_Mysql_Mix extends RedUNIT_Mysql
{
	public function unnamed0()
	{
		$toolbox = R::$toolbox;

		$adapter = $toolbox->getDatabaseAdapter();

		$mixer = new RedBean_SQLHelper( $adapter );

		$now = $mixer->now();

		asrt( is_string( $now ), true );

		asrt( ( strlen( $now ) > 5 ), true );

		$bean = R::dispense( 'bean' );

		$bean->field1 = 'a';
		$bean->field2 = 'b';

		R::store( $bean );

		$data = $mixer->begin()->select( '*' )->from( 'bean' )
			->where( ' field1 = ? ' )->put( 'a' )->get();

		asrt( is_array( $data ), true );

		$row = array_pop( $data );

		asrt( is_array( $row ), true );

		asrt( $row['field1'], 'a' );
		asrt( $row['field2'], 'b' );

		$row = $mixer->begin()->select( 'field1', 'field2' )->from( 'bean' )
			->where( ' 1 ' )->limit( '1' )->get( 'row' );

		asrt( is_array( $row ), true );

		asrt( $row['field1'], 'a' );
		asrt( $row['field2'], 'b' );

		$cell = $mixer->begin()->select( 'field1' )->from( 'bean' )
			->get( 'cell' );

		asrt( $cell, 'a' );

		$cell = $mixer->begin()->select_field1_from( 'bean' )
			->get( 'cell' );

		asrt( $cell, 'a' );

		// Now switch back to non-capture mode (issue #142)
		$value = $mixer->now();

		asrt( is_object( $value ), false );
		asrt( is_scalar( $value ), true );

		asrt( $value > 0, true );

		$mixer->begin()->select_field1_from( 'bean' );

		$mixer->clear();

		$value = $mixer->now();

		asrt( is_scalar( $value ), true );

		// Test open and close block commands
		$bean = R::dispense( 'bean' );

		$bean->num = 2;

		R::store( $bean );

		$value = $mixer->begin()
			->select( 'num' )->from( 'bean' )->where( 'num IN' )
			->open()
			->addSQL( '2' )
			->close()
			->get( 'cell' );

		asrt( ( $value == 2 ), true );

		// Test nesting
		$bean = R::dispense( 'bean' );

		$bean->num = 2;

		R::store( $bean );

		$value = $mixer->begin()
			->select( 'num' )->from( 'bean' )->where( 'num IN' )
			->nest( $mixer->getNew()->begin()->addSQL( ' ( 2 ) ' ) )
			->get( 'cell' );

		asrt( ( $value == 2 ), true );
	}
}




