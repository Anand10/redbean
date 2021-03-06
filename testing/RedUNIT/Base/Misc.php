<?php
/**
 * RedUNIT_Base_Misc
 *
 * @file    RedUNIT/Base/Misc.php
 * @desc    Various tests.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedUNIT_Base_Misc extends RedUNIT_Base
{
	public function testUUIDs()
	{
		testpack( 'Test basic support UUIDs' );

		$book = R::dispense( 'book' );

		$book->name = 'abc';

		$old = R::$writer->setNewIDSQL( '100' );

		pass();

		asrt( is_string( $old ), true );

		R::store( $book );

		$book = R::load( 'book', 100 );

		asrt( $book->name, 'abc' );

		R::$writer->setNewIDSQL( $old );

		pass();

		//test backward compatibility functions
		testpack( 'Test backward compatability methods' );

		asrt( R::$writer->safeColumn( 'column', true ), R::$writer->esc( 'column', true ) );
		asrt( R::$writer->safeColumn( 'column', false ), R::$writer->esc( 'column', false ) );
		asrt( R::$writer->safeTable( 'table', true ), R::$writer->esc( 'table', true ) );
		asrt( R::$writer->safeTable( 'table', false ), R::$writer->esc( 'table', false ) );
	}

	public function testBeautifulColumnNames()
	{
		testpack( 'Beautiful column names' );

		$town = R::dispense( 'town' );

		$town->isCapital       = false;
		$town->hasTrainStation = true;
		$town->name            = 'BeautyVille';

		$houses = R::dispense( 'house', 2 );

		$houses[0]->isForSale = true;

		$town->ownHouse = $houses;

		R::store( $town );

		$town = R::load( 'town', $town->id );

		asrt( ( $town->isCapital == false ), true );
		asrt( ( $town->hasTrainStation == true ), true );
		asrt( ( $town->name == 'BeautyVille' ), true );

		testpack( 'Accept datetime objects.' );

		$cal = R::dispense( 'calendar' );

		$cal->when = new DateTime( '2000-01-01', new DateTimeZone( 'Pacific/Nauru' ) );

		asrt( $cal->when, '2000-01-01 00:00:00' );

		testpack( 'Affected rows test' );

		$currentDriver = $this->currentlyActiveDriverID;

		$toolbox = R::$toolbox;
		$adapter = $toolbox->getDatabaseAdapter();
		$writer  = $toolbox->getWriter();
		$redbean = $toolbox->getRedBean();
		$pdo     = $adapter->getDatabase();

		$bean = $redbean->dispense( 'bean' );

		$bean->prop = 3; //make test run with strict mode as well

		$redbean->store( $bean );

		$adapter->exec( 'UPDATE bean SET prop = 2' );

		asrt( $adapter->getAffectedRows(), 1 );

		testpack( 'Testing Logger' );

		R::$adapter->getDatabase()->setLogger( new RedBean_Logger_Default );

		asrt( ( R::$adapter->getDatabase()->getLogger() instanceof RedBean_Logger ), true );
		asrt( ( R::$adapter->getDatabase()->getLogger() instanceof RedBean_Logger_Default ), true );

		$bean = R::dispense( 'bean' );

		$bean->property = 1;
		$bean->unsetAll( array( 'property' ) );

		asrt( $bean->property, null );

		asrt( ( $bean->setAttr( 'property', 2 ) instanceof RedBean_OODBBean ), true );
		asrt( $bean->property, 2 );

		asrt( preg_match( '/\d\d\d\d\-\d\d\-\d\d/', R::isoDate() ), 1 );
		asrt( preg_match( '/\d\d\d\d\-\d\d\-\d\d\s\d\d:\d\d:\d\d/', R::isoDateTime() ), 1 );

		$redbean = R::getRedBean();
		$adapter = R::getDatabaseAdapter();
		$writer  = R::getWriter();

		asrt( ( $redbean instanceof RedBean_OODB ), true );
		asrt( ( $adapter instanceof RedBean_Adapter ), true );
		asrt( ( $writer instanceof RedBean_QueryWriter ), true );

		R::setRedBean( $redbean );
		pass(); //cant really test this

		R::setDatabaseAdapter( $adapter );
		pass(); //cant really test this

		R::setWriter( $writer );
		pass(); //cant really test this

		$u1 = R::dispense( 'user' );

		$u1->name  = 'Gabor';
		$u1->login = 'g';

		$u2 = R::dispense( 'user' );

		$u2->name  = 'Eric';
		$u2->login = 'e';

		R::store( $u1 );
		R::store( $u2 );

		$list = R::getAssoc( 'select login,' . R::$writer->esc( 'name' ) . ' from ' . R::$writer->esc( 'user' ) . ' ' );

		asrt( $list['e'], 'Eric' );
		asrt( $list['g'], 'Gabor' );

		$painting = R::dispense( 'painting' );

		$painting->name = 'Nighthawks';

		$id = R::store( $painting );

		testpack( 'Testing Plugin Cooker' );

		$cooker = new RedBean_Plugin_Cooker();
		$cooker->setToolbox( $toolbox );

		try {
			asrt( $cooker->graph( 'abc' ), 'abc' );

			fail();
		} catch ( RedBean_Exception_Security $e ) {
			pass();
		}

		testpack( 'Testing SQL Error Types' );

		foreach ( $writer->typeno_sqltype as $code => $text ) {
			asrt( is_integer( $code ), true );
			asrt( is_string( $text ), true );
		}

		foreach ( $writer->sqltype_typeno as $text => $code ) {
			asrt( is_integer( $code ), true );
			asrt( is_string( $text ), true );
		}

		testpack( 'Testing Nowhere Pt. 1 (unfrozen)' );

		foreach (
			array(
				'exec', 'getAll', 'getCell', 'getAssoc', 'getRow', 'getCol'
			)
			as $method ) {

			R::$method( 'select * from nowhere' );
			pass();
		}

		testpack( 'Testing Nowhere Pt. 2 (frozen)' );

		R::freeze( true );

		foreach (
			array(
				'exec', 'getAll', 'getCell', 'getAssoc', 'getRow', 'getCol'
			)
			as $method ) {

			try {
				R::$method( 'select * from nowhere' );

				fail();
			} catch ( RedBean_Exception_SQL $e ) {
				pass();
			}
		}

		R::freeze(false);
	}

	/**
	 *
	 */
	public function unnamed1()
	{
		testpack( 'Testing Database Properties' );

		$adapter = R::getDatabaseAdapter();

		if ( method_exists( R::$adapter->getDatabase(), 'getPDO' ) ){
			asrt( $adapter->getDatabase()->getPDO() instanceof PDO, true );
		}

		asrt( strlen( $adapter->getDatabase()->getDatabaseVersion() ) > 0, true );
		asrt( strlen( $adapter->getDatabase()->getDatabaseType() ) > 0, true );
	}

	/**
	 *
	 */
	public function unnamed2()
	{
		$track = R::dispense( 'track' );
		$album = R::dispense( 'cd' );

		$track->name     = 'a';
		$track->ordernum = 1;

		$track2 = R::dispense( 'track' );

		$track2->ordernum = 2;
		$track2->name     = 'b';

		R::associate( $album, $track );
		R::associate( $album, $track2 );

		$tracks = R::related( $album, 'track' );

		$track  = array_shift( $tracks );
		$track2 = array_shift( $tracks );

		$ab = $track->name . $track2->name;

		asrt( ( $ab == 'ab' || $ab == 'ba' ), true );

		$t  = R::dispense( 'person' );
		$s  = R::dispense( 'person' );
		$s2 = R::dispense( 'person' );

		$t->name = 'a';
		$t->role = 'teacher';

		$s->role  = 'student';
		$s2->role = 'student';

		$s->name  = 'a';
		$s2->name = 'b';

		$role = R::$writer->esc( 'role' );

		R::associate( $t, $s );
		R::associate( $t, $s2 );

		$students = R::related( $t, 'person', sprintf( ' %s  = ? ', $role ), array( "student" ) );

		$s  = array_shift( $students );
		$s2 = array_shift( $students );

		asrt( ( $s->name == 'a' || $s2->name == 'a' ), true );
		asrt( ( $s->name == 'b' || $s2->name == 'b' ), true );

		// Empty classroom
		R::clearRelations( $t, 'person' );
		R::associate( $t, $s2 );

		$students = R::related( $t, 'person', sprintf( ' %s  = ? ', $role ), array( "student" ) );

		asrt( count( $students ), 1 );

		$s = reset( $students );

		asrt( $s->name, 'b' );
	}

	/**
	 *
	 */
	public function testTransactions()
	{
		testpack( 'transactions' );

		R::begin();

		$bean = R::dispense( 'bean' );

		R::store( $bean );
		R::commit();

		asrt( R::count( 'bean' ), 1 );

		R::wipe( 'bean' );
		R::freeze( 1 );
		R::begin();

		$bean = R::dispense( 'bean' );

		R::store( $bean );
		R::rollback();

		asrt( R::count( 'bean' ), 0 );

		R::freeze( false );

		testpack( 'genSlots' );

		asrt( R::genSlots( array( 'a', 'b' ) ), '?,?' );
		asrt( R::genSlots( array( 'a' ) ), '?' );
		asrt( R::genSlots( array() ), '' );
	}

	/**
	 *
	 */
	public function testFUSEnested()
	{
		testpack( 'FUSE models cant touch nested beans in update() - issue 106' );

		$spoon       = R::dispense( 'spoon' );
		$spoon->name = 'spoon for test bean';

		$deep        = R::dispense( 'deep' );
		$deep->name  = 'deepbean';

		$item        = R::dispense( 'item' );
		$item->val   = 'Test';
		$item->deep  = $deep;

		$test = R::dispense( 'test' );

		$test->item          = $item;
		$test->sharedSpoon[] = $spoon;

		$test->isnowtainted = true;

		$id   = R::store( $test );
		$test = R::load( 'test', $id );

		asrt( $test->item->val, 'Test2' );

		$can   = reset( $test->ownCan );
		$spoon = reset( $test->sharedSpoon );

		asrt( $can->name, 'can for bean' );
		asrt( $spoon->name, 'S2' );
		asrt( $test->item->deep->name, '123' );
		asrt( count( $test->ownCan ), 1 );
		asrt( count( $test->sharedSpoon ), 1 );
		asrt( count( $test->sharedPeas ), 10 );
		asrt( count( $test->ownChip ), 9 );
	}

	/**
	 *
	 */
	public function unnamed5()
	{
		$coffee = R::dispense( 'coffee' );

		$coffee->size     = 'XL';
		$coffee->ownSugar = R::dispense( 'sugar', 5 );

		$id = R::store( $coffee );

		$coffee = R::load( 'coffee', $id );

		asrt( count( $coffee->ownSugar ), 3 );

		$coffee->ownSugar = R::dispense( 'sugar', 2 );

		$id     = R::store( $coffee );
		$coffee = R::load( 'coffee', $id );

		asrt( count( $coffee->ownSugar ), 2 );

		$cocoa = R::dispense( 'cocoa' );

		$cocoa->name = 'Fair Cocoa';

		list( $taste1, $taste2 ) = R::dispense( 'taste', 2 );

		$taste1->name = 'sweet';
		$taste2->name = 'bitter';

		$cocoa->ownTaste = array( $taste1, $taste2 );

		R::store( $cocoa );

		$cocoa->name = 'Koko';

		R::store( $cocoa );

		if ( method_exists( R::$adapter->getDatabase(), 'getPDO' ) ) {
			$pdo    = R::$adapter->getDatabase()->getPDO();
			$driver = new RedBean_Driver_PDO( $pdo );

			pass();

			asrt( $pdo->getAttribute( PDO::ATTR_ERRMODE ), PDO::ERRMODE_EXCEPTION );
			asrt( $pdo->getAttribute( PDO::ATTR_DEFAULT_FETCH_MODE ), PDO::FETCH_ASSOC );
			asrt( strval( $driver->GetCell( 'select 123' ) ), '123' );
		}

		$a = new RedBean_Exception_SQL;
		$a->setSqlState( 'test' );

		$b = strval( $a );

		asrt( $b, '[test] - ' );
	}

	/**
	 *
	 */
	public function testMultiDeleteUpdate()
	{
		testpack( 'test multi delete and multi update' );

		$beans = R::dispenseLabels( 'bean', array( 'a', 'b' ) );
		$ids   = R::storeAll( $beans );

		asrt( (int) R::count( 'bean' ), 2 );

		R::trashAll( R::batch( 'bean', $ids ) );

		asrt( (int) R::count( 'bean' ), 0 );

		testpack( 'test assocManager check' );

		$rb = new RedBean_OODB( R::$writer );

		try {
			$rb->getAssociationManager();

			fail();
		} catch ( RedBean_Exception_Security $e ) {
			pass();
		}
	}
}



