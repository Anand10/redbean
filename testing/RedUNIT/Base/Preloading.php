<?php
/**
 * RedUNIT_Base_Preloading
 *
 * @file    RedUNIT/Base/Preloading.php
 * @desc    Tests eager loading for parent beans.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedUNIT_Base_Preloading extends RedUNIT_Base
{
	public function testPreloadSave()
	{
		testpack( 'Can we save in preload ?' );

		$publisher = R::dispense( 'publisher' );

		list( $book, $magazine ) = R::dispense( 'book', 2 );

		$book->ownPage       = R::dispense( 'page', 3 );

		$magazine->ownPage   = R::dispense( 'page', 2 );
		$magazine->title     = 'magazine';

		$firstPage           = reset( $book->ownPage );
		$secondPage          = reset( $magazine->ownPage );

		$book->publisher     = $publisher;

		$magazine->publisher = $publisher;

		R::storeAll( array( $book, $magazine ) );

		$firstPage  = $firstPage->fresh();
		$secondPage = $secondPage->fresh();

		$pages      = array( $firstPage, $secondPage );

		R::each( $pages, 'book,*.publisher', function ( $page, $book, $publisher ) {
			R::storeAll( array( $page, $book, $publisher ) );
		} );

		asrt( R::count( 'publisher' ), 1 );
		asrt( R::count( 'book' ), 2 );
		asrt( R::count( 'page' ), 5 );

		testpack( 'Can we re-preload ?' );

		$books = $publishers = array();

		R::each( $pages, 'book,*.publisher', function ( $page, $book, $publisher ) use ( &$pages, &$books, &$publishers ) {
			$books[]      = $book;
			$publishers[] = $publisher;
		} );

		asrt( count( $books ), 2 );

		$foundMagazine = null;
		foreach ( $books as $book ) {
			if ( $book->title === 'magazine' ) {
				$foundMagazine = $book;
			}
		}

		asrt( ( $foundMagazine instanceof RedBean_OODBBean ), true );

		asrt( count( $foundMagazine->ownPage ), 2 );

		testpack( 'Does deleting still work after preloading ?' );

		$firstPageOfMag = reset( $foundMagazine->ownPage );

		$firstID        = $firstPageOfMag->id;

		unset( $foundMagazine->ownPage[$firstID] );

		R::store( $foundMagazine );

		asrt( count( $foundMagazine->ownPage ), 1 );
	}

	public function testShadowUpdate()
	{
		testpack( 'Is the shadow updated ?' );

		$book = R::dispense( 'book' );

		$book->ownPage = R::dispense( 'page', 3 );

		$firstPage = reset( $book->ownPage );

		R::store( $book );

		$book = $book->fresh();

		R::preload( array( $book ), array( 'ownPage' => 'page' ) );

		R::store( $book );

		$book = $book->fresh();

		// Don't we lose beans when saving?
		asrt( count( $book->ownPage ), 3 );

		$book = $book->fresh();

		R::preload( array( $book ), array( 'ownPage' => 'page' ) );

		// Can we delete bean from list if bean has been preloaded ?
		unset( $book->ownPage[$firstPage->id] );

		R::store( $book );

		$book = $book->fresh();

		asrt( count( $book->ownPage ), 2 );

		$book = $book->fresh();

		R::preload( array( $book ), array( 'ownPage' => 'page' ) );

		// Can we add a page?
		$book->ownPage[] = R::dispense( 'page' );

		R::store( $book );

		$book = $book->fresh();

		asrt( count( $book->ownPage ), 3 );
	}

	public function unnamed2()
	{
		$book = R::dispense( 'book' );

		$book->sharedPage = R::dispense( 'page', 3 );

		$firstPage = reset( $book->sharedPage );

		R::store( $book );

		$book = $book->fresh();

		R::preload( array( $book ), array( 'sharedPage' => 'page' ) );

		R::store( $book );

		$book = $book->fresh();

		// Don't we lose beans when saving?
		asrt( count( $book->sharedPage ), 3 );

		$book = $book->fresh();

		R::preload( array( $book ), array( 'sharedPage' => 'page' ) );

		// Can we delete bean from list if bean has been preloaded ?
		unset( $book->sharedPage[$firstPage->id] );

		R::store( $book );

		$book = $book->fresh();

		asrt( count( $book->sharedPage ), 2 );

		$book = $book->fresh();

		R::preload( array( $book ), array( 'sharedPage' => 'page' ) );

		// Can we add a page?
		$book->sharedPage[] = R::dispense( 'page' );

		R::store( $book );

		$book = $book->fresh();

		asrt( count( $book->sharedPage ), 3 );
	}

	public function testNoPreload()
	{
		$books = R::dispense( 'book', 3 );

		$i = 0;

		foreach ( $books as $book ) {
			$i++;

			$book->name      = $i;

			$book->ownPage[] = R::dispense( 'page' )->setAttr( 'name', $i );

			$book->author    = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->coauthor  = R::dispense( 'author' )->setAttr( 'name', $i );
		}

		R::storeAll( $books );

		$books = R::find( 'book' );

		R::nuke();

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->author->id, 0 );
		}
	}

	public function testPreload()
	{
		$books = R::dispense( 'book', 3 );

		$i = 0;

		foreach ( $books as $book ) {
			$i++;

			$book->name      = $i;

			$book->ownPage[] = R::dispense( 'page' )->setAttr( 'name', $i );

			$book->author    = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->coauthor  = R::dispense( 'author' )->setAttr( 'name', $i );
		}

		R::storeAll( $books );

		$books = R::find( 'book' );

		R::preload( $books, array( 'author' ) );

		R::nuke();

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->author->name, strval( ++$i ) );
		}
	}

	public function testAliasedPreload()
	{
		$books = R::dispense( 'book', 3 );

		$i = 0;

		foreach ( $books as $book ) {
			$i++;

			$book->name      = $i;

			$book->ownPage[] = R::dispense( 'page' )->setAttr( 'name', $i );

			$book->author    = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->coauthor  = R::dispense( 'author' )->setAttr( 'name', $i );
		}

		R::storeAll( $books );

		$books = R::find( 'book' );

		R::preload( $books, array( 'coauthor' => 'author' ) );

		R::nuke();

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->fetchAs( 'author' )->coauthor->name, strval( ++$i ) );
		}
	}

	public function testCombinedAndMultiple()
	{
		$books = R::dispense( 'book', 3 );

		$i = 0;

		foreach ( $books as $book ) {
			$i++;

			$book->name       = $i;

			$book->ownPage[]  = R::dispense( 'page' )->setAttr( 'name', $i );

			$book->author     = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->coauthor   = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->collection = R::dispense( 'collection' )->setAttr( 'name', $i );
		}

		R::storeAll( $books );

		$books = R::find( 'book' );

		R::preload( $books, array( 'coauthor' => 'author', 'author', 'collection' ) );

		R::nuke();

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->author->name, strval( ++$i ) );
		}

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->fetchAs( 'author' )->coauthor->name, strval( ++$i ) );
		}

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->collection->name, strval( ++$i ) );
		}

		// Crud
		$books = R::dispense( 'book', 3 );

		$i = 0;

		foreach ( $books as $book ) {
			$i++;

			$book->name       = $i;

			$book->ownPage[]  = R::dispense( 'page' )->setAttr( 'name', $i );

			$book->author     = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->coauthor   = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->collection = R::dispense( 'collection' )->setAttr( 'name', $i );
		}

		R::storeAll( $books );

		$books = R::find( 'book' );

		R::preload( $books, array( 'coauthor' => 'author', 'author', 'collection' ) );

		$i = 0;
		foreach ( $books as $book ) {
			$book->author->name .= 'nth';
		}

		$i = 0;
		foreach ( $books as $book ) {
			$book->fetchAs( 'author' )->coauthor->name .= 'nth';
		}

		$i = 0;
		foreach ( $books as $book ) {
			$book->collection->name .= 'nth';
		}

		R::storeAll( $books );

		$books = R::find( 'books' );

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->author->name, strval( ++$i ) . 'nth' );
		}

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->fetchAs( 'author' )->coauthor->name, strval( ++$i ) . 'nth' );
		}

		$i = 0;
		foreach ( $books as $book ) {
			asrt( $book->collection->name, strval( ++$i ) . 'nth' );
		}
	}

	public function testMultipleSameParents()
	{
		$author = R::dispense( 'author' );

		$author->setAttr( 'name', 'John' );

		R::store( $author );

		$books = R::dispense( 'book', 3 );

		$books[0]->title = 'First book';
		$books[1]->title = 'Second book';
		$books[2]->title = 'Third book';

		$author->ownBook = $books;

		R::store( $author );

		$collection = R::findAll( 'book' );

		R::preload( $collection, array( 'author' ) );

		R::nuke();

		foreach ( $collection as $item ) {
			asrt( $item->author->name, 'John' );
		}
	}

	public function testNested()
	{
		$authors = R::dispense( 'author', 2 );

		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText[] = R::dispense( 'text', 1 );
				}
			}
		}

		R::storeAll( $authors );

		$texts = R::find( 'text' );

		R::nuke();

		$text = reset( $texts );

		asrt( (int) ( $text->page->id ), 0 );
	}

	public function testNestedPreload()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}
			}
		}

		R::storeAll( $authors );

		$texts = R::find( 'text' );

		R::preload( $texts, array( 'page', 'page.book', 'page.book.author' ) );

		R::nuke();

		$text = reset( $texts );

		asrt( ( $text->page->id ) > 0, true );
		asrt( ( $text->page->book->id ) > 0, true );
		asrt( ( $text->page->book->author->id ) > 0, true );

	}

	public function testNestedPreloadAlias()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->alias( 'coauthor' )->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->alias( 'coauthor' )->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}
			}
		}

		R::storeAll( $authors );

		$texts = R::find( 'text' );

		R::preload( $texts, array( 'page', 'page.book', 'page.book.coauthor' => 'author' ) );

		R::nuke();

		$text = reset( $texts );

		asrt( ( $text->page->id ) > 0, true );
		asrt( ( $text->page->book->id ) > 0, true );
		asrt( ( $text->page->book->fetchAs( 'author' )->coauthor->id ) > 0, true );
	}

	public function testPreloadOwnlistShort()
	{
		$tree = R::dispense( 'tree' );

		$tree->ownLeaf = R::dispense( 'leaf', 3 );

		$id = R::store( $tree );

		$tree = R::load( 'tree', $id );

		R::preload( $tree, 'ownLeaf|leaf' );

		R::nuke();

		asrt( count( $tree->ownLeaf ), 3 );
	}

	public function testPreloadOwnlist()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}
			}
		}

		R::storeAll( $authors );

		$authors = R::find( 'author' );

		R::preload( $authors, 'ownBook|book,ownBook.ownPage|page,ownBook.ownPage.ownText|text' );

		R::nuke();

		$author = reset( $authors );

		asrt( count( $author->ownBook ), 2 );

		$book = reset( $author->ownBook );

		asrt( count( $book->ownPage ), 2 );

		$page = reset( $book->ownPage );

		asrt( count( $page->ownText ), 2 );
	}

	public function testPreloadSharedLists()
	{
		list( $a1, $a2, $a3 ) = R::dispense( 'army', 3 );
		list( $v1, $v2, $v3 ) = R::dispense( 'village', 3 );

		$v1->name = 'a';
		$v2->name = 'b';
		$v3->name = 'c';

		$a1->name = 'one';
		$a2->name = 'two';
		$a3->name = 'three';

		$a1->sharedVillage = array( $v1, $v3 );
		$a2->sharedVillage = array( $v3, $v1, $v2 );
		$a3->sharedVillage = array();

		list( $g, $e ) = R::dispense( 'people', 2 );

		$g->nature = 'good';
		$e->nature = 'evil';

		$v1->sharedPeople = array( $g );
		$v2->sharedPeople = array( $e, $g );
		$v3->sharedPeople = array( $g );

		R::storeAll( array( $a1, $a2, $a3 ) );

		$armies = R::find( 'army' );

		R::each(
			$armies,
			array( 'sharedVillage' => 'village', 'sharedVillage.sharedPeople' => 'people' ),
			function ( $army, $villages, $people ) {
				if ( $army->name == 'one' ) {
					$names = array();

					foreach ( $villages as $village ) {
						$names[] = $village->name;
					}

					sort( $names );

					$names = implode( ',', $names );

					asrt( $names, 'a,c' );
				}

				if ( $army->name == 'two' ) {
					$names = array();

					foreach ( $villages as $village ) {
						$names[] = $village->name;
					}

					sort( $names );

					$names = implode( ',', $names );

					asrt( $names, 'a,b,c' );
				}

				if ( $army->name == 'three' ) {
					asrt( count( $villages ), 0 );
				}
			}
		);

		R::nuke();

		foreach ( $armies as $army ) {
			$villages = $army->sharedVillage;

			$ppl = array();

			foreach ( $villages as $village ) {
				$ppl = array_merge( $ppl, $village->sharedPeople );
			}

			if ( $army->name == 'one' ) {
				asrt( count( $villages ), 2 );
				asrt( count( $ppl ), 2 );

				foreach ( $ppl as $p ) {
					if ( $p->nature !== 'good' ) fail();
				}

				$names = array();

				foreach ( $villages as $village ) {
					$names[] = $village->name;
				}

				sort( $names );

				$names = implode( ',', $names );

				asrt( $names, 'a,c' );

				$natures = array();

				foreach ( $ppl as $p ) {
					$natures[] = $p->nature;
				}

				sort( $natures );

				$natures = implode( ',', $natures );

				asrt( $natures, 'good,good' );
			}

			if ( $army->name == 'two' ) {
				asrt( count( $villages ), 3 );
				asrt( count( $ppl ), 4 );

				$names = array();

				foreach ( $villages as $village ) {
					$names[] = $village->name;
				}

				sort( $names );

				$names = implode( ',', $names );

				asrt( $names, 'a,b,c' );

				$natures = array();

				foreach ( $ppl as $p ) {
					$natures[] = $p->nature;
				}

				sort( $natures );

				$natures = implode( ',', $natures );

				asrt( $natures, 'evil,good,good,good' );
			}

			if ( $army->name == 'three' ) {
				asrt( count( $villages ), 0 );
				asrt( count( $ppl ), 0 );
			}
		}

		// Now test with empty beans
		$authors = R::dispense( 'author', 2 );

		R::storeAll( $authors );

		$authors = R::find( 'author' );

		R::preload( $authors, array( 'ownBook' => 'book', 'ownBook.ownPage' => 'page', 'ownBook.ownPage.ownText' => 'text' ) );

		$author = reset( $authors );

		asrt( count( $author->ownBook ), 0 );

		$texts = R::dispense( 'text', 2 );

		R::storeAll( $texts );

		$texts = R::find( 'text' );

		R::preload( $texts, array( 'page', 'page.book' ) );

		$text = reset( $texts );

		asrt( $text->page, null );
	}

	public function testClosure()
	{
		$books = R::dispense( 'book', 3 );

		$i = 0;

		foreach ( $books as $book ) {
			$i++;

			$book->name = $i;

			$book->ownPage[] = R::dispense( 'page' )->setAttr( 'name', $i );

			$book->author = R::dispense( 'author' )->setAttr( 'name', $i );

			$book->coauthor = R::dispense( 'author' )->setAttr( 'name', $i );
		}

		R::storeAll( $books );

		$books = R::find( 'book' );

		$hasNuked = false;

		R::preload(
			$books,
			'author',
			function ( $book, $author ) use ( &$hasNuked ) {
				if ( !$hasNuked ) {
					R::nuke();

					$hasNuked = true;
				}

				asrt( $book->getMeta( 'type' ), 'book' );
				asrt( $author->getMeta( 'type' ), 'author' );
			}
		);
	}

	public function testClosureAbbreviations()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}
			}
		}

		R::storeAll( $authors );

		$texts = R::find( 'text' );

		$hasNuked = false;

		R::preload(
			$texts,
			'page,*.book,*.author',
			function ( $text, $page, $book, $author ) use ( &$hasNuked ) {
				if ( !$hasNuked ) {
					R::nuke();

					$hasNuked = true;
				}

				asrt( $text->getMeta( 'type' ), 'text' );
				asrt( $page->getMeta( 'type' ), 'page' );
				asrt( $book->getMeta( 'type' ), 'book' );
				asrt( $author->getMeta( 'type' ), 'author' );
			}
		);
	}

	public function testClosureAbbreviationsSameLevel()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}
			}
		}

		foreach ( $authors as $author ) {
			foreach ( $author->ownBook as $book ) {
				$book->shelf = R::dispense( 'shelf' )->setAttr( 'name', 'abc' );
			}
		}

		R::storeAll( $authors );

		$texts = R::find( 'text' );

		$hasNuked = false;

		R::preload(
			$texts,
			'page,*.book,*.author,&.shelf',
			function ( $text, $page, $book, $author, $shelf ) use ( &$hasNuked ) {
				if ( !$hasNuked ) {
					R::nuke();
					$hasNuked = true;
				}
				asrt( $text->getMeta( 'type' ), 'text' );
				asrt( $page->getMeta( 'type' ), 'page' );
				asrt( ( $page->id > 0 ), true );
				asrt( $book->getMeta( 'type' ), 'book' );
				asrt( ( $book->id > 0 ), true );
				asrt( $author->getMeta( 'type' ), 'author' );
				asrt( $shelf->getMeta( 'type' ), 'shelf' );
			}
		);
	}

	public function testClosureAbbreviationOwnlist()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}
			}
		}

		R::storeAll( $authors );

		$pages    = R::find( 'page' );

		$hasNuked = false;

		R::preload(
			$pages,
			array( 'book', '*.author', 'ownText' => 'text' ),
			function ( $page, $book, $author, $texts ) use ( &$hasNuked ) {
				if ( !$hasNuked ) {
					R::nuke();

					$hasNuked = true;
				}

				asrt( $page->getMeta( 'type' ), 'page' );

				asrt( ( $page->id > 0 ), true );

				asrt( $book->getMeta( 'type' ), 'book' );

				asrt( ( $book->id > 0 ), true );

				asrt( $author->getMeta( 'type' ), 'author' );

				asrt( ( $author->id > 0 ), true );

				asrt( is_array( $texts ), true );

				asrt( count( $texts ), 2 );

				$first = reset( $texts );

				asrt( $first->getMeta( 'type' ), 'text' );
			}
		);
	}

	public function testVariations()
	{
		$authors = R::dispense( 'author', 2 );
		foreach ( $authors as $author ) {
			$author->ownBook = R::dispense( 'book', 2 );

			foreach ( $author->ownBook as $book ) {
				$book->ownPage = R::dispense( 'page', 2 );
				$book->cover   = R::dispense( 'cover', 1 );

				foreach ( $book->ownPage as $page ) {
					$page->ownText = R::dispense( 'text', 2 );
				}

				foreach ( $book->ownPage as $page ) {
					$page->ownPicture = R::dispense( 'picture', 3 );
				}
			}
		}

		R::storeAll( $authors );

		$texts = R::find( 'text' );

		$hasNuked = false;

		$i = 0;

		R::each(
			$texts,
			array(
				'page',
				'*.ownPicture' => 'picture',
				'&.book',
				'*.cover',
				'&.author'
			),
			function ( $t, $p, $x, $b, $c, $a ) use ( &$hasNuked, &$i ) {
				if ( !$hasNuked ) {
					R::nuke();

					$hasNuked = true;
				}
				$i++;
				asrt( count( $x ), 3 );

				asrt( ( $p->id > 0 ), true );
				asrt( ( $c->id > 0 ), true );
				asrt( ( $t->id > 0 ), true );
				asrt( ( $b->id > 0 ), true );
				asrt( ( $a->id > 0 ), true );

				asrt( $t->getMeta( 'type' ), 'text' );
				asrt( $p->getMeta( 'type' ), 'page' );
				asrt( $c->getMeta( 'type' ), 'cover' );
				asrt( $b->getMeta( 'type' ), 'book' );
				asrt( $a->getMeta( 'type' ), 'author' );

				$x1 = reset( $x );

				asrt( $x1->getMeta( 'type' ), 'picture' );
			}
		);

		// Follows the first parameter
		asrt( $i, 16 );
	}

	public function unnamed19()
	{
		// Extra, test in combination with writer cache
		R::$writer->setUseCache( true );

		$villages = R::dispense( 'village', 3 );

		foreach ( $villages as $v ) {
			$v->ownBuilding = R::dispense( 'building', 3 );
		}

		foreach ( $villages as $v ) {
			foreach ( $v->ownBuilding as $b ) {
				$b->ownFurniture = R::dispense( 'furniture', 2 );
			}
		}

		$armies = R::dispense( 'army', 3 );

		$villages[0]->sharedArmy = array( $armies[1], $armies[2] );
		$villages[1]->sharedArmy = array( $armies[0], $armies[1] );
		$villages[2]->sharedArmy = array( $armies[2] );

		$soldiers = R::dispense( 'soldier', 4 );

		$armies[0]->sharedSoldier = array( $soldiers[0], $soldiers[1], $soldiers[2] );
		$armies[1]->sharedSoldier = array( $soldiers[2], $soldiers[1] );
		$armies[2]->sharedSoldier = array( $soldiers[2] );

		$counter = 0;

		foreach ( $villages as $v ) {
			$v->name = $counter++;
		}

		$counter = 0;

		foreach ( $armies as $a ) {
			$a->name = $counter++;
		}

		$counter = 0;

		foreach ( $soldiers as $s ) {
			$s->name = $counter++;
		}

		$buildings = R::dispense( 'building', 4 );

		$villages[0]->ownBuilding = array( $buildings[0] );
		$villages[1]->ownBuilding = array( $buildings[1], $buildings[2] );
		$villages[2]->ownBuilding = array( $buildings[3] );

		$counter = 0;

		foreach ( $buildings as $b ) $b->name = $counter++;

		$books = R::dispense( 'book', 5 );

		$counter = 0;

		foreach ( $books as $b ) $b->name = $counter++;

		$buildings[0]->ownBook = array( $books[0], $books[1] );
		$buildings[1]->ownBook = array( $books[2] );
		$buildings[2]->ownBook = array( $books[3], $books[4] );

		$world = R::dispense( 'world' );

		$world->name = 'w1';

		$villages[1]->world = $world;

		R::storeAll( $villages );

		$towns = R::find( 'village' );

		$counter = 0;

		R::each(
			$towns,
			array(
				'sharedArmy'               => 'army',
				'sharedArmy.sharedSoldier' => 'soldier',
				'ownBuilding'              => 'building',
				'ownBuilding.ownBook'      => 'book',
				'world'
			),
			function ( $t, $a, $s, $b, $x, $w ) use ( &$counter ) {
				if ( $counter === 0 ) {
					asrt( $w, null );

					asrt( (string) $t->name, '0' );

					asrt( count( $t->sharedArmy ), 2 );

					$list = array();
					foreach ( $a as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '1,2' );

					$list = array();

					foreach ( $s as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '1,2' );

					$list = array();
					foreach ( $b as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0' );

					$list = array();
					foreach ( $x as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0,1' );

					$first = reset( $a );

					asrt( $first->getMeta( 'type' ), 'army' );

					$first = reset( $s );

					asrt( $first->getMeta( 'type' ), 'soldier' );

					$first = reset( $b );

					asrt( $first->getMeta( 'type' ), 'building' );

					$first = reset( $x );

					asrt( $first->getMeta( 'type' ), 'book' );
				} elseif ( $counter === 1 ) {
					asrt( $w->name, 'w1' );

					asrt( (string) $t->name, '1' );

					asrt( count( $t->sharedArmy ), 2 );

					$list = array();
					foreach ( $a as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0,1' );

					$list = array();
					foreach ( $s as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0,1,2' );

					$list = array();
					foreach ( $b as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '1,2' );

					$list = array();
					foreach ( $x as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '2,3,4' );

					$first = reset( $a );

					asrt( $first->getMeta( 'type' ), 'army' );

					$first = reset( $s );

					asrt( $first->getMeta( 'type' ), 'soldier' );

					$first = reset( $b );

					asrt( $first->getMeta( 'type' ), 'building' );

					$first = reset( $x );

					asrt( $first->getMeta( 'type' ), 'book' );
				} elseif ( $counter === 2 ) {
					asrt( $w, null );

					asrt( (string) $t->name, '2' );

					asrt( count( $t->sharedArmy ), 1 );

					$list = array();
					foreach ( $a as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '2' );

					$list = array();
					foreach ( $s as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '2' );

					$list = array();
					foreach ( $b as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '3' );

					asrt( count( $x ), 0 );

					$first = reset( $a );

					asrt( $first->getMeta( 'type' ), 'army' );

					$first = reset( $s );

					asrt( $first->getMeta( 'type' ), 'soldier' );

					$first = reset( $b );

					asrt( $first->getMeta( 'type' ), 'building' );
				}

				$counter++;
			}
		);
	}

	public function unnamed20()
	{
		R::$writer->setUseCache( false );

		$books = R::dispense( 'book', 4 );

		foreach ( $books as $b ) $b->ownPage = R::dispense( 'page', 2 );

		foreach ( $books as $b ) $b->sharedAd = R::dispense( 'ad', 2 );

		R::storeAll( $books );

		$books = R::find( 'book' );

		R::preload(
			$books,
			array(
				'ownPage'  => array( 'page', array( ' AND page.id > 0 LIMIT ? ', array( 2 ) ) ),
				'sharedAd' => array( 'ad', array( ' AND ad.id > 0 LIMIT ? ', array( 4 ) ) )
			)
		);

		asrt( count( $books[1]->ownPage ), 2 );
		asrt( count( $books[1]->sharedAd ), 2 );

		asrt( count( $books[2]->ownPage ), 0 );
		asrt( count( $books[2]->sharedAd ), 2 );

		asrt( count( $books[3]->ownPage ), 0 );
		asrt( count( $books[3]->sharedAd ), 0 );
	}

	public function unnamed21()
	{
		R::$writer->setUseCache( false );

		$villages = R::dispense( 'village', 3 );

		foreach ( $villages as $v ) {
			$v->ownBuilding = R::dispense( 'building', 3 );
		}

		foreach ( $villages as $v ) {
			foreach ( $v->ownBuilding as $b ) {
				$b->ownFurniture = R::dispense( 'furniture', 2 );
			}
		}

		$armies = R::dispense( 'army', 3 );

		$villages[0]->sharedArmy = array( $armies[1], $armies[2] );
		$villages[1]->sharedArmy = array( $armies[0], $armies[1] );
		$villages[2]->sharedArmy = array( $armies[2] );

		$soldiers = R::dispense( 'soldier', 4 );

		$armies[0]->sharedSoldier = array( $soldiers[0], $soldiers[1], $soldiers[2] );
		$armies[1]->sharedSoldier = array( $soldiers[2], $soldiers[1] );
		$armies[2]->sharedSoldier = array( $soldiers[2] );

		$counter = 0;

		foreach ( $villages as $v ) $v->name = $counter++;

		$counter = 0;

		foreach ( $armies as $a ) $a->name = $counter++;

		$counter = 0;

		foreach ( $soldiers as $s ) $s->name = $counter++;

		$buildings = R::dispense( 'building', 4 );

		$villages[0]->ownBuilding = array( $buildings[0] );
		$villages[1]->ownBuilding = array( $buildings[1], $buildings[2] );
		$villages[2]->ownBuilding = array( $buildings[3] );

		$counter = 0;

		foreach ( $buildings as $b ) $b->name = $counter++;

		$books = R::dispense( 'book', 5 );

		$counter = 0;

		foreach ( $books as $b ) $b->name = $counter++;

		$buildings[0]->ownBook = array( $books[0], $books[1] );
		$buildings[1]->ownBook = array( $books[2] );
		$buildings[2]->ownBook = array( $books[3], $books[4] );

		$world = R::dispense( 'world' );

		$world->name = 'w1';

		$villages[1]->world = $world;

		R::storeAll( $villages );

		$towns = R::find( 'village' );

		$counter = 0;

		R::each(
			$towns,
			array(
				'sharedArmy'               => 'army',
				'sharedArmy.sharedSoldier' => array( 'soldier', array( ' ORDER BY soldier.name DESC ', array() ) ),
				'ownBuilding'              => array( 'building', array( ' ORDER BY building.name DESC ', array() ) ),
				'ownBuilding.ownBook'      => 'book',
				'world'
			),
			function ( $t, $a, $s, $b, $x, $w ) use ( &$counter ) {
				if ( $counter === 0 ) {
					asrt( $w, null );

					asrt( (string) $t->name, '0' );

					asrt( count( $t->sharedArmy ), 2 );

					$list = array();
					foreach ( $a as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '1,2' );

					$list = array();
					foreach ( $s as $item ) {
						$list[] = $item->name;
					}

					asrt( implode( ',', $list ), '2,1' );

					$list = array();

					foreach ( $b as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0' );

					$list = array();
					foreach ( $x as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0,1' );

					$first = reset( $a );

					asrt( $first->getMeta( 'type' ), 'army' );

					$first = reset( $s );

					asrt( $first->getMeta( 'type' ), 'soldier' );

					$first = reset( $b );

					asrt( $first->getMeta( 'type' ), 'building' );

					$first = reset( $x );

					asrt( $first->getMeta( 'type' ), 'book' );
				} elseif ( $counter === 1 ) {
					asrt( $w->name, 'w1' );

					asrt( (string) $t->name, '1' );

					asrt( count( $t->sharedArmy ), 2 );

					$list = array();
					foreach ( $a as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0,1' );

					$list = array();
					foreach ( $s as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '0,1,2' );

					$list = array();
					foreach ( $b as $item ) {
						$list[] = $item->name;
					}

					asrt( implode( ',', $list ), '2,1' );

					$list = array();

					foreach ( $x as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '2,3,4' );

					$first = reset( $a );

					asrt( $first->getMeta( 'type' ), 'army' );

					$first = reset( $s );

					asrt( $first->getMeta( 'type' ), 'soldier' );

					$first = reset( $b );

					asrt( $first->getMeta( 'type' ), 'building' );

					$first = reset( $x );
					asrt( $first->getMeta( 'type' ), 'book' );
				} elseif ( $counter === 2 ) {
					asrt( $w, null );

					asrt( (string) $t->name, '2' );

					asrt( count( $t->sharedArmy ), 1 );

					$list = array();
					foreach ( $a as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '2' );

					$list = array();
					foreach ( $s as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '2' );

					$list = array();
					foreach ( $b as $item ) {
						$list[] = $item->name;
					}

					sort( $list );

					asrt( implode( ',', $list ), '3' );

					asrt( count( $x ), 0 );

					$first = reset( $a );

					asrt( $first->getMeta( 'type' ), 'army' );

					$first = reset( $s );

					asrt( $first->getMeta( 'type' ), 'soldier' );

					$first = reset( $b );

					asrt( $first->getMeta( 'type' ), 'building' );
				}

				$counter++;
			}
		);
	}
}
