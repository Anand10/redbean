<?php
/**
 * RedUNIT_Mysql_Freeze
 *
 * @file    RedUNIT/Mysql/Freeze.php
 * @desc    Tests freezing of databases for production environments.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedUNIT_Mysql_Freeze extends RedUNIT_Mysql
{
	public function unnamed0()
	{
		$toolbox = R::$toolbox;
		$adapter = $toolbox->getDatabaseAdapter();
		$writer  = $toolbox->getWriter();
		$redbean = $toolbox->getRedBean();
		$pdo     = $adapter->getDatabase();

		$a = new RedBean_AssociationManager( $toolbox );

		$post = $redbean->dispense( 'post' );

		$post->title = 'title';

		$redbean->store( $post );

		$page = $redbean->dispense( 'page' );

		$page->name = 'title';

		$redbean->store( $page );

		$page = $redbean->dispense( "page" );

		$page->name = "John's page";

		$idpage = $redbean->store( $page );

		$page2 = $redbean->dispense( "page" );

		$page2->name = "John's second page";

		$idpage2 = $redbean->store( $page2 );

		$a->associate( $page, $page2 );

		$redbean->freeze( true );

		$page = $redbean->dispense( "page" );

		$page->sections = 10;

		$page->name = "half a page";

		try {
			$id = $redbean->store( $page );

			fail();
		} catch ( RedBean_Exception_SQL $e ) {
			pass();
		}

		$post = $redbean->dispense( "post" );

		$post->title = "existing table";

		try {
			$id = $redbean->store( $post );

			pass();
		} catch ( RedBean_Exception_SQL $e ) {
			fail();
		}

		asrt( in_array( "name", array_keys( $writer->getColumns( "page" ) ) ), true );
		asrt( in_array( "sections", array_keys( $writer->getColumns( "page" ) ) ), false );

		$newtype = $redbean->dispense( "newtype" );

		$newtype->property = 1;

		try {
			$id = $redbean->store( $newtype );

			fail();
		} catch ( RedBean_Exception_SQL $e ) {
			pass();
		}

		$logger = RedBean_Plugin_QueryLogger::getInstanceAndAttach( $adapter );

		// Now log and make sure no 'describe SQL' happens
		$page = $redbean->dispense( "page" );

		$page->name = "just another page that has been frozen...";

		$id = $redbean->store( $page );

		$page = $redbean->load( "page", $id );

		$page->name = "just a frozen page...";

		$redbean->store( $page );

		$page2 = $redbean->dispense( "page" );

		$page2->name = "an associated frozen page";

		$a->associate( $page, $page2 );

		$a->related( $page, "page" );

		$a->unassociate( $page, $page2 );

		$a->clearRelations( $page, "page" );

		$items = $redbean->find( "page", array(), array( "1" ) );

		$redbean->trash( $page );

		$redbean->freeze( false );

		asrt( count( $logger->grep( "SELECT" ) ) > 0, true );
		asrt( count( $logger->grep( "describe" ) ) < 1, true );

		asrt( is_array( $logger->getLogs() ), true );
	}
}
