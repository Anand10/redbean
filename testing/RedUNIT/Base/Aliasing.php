<?php
/**
 * RedUNIT_Base_Aliasing
 *
 * @file    RedUNIT/Base/Aliasing.php
 * @desc    Tests for nested beans with aliases, i.e. teacher alias for person etc.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedUNIT_Base_Aliasing extends RedUNIT_Base
{

	/**
	 * Associating two beans, then loading the associated bean
	 */
	public function testAssociated()
	{
		$person       = R::dispense( 'person' );
		$person->name = 'John';

		R::store( $person );

		$course       = R::dispense( 'course' );
		$course->name = 'Math';

		R::store( $course );

		$course->teacher = $person;

		$id      = R::store( $course );
		$course  = R::load( 'course', $id );
		$teacher = $course->fetchAs( 'person' )->teacher;

		asrt( $teacher->name, 'John' );

		/**
		 * Trying to load a property that has an invalid name
		 */

		$book = R::dispense( 'book' );
		$page = R::dispense( 'page' );

		$book->wrongProperty = array( $page );

		try {
			$book->wrongProperty[] = $page;
			R::store( $book );
			fail();
		} catch ( RedBean_Exception_Security $e ) {
			pass();
		} catch ( Exception $e ) {
			fail();
		}

	}

	/**
	 * Test for quick detect change
	 */
	public function basic()
	{
		$book = R::dispense( 'book' );

		if ( $book->prop ) {
		}

		//echo $book;

		asrt( isset( $book->prop ), false ); //not a very good test
		asrt( in_array( 'prop', array_keys( $book->export() ) ), false ); //better...

		$book = R::dispense( 'book' );
		$page = R::dispense( 'page' );

		$book->paper = $page;

		$id   = R::store( $book );
		$book = R::load( 'book', $id );

		asrt( false, ( isset( $book->paper ) ) );
		asrt( false, ( isset( $book->page ) ) );

		/**
		 * The following tests try to store various things that aren't
		 * beans (which is expected) with the own* and shared* properties
		 * which only accept beans as assignments, so they're expected to fail
		 */

		foreach (
			array(
				new stdClass(), "a string", 1928, true, null, array()
			)
			as $value
		) {
			try {
				$book->ownPage[] = $value;

				R::store( $book );

				$book->sharedPage[] = $value;

				R::store( $book );

				fail();
			} catch ( RedBean_Exception_Security $e ) {
				pass();
			} catch ( Exception $e ) {
				fail();
			}
		}
	}

	/**
	 * Finding $person beans that have been aliased into various roles
	 */
	public function aliasedFinder()
	{
		$message          = R::dispense( 'message' );
		$message->subject = 'Roommate agreement';

		list( $sender, $recipient ) = R::dispense( 'person', 2 );

		$sender->name    = 'Sheldon';
		$recipient->name = 'Leonard';

		$message->sender    = $sender;
		$message->recipient = $recipient;

		$id      = R::store( $message );
		$message = R::load( 'message', $id );

		asrt( $message->fetchAs( 'person' )->sender->name, 'Sheldon' );
		asrt( $message->fetchAs( 'person' )->recipient->name, 'Leonard' );

		$otherRecipient       = R::dispense( 'person' );
		$otherRecipient->name = 'Penny';

		$message->recipient = $otherRecipient;

		R::store( $message );

		$message = R::load( 'message', $id );

		asrt( $message->fetchAs( 'person' )->sender->name, 'Sheldon' );
		asrt( $message->fetchAs( 'person' )->recipient->name, 'Penny' );

	}

	/**
	 *
	 */
	public function unnamed1()
	{
		$project       = R::dispense( 'project' );
		$project->name = 'Mutant Project';

		list( $teacher, $student ) = R::dispense( 'person', 2 );

		$teacher->name = 'Charles Xavier';

		$project->student       = $student;
		$project->student->name = 'Wolverine';
		$project->teacher       = $teacher;

		$id      = R::store( $project );
		$project = R::load( 'project', $id );

		asrt( $project->fetchAs( 'person' )->teacher->name, 'Charles Xavier' );
		asrt( $project->fetchAs( 'person' )->student->name, 'Wolverine' );
	}

	/**
	 *
	 */
	public function unnamed2()
	{
		$farm    = R::dispense( 'building' );
		$village = R::dispense( 'village' );

		$farm->name    = 'farm';
		$village->name = 'Dusty Mountains';

		$farm->village = $village;

		$id   = R::store( $farm );
		$farm = R::load( 'building', $id );

		asrt( $farm->name, 'farm' );
		asrt( $farm->village->name, 'Dusty Mountains' );

		$village = R::dispense( 'village' );

		list( $mill, $tavern ) = R::dispense( 'building', 2 );

		$mill->name   = 'Mill';
		$tavern->name = 'Tavern';

		$village->ownBuilding = array( $mill, $tavern );

		$id      = R::store( $village );
		$village = R::load( 'village', $id );

		asrt( count( $village->ownBuilding ), 2 );

		$village2 = R::dispense( 'village' );
		$army     = R::dispense( 'army' );

		$village->sharedArmy[]  = $army;
		$village2->sharedArmy[] = $army;

		$id1 = R::store( $village );
		$id2 = R::store( $village2 );

		$village1 = R::load( 'village', $id1 );
		$village2 = R::load( 'village', $id2 );

		asrt( count( $village1->sharedArmy ), 1 );
		asrt( count( $village2->sharedArmy ), 1 );

		asrt( count( $village1->ownArmy ), 0 );
		asrt( count( $village2->ownArmy ), 0 );

	}

	public function unnamed3()
	{
		/**
		 * Ensure that aliased column aren't beautified
		 */

		$points = R::dispense( 'point', 2 );
		$line   = R::dispense( 'line' );

		$line->pointA = $points[0];
		$line->pointB = $points[1];

		R::store( $line );

		$line2 = R::dispense( 'line' );

		$line2->pointA = $line->pointA;
		$line2->pointB = R::dispense( 'point' );

		R::store( $line2 );

		//now we have two points per line (1-to-x)
		//I want to know which lines cross A:

		$a = R::load( 'point', $line->pointA->id ); //reload A

		$lines = $a->alias( 'pointA' )->ownLine;

		asrt( count( $lines ), 2 );
	}
}
