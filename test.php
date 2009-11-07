<?php 

//
//                   ._______ __________  .______________
//_______   ____   __| _/    |   \      \ |   \__    ___/
//\_  __ \_/ __ \ / __ ||    |   /   |   \|   | |    |
// |  | \/\  ___// /_/ ||    |  /    |    \   | |    |
// |__|    \___  >____ ||______/\____|__  /___| |____|
//            \/     \/                \/

 // Written by Gabor de Mooij Copyright (c) 2009
/**
 * RedUNIT (Test Suite)
 * @package 		test.php
 * @description		Series of Unit Tests for RedBean
 * @author			Gabor de Mooij
 * @license			BSD
 */

function printtext( $text ) {
	if ($_SERVER["DOCUMENT_ROOT"]) {
		echo "<BR>".$text;
	}
	else {
		echo "\n".$text;
	}
}



//New test functions, no objects required here
function asrt( $a, $b ) {
	if ($a === $b) {
		global $tests;
		$tests++;
		print( "[".$tests."]" );
	}
	else {
		printtext("FAILED TEST: EXPECTED $b BUT GOT: $a ");
		fail();
	}
}

function pass() {
	global $tests;
	$tests++;
	print( "[".$tests."]" );
}

function fail() {
	printtext("FAILED TEST");
        debug_print_backtrace();
	exit;
}


function testpack($name) {
	printtext("testing: ".$name);
}


require("RedBean/redbean.inc.php");
$toolbox = RedBean_Setup::kickstartDev( "mysql:host=localhost;dbname=oodb","root","" );

//Observable Mock Object
class ObservableMock extends RedBean_Observable {
    public function test( $eventname, $info ) {
        $this->signal($eventname, $info);
    }
}

class ObserverMock implements RedBean_Observer {
    public $event = false;
    public $info = false;
    public function onEvent($event, $info) {
        $this->event = $event;
        $this->info = $info;
    }
}

class NullWriter implements RedBean_QueryWriter {

	//Arguments
	public $createTableArgument = NULL;
	public $getColumnsArgument = NULL;
	public $scanTypeArgument = NULL;
	public $addColumnArguments = array();
	public $codeArgument = NULL;
	public $widenColumnArguments = array();
	public $updateRecordArguments = array();
	public $insertRecordArguments = array();
	public $selectRecordArguments = array();
	public $deleteRecordArguments = array();
	public $checkChangesArguments = array();
	public $addUniqueIndexArguments = array();

	//Return values
	public $returnTables = array();
	public $returnGetColumns = array();
	public $returnScanType = 1;
	public $returnAddColumn = NULL;
	public $returnCode = NULL;
	public $returnWidenColumn = NULL;
	public $returnUpdateRecord = NULL;
	public $returnInsertRecord = NULL;
	public $returnSelectRecord = NULL;
	public $returnDeleteRecord = NULL;
	public $returnCheckChanges = NULL;
	public $returnAddUniqueIndex = NULL;

	//Dummy implementations
	public function getTables(){ return $this->returnTables; }
	public function createTable( $table ){ $this->createTableArgument = $table; }
    public function getColumns( $table ){ $this->getColumnsArgument = $table; return $this->returnGetColumns; }
	public function scanType( $value ){ $this->scanTypeArgument = $value; return $this->returnScanType; }
    public function addColumn( $table, $column, $type ){
		$this->addColumnArguments = array( $table, $column, $type );
		return $this->returnAddColumn;
	}
    public function code( $typedescription ){ $this->codeArgument = $typedescription;
		return $this->returnCode;
	}
    public function widenColumn( $table, $column, $type ){
		$this->widenColumnArguments = array($table, $column, $type);
		return $this->returnWidenColumn;
	}
    public function updateRecord( $table, $updatevalues, $id){
		$this->updateRecordArguments = array($table, $updatevalues, $id);
		return $this->returnUpdateRecord;
	}
    public function insertRecord( $table, $insertcolumns, $insertvalues ){
		$this->insertRecordArguments = array( $table, $insertcolumns, $insertvalues );
		return $this->returnInsertRecord;
	}
    public function selectRecord($type, $ids){
		$this->selectRecordArguments = array($type, $ids);
		return $this->returnSelectRecord;
	}
	public function deleteRecord( $table, $id){
		$this->deleteRecordArguments = array($table, "id", $id);
		return $this->returnDeleteRecord;
	}
    public function checkChanges($type, $id, $logid){
		$this->checkChangesArguments = array($type, $id, $logid);
		return $this->returnCheckChanges;
	}
	public function addUniqueIndex( $table,$columns ){
		$this->addUniqueIndexArguments=array($table,$columns);
		return $this->returnAddUniqueIndex;
	}
	public function reset() {
		$this->createTableArgument = NULL;
		$this->getColumnsArgument = NULL;
		$this->scanTypeArgument = NULL;
		$this->addColumnArguments = array();
		$this->codeArgument = NULL;
		$this->widenColumnArguments = array();
		$this->updateRecordArguments = array();
		$this->insertRecordArguments = array();
		$this->selectRecordArguments = array();
		$this->deleteRecordArguments = array();
		$this->checkChangesArguments = array();
		$this->addUniqueIndexArguments = array();

		$this->returnTables = array();
		$this->returnGetColumns = array();
		$this->returnScanType = 1;
		$this->returnAddColumn = NULL;
		$this->returnCode = NULL;
		$this->returnWidenColumn = NULL;
		$this->returnUpdateRecord = NULL;
		$this->returnInsertRecord = NULL;
		$this->returnSelectRecord = NULL;
		$this->returnDeleteRecord = NULL;
		$this->returnCheckChanges = NULL;
		$this->returnAddUniqueIndex = NULL;
	}
}

$nullWriter = new NullWriter();
$redbean = new RedBean_OODB( $nullWriter );

//Section A: Config Testing
testpack("CONFIG TEST");
//Can we access the required exceptions?
asrt(class_exists("RedBean_Exception_FailedAccessBean"),true);
asrt(class_exists("RedBean_Exception_Security"),true);
asrt(class_exists("RedBean_Exception_SQL"),true);


//Section B: UNIT TESTING
testpack("UNIT TEST RedBean OODB: Dispense");
$page = $redbean->dispense("page");
asrt(((bool)$page->getMeta("type")),true);
asrt(isset($page->id),true);
asrt(($page->getMeta("type")),"page");
asrt(($page->id),0);
try{ $redbean->dispense(""); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try{ $redbean->dispense("."); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try{ $redbean->dispense("-"); fail(); }catch(RedBean_Exception_Security $e){ pass(); }


testpack("UNIT TEST RedBean OODB: Check");
$bean = $redbean->dispense("page");
$bean->name = array("1");
try{ $redbean->store($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try{ $redbean->check($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
$bean->name = new RedBean_OODBBean;
try{ $redbean->store($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try{ $redbean->check($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
$prop = ".";
$bean->$prop = 1;
try{ $redbean->store($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try{ $redbean->check($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
$prop = "-";
$bean->$prop = 1;
try{ $redbean->store($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try{ $redbean->check($bean); fail(); }catch(RedBean_Exception_Security $e){ pass(); }


testpack("UNIT TEST RedBean OODB: Load");
$bean = $redbean->load("typetest",2); 
$nullWriter->returnSelectRecord = array();
asrt($nullWriter->selectRecordArguments[0],"typetest");
asrt($nullWriter->selectRecordArguments[1],array(2));
asrt($bean->id,0);
$nullWriter->returnSelectRecord = array(array("name"=>"abc","id"=>3));
$bean = $redbean->load("typetest",3);
asrt($nullWriter->selectRecordArguments[0],"typetest");
asrt($nullWriter->selectRecordArguments[1],array(3));
asrt($bean->id,3);
try { $bean = $redbean->load("typetest",-2); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try { $bean = $redbean->load("typetest",0); pass(); }catch(RedBean_Exception_Security $e){ fail(); }
try { $bean = $redbean->load("typetest",2.1); pass(); }catch(RedBean_Exception_Security $e){ fail(); }
try { $bean = $redbean->load(" ",3); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try { $bean = $redbean->load(".",3); fail(); }catch(RedBean_Exception_Security $e){ pass(); }
try { $bean = $redbean->load("type.test",3); fail(); }catch(RedBean_Exception_Security $e){ pass(); }

testpack("UNIT TEST RedBean OODB: Batch");
$nullWriter->reset();
$beans = $redbean->batch("typetest",array(2));
$nullWriter->returnSelectRecord = array();
asrt($nullWriter->selectRecordArguments[0],"typetest");
asrt($nullWriter->selectRecordArguments[1],array(2));
asrt(count($beans),0);
$nullWriter->reset();
$nullWriter->returnSelectRecord = array(array("name"=>"abc","id"=>3));
$beans = $redbean->batch("typetest",array(3));
asrt($nullWriter->selectRecordArguments[0],"typetest");
asrt($nullWriter->selectRecordArguments[1],array(3));
asrt(count($beans),1);


testpack("UNIT TEST RedBean OODB: Store");
$nullWriter->reset();
$bean = $redbean->dispense("bean");
$bean->name = "coffee";
$nullWriter->returnScanType = 91239;
$nullWriter->returnInsertRecord = 1234;
asrt($redbean->store($bean),1234);
asrt($nullWriter->getColumnsArgument,"bean");
asrt($nullWriter->createTableArgument,"bean");
asrt($nullWriter->scanTypeArgument,"coffee");
asrt($nullWriter->codeArgument,NULL);
//print_r($nullWriter);
asrt($nullWriter->addColumnArguments,array("bean","name",91239));
asrt($nullWriter->insertRecordArguments,array("bean",array("name"),array(array("coffee"))));
asrt($nullWriter->addUniqueIndexArguments,array());
asrt($nullWriter->updateRecordArguments,array());
asrt($nullWriter->widenColumnArguments,array());
$nullWriter->reset();
$bean = $redbean->dispense("bean");
$bean->name = "chili";
$bean->id=9876;
$nullWriter->returnCode = 0;
$nullWriter->returnScanType = 777;
$nullWriter->returnTables=array("bean");
$nullWriter->returnGetColumns=array("name"=>13);
asrt($redbean->store($bean),9876);
asrt($nullWriter->getColumnsArgument,"bean");
asrt($nullWriter->createTableArgument,NULL);
asrt($nullWriter->scanTypeArgument,"chili");
asrt($nullWriter->codeArgument,13);
asrt($nullWriter->addColumnArguments,array());
asrt($nullWriter->insertRecordArguments,array());
asrt($nullWriter->addUniqueIndexArguments,array());
asrt($nullWriter->updateRecordArguments,array("bean",array(array("property"=>"name","value"=>"chili")),9876 ));
asrt($nullWriter->widenColumnArguments,array("bean","name", 777));

testpack("UNIT TEST RedBean OODB: Freeze");
$nullWriter->reset();
$redbean->freeze(true);
$bean = $redbean->dispense("bean");
$bean->name = "coffee";
$nullWriter->returnScanType = 91239;
$nullWriter->returnInsertRecord = 1234;
asrt($redbean->store($bean),1234);
asrt($nullWriter->getColumnsArgument,"bean");
asrt($nullWriter->createTableArgument,NULL);
asrt($nullWriter->scanTypeArgument,NULL);
asrt($nullWriter->codeArgument,NULL);
asrt($nullWriter->addColumnArguments,array());
asrt($nullWriter->insertRecordArguments,array("bean",array("name"),array(array("coffee"))));
asrt($nullWriter->addUniqueIndexArguments,array());
asrt($nullWriter->updateRecordArguments,array());
asrt($nullWriter->widenColumnArguments,array());
$redbean->freeze(false);


testpack("UNIT TEST RedBean OODBBean: Meta Information");
$bean = new RedBean_OODBBean;
$bean->setMeta( "this.is.a.custom.metaproperty" , "yes" );
asrt($bean->getMeta("this.is.a.custom.metaproperty"),"yes");
$bean->setMeta( "test", array( "one" => 123 ));
asrt($bean->getMeta("test.one"),123);
$bean->setMeta( "arr", array(1,2) );
asrt(is_array($bean->getMeta("arr")),true);
asrt($bean->getMeta("nonexistant"),NULL);
asrt($bean->getMeta("nonexistant","abc"),"abc");
asrt($bean->getMeta("nonexistant.nested"),NULL);
asrt($bean->getMeta("nonexistant,nested","abc"),"abc");
$bean->setMeta("test.two","second");
asrt($bean->getMeta("test.two"),"second");
$bean->setMeta("another.little.property","yes");
asrt($bean->getMeta("another.little.property"),"yes");
asrt($bean->getMeta("test.two"),"second");


testpack("UNIT TEST RedBean OODBBean: import");
$bean = new RedBean_OODBBean;
$bean->import(array("a"=>1,"b"=>2));
asrt($bean->a, 1);
asrt($bean->b, 2);

testpack("UNIT TEST RedBean OODBBean: export");
$bean->setMeta("justametaproperty","hellothere");
$arr = $bean->export();
asrt(is_array($arr),true);
asrt(isset($arr["a"]),true);
asrt(isset($arr["b"]),true);
asrt($arr["a"],1);
asrt($arr["b"],2);
asrt(isset($arr["__info"]),false);
$arr = $bean->export( true );
asrt(isset($arr["__info"]),true);
asrt($arr["a"],1);
asrt($arr["b"],2);

//Test observer
testpack("UNIT TEST Observer Mechanism ");
$observable = new ObservableMock();
$observer = new ObserverMock();
$observable->addEventListener("event1",$observer);
$observable->addEventListener("event3",$observer);
$observable->test("event1", "testsignal1");
asrt($observer->event,"event1");
asrt($observer->info,"testsignal1");
$observable->test("event2", "testsignal2");
asrt($observer->event,"event1");
asrt($observer->info,"testsignal1");
$observable->test("event3", "testsignal3");
asrt($observer->event,"event3");
asrt($observer->info,"testsignal3");

$adapter = $toolbox->getDatabaseAdapter();
$writer  = $toolbox->getWriter();
$redbean = $toolbox->getRedBean();

testpack("UNIT TEST Toolbox");
asrt(($adapter instanceof RedBean_DBAdapter),true);
asrt(($writer instanceof RedBean_QueryWriter),true);
asrt(($redbean instanceof RedBean_OODB),true);


$pdo = $adapter->getDatabase();
$pdo->setDebugMode(0);
$pdo->Execute("CREATE TABLE IF NOT EXISTS`hack` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
) ENGINE = MYISAM ;
");
$pdo->Execute("DROP TABLE IF EXISTS page");
$pdo->Execute("DROP TABLE IF EXISTS user");
$pdo->Execute("DROP TABLE IF EXISTS movie");
$pdo->Execute("DROP TABLE IF EXISTS book");
$pdo->Execute("DROP TABLE IF EXISTS author");
$pdo->Execute("DROP TABLE IF EXISTS one");
$pdo->Execute("DROP TABLE IF EXISTS post");
$pdo->Execute("DROP TABLE IF EXISTS page_user");
$pdo->Execute("DROP TABLE IF EXISTS page_page");
$pdo->Execute("DROP TABLE IF EXISTS association");
$pdo->Execute("DROP TABLE IF EXISTS logentry");
$pdo->Execute("DROP TABLE IF EXISTS admin");
$pdo->Execute("DROP TABLE IF EXISTS admin_logentry");
$page = $redbean->dispense("page");

testpack("UNIT TEST Database");
try{ $adapter->exec("an invalid query"); fail(); }catch(RedBean_Exception_SQL $e ){ pass(); }
asrt( (int) $adapter->getCell("SELECT 123") ,123);
asrt( (int) $adapter->getCell("SELECT ?",array("987")) ,987);
asrt( (int) $adapter->getCell("SELECT ?+?",array("987","2")) ,989);
asrt( (int) $adapter->getCell("SELECT :numberOne+:numberTwo",array(
			":numberOne"=>42,":numberTwo"=>50)) ,92);


//Section C: Integration Tests / Regression Tests

testpack("Test RedBean OODB: Insert Record");
$page->name = "my page";
$id = (int) $redbean->store($page);
asrt( $page->id, 1 );
asrt( (int) $pdo->GetCell("SELECT count(*) FROM page"), 1 );
asrt( $pdo->GetCell("SELECT `name` FROM page LIMIT 1"), "my page" );
asrt( $id, 1 );
testpack("Test RedBean OODB: Can we Retrieve a Record? ");
$page = $redbean->load( "page", 1 );
asrt($page->name, "my page");
asrt(( (bool) $page->getMeta("type")),true);
asrt(isset($page->id),true);
asrt(($page->getMeta("type")),"page");
asrt((int)$page->id,$id);



testpack("Test RedBean OODB: Can we Update a Record? ");
$page->name = "new name";
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );


$page->rating = "1";
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( $page->rating, "1" );



$page->rating = 5;
//$page->__info["unique"] = array("name","rating");
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( strval( $page->rating ), "5" );

$page->rating = 300;
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( strval( $page->rating ), "300" );

$page->rating = -2;
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( strval( $page->rating ), "-2" );

$page->rating = 2.5;
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( strval( $page->rating ), "2.5" );

$page->rating = -3.3;
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( strval( $page->rating ), "-3.3" );

$page->rating = "good";
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( $page->rating, "good" );

$longtext = str_repeat('great! because..',100);
$page->rating = $longtext;
$newid = $redbean->store( $page );
asrt( $newid, $id );
$page = $redbean->load( "page", $id );
asrt( $page->name, "new name" );
asrt( $page->rating, $longtext );

$redbean->trash( $page );



asrt( (int) $pdo->GetCell("SELECT count(*) FROM page"), 0 );

testpack("Test RedBean OODB: Batch Loader ");
$page = $redbean->dispense("page");
$page->name = "page no. 1";
$page->rating = 1;
$id1 = $redbean->store($page);
$page = $redbean->dispense("page");
$page->name = "page no. 2";
$id2 = $redbean->store($page);
$batch = $redbean->batch( "page", array($id1, $id2) );
asrt(count($batch),2);
asrt($batch[$id1]->getMeta("type"),"page");
asrt($batch[$id2]->getMeta("type"),"page");
asrt((int)$batch[$id1]->id,$id1);
asrt((int)$batch[$id2]->id,$id2);
$book = $redbean->dispense("book");
$book->name="book 1";
$redbean->store($book);
$book = $redbean->dispense("book");
$book->name="book 2";
$redbean->store($book);
$book = $redbean->dispense("book");
$book->name="book 3";
$redbean->store($book);
$books = $redbean->batch("book", $adapter->getCol("SELECT id FROM book"));
asrt(count($books),3);


//test locking

testpack("Test RedBean Locking: Change Logger method ");
$observers = RedBean_Setup::getAttachedObservers();
$logger = array_pop($observers);
$page = $redbean->dispense("page");
$page->name = "a page";
$id = $redbean->store( $page );
$page = $redbean->load("page", $id);
$otherpage = $redbean->load("page", $id);
asrt(((bool)$page->getMeta("opened")),true);
asrt(((bool)$otherpage->getMeta("opened")),true); 
try{ $redbean->store( $page ); pass(); }catch(Exception $e){ fail(); }
echo '---';
try{ $redbean->store( $otherpage ); fail(); }catch(Exception $e){ pass(); }
asrt(count($logger->testingOnly_getStash()),0); // Stash empty?

testpack("Test Association ");
$user = $redbean->dispense("user");
$user->name = "John";
$redbean->store( $user );
$page = $redbean->dispense("page");
$page->name = "John's page";
$redbean->store($page);
$page2 = $redbean->dispense("page");
$page2->name = "John's second page";
$redbean->store($page2);
$a = new RedBean_AssociationManager( $toolbox );
$a->associate($page, $user);
asrt(count($a->related($user, "page" )),1);
$a->associate($user,$page2);
asrt(count($a->related($user, "page" )),2);
$a->unassociate($page, $user);
asrt(count($a->related($user, "page" )),1);
$a->clearRelations($user, "page");
asrt(count($a->related($user, "page" )),0);
$user2 = $redbean->dispense("user");
$user2->name = "Second User";
$a->set1toNAssoc($user2, $page);
$a->set1toNAssoc($user, $page);
asrt(count($a->related($user2, "page" )),0);
asrt(count($a->related($user, "page" )),1);
$a->set1toNAssoc($user, $page2);
asrt(count($a->related($user, "page" )),2);
$pages = ($redbean->batch("page", $a->related($user, "page" )));
asrt(count($pages),2);
$apage = array_shift($pages);
asrt(($apage->name=="John's page" || $apage->name=="John's second page"),true);
$apage = array_shift($pages);
asrt(($apage->name=="John's page" || $apage->name=="John's second page"),true);
//test save on the fly
$page = $redbean->dispense("page");
$page2 = $redbean->dispense("page");
$page->name="idless page 1";
$page2->name="idless page 1";
$a->associate($page, $page2);
asrt(($page->id>0),true);
asrt(($page2->id>0),true);
$idpage = $page->id;
$idpage2 = $page2->id;

testpack("Cross References");
$ids = $a->related($page, "page");
asrt(count($ids),1);
asrt(intval(array_pop($ids)),intval($idpage2));
$ids = $a->related($page2, "page");
asrt(count($ids),1);
asrt(intval(array_pop($ids)),intval($idpage));
$page3 = $redbean->dispense("page");
$page3->name="third";
$page4 = $redbean->dispense("page");
$page4->name="fourth";
$a->associate($page3,$page2);
$a->associate($page2,$page4);
$a->unassociate($page,$page2);
asrt(count($a->related($page, "page")),0);
$ids = $a->related($page2, "page");
asrt(count($ids),2);
asrt(in_array($page3->id,$ids),true);
asrt(in_array($page4->id,$ids),true);
asrt(in_array($page->id,$ids),false);
asrt(count($a->related($page3, "page")),1);
asrt(count($a->related($page4, "page")),1);
$a->clearRelations($page2, "page");
asrt(count($a->related($page2, "page")),0);
asrt(count($a->related($page3, "page")),0);
asrt(count($a->related($page4, "page")),0);
try{ $a->associate($page2,$page2); pass(); }catch(RedBean_Exception_SQL $e){ fail(); }
try{ $a->associate($page2,$page2); fail(); }catch(RedBean_Exception_SQL $e){ pass(); }
$pageOne = $redbean->dispense("page");
$pageOne->name = "one";
$pageMore = $redbean->dispense("page");
$pageMore->name = "more";
$pageEvenMore = $redbean->dispense("page");
$pageEvenMore->name = "evenmore";
$pageOther = $redbean->dispense("page");
$pageOther->name = "othermore";
$a->set1toNAssoc($pageOther, $pageMore);
$a->set1toNAssoc($pageOne, $pageMore);
$a->set1toNAssoc($pageOne, $pageEvenMore);
asrt(count($a->related($pageOne, "page")),2);
asrt(count($a->related($pageMore, "page")),1);
asrt(count($a->related($pageEvenMore, "page")),1);
asrt(count($a->related($pageOther, "page")),0);

testpack("Test Locking with Assoc");
$page = $redbean->dispense("page");
$user = $redbean->dispense("page");
$id = $redbean->store($page);
$pageII = $redbean->load("page", $id);
$redbean->store($page);
try{ $redbean->store($pageII); fail(); }catch(RedBean_Exception_FailedAccessBean $e){ pass(); }
try{ $a->associate($pageII,$user); fail(); }catch(RedBean_Exception_FailedAccessBean $e){ pass(); }
try{ $a->unassociate($pageII,$user); fail(); }catch(RedBean_Exception_FailedAccessBean $e){ pass(); }
try{ $a->clearRelations($pageII, "user"); fail(); }catch(RedBean_Exception_FailedAccessBean $e){ pass(); }
try{ $redbean->store($page); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
try{ $a->associate($page,$user); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
try{ $a->unassociate($page,$user); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
try{ $a->clearRelations($page, "user"); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
$pageII = $redbean->load("page",$pageII->id); //reload will help
try{ $redbean->store($pageII); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
try{ $a->associate($pageII,$user); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
try{ $a->unassociate($pageII,$user); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }
try{ $a->clearRelations($pageII, "user"); pass(); }catch(RedBean_Exception_FailedAccessBean $e){ fail(); }

//Test whether we can pre-open, or prelock multiple beans at once and
//if the logger fires less queries
testpack("Test Preloader");
class QueryCounter implements RedBean_Observer {
	public $counter = 0;
	public function onEvent($event, $info) {
		$this->counter++;
	}
}
$querycounter = new QueryCounter;
$observers = RedBean_Setup::getAttachedObservers();
$logger = array_pop($observers);
asrt(($logger instanceof RedBean_Observer),true);
$pagea = $redbean->dispense("page");
$pageb = $redbean->dispense("page");
$pagec = $redbean->dispense("page");
$paged = $redbean->dispense("page");
$redbean->store($pagea);
$redbean->store($pageb);
$redbean->store($pagec);
$redbean->store($paged);
$a->associate($pagea, $pageb);
$a->associate($pagea, $pagec);
$a->associate($pagea, $paged);
$ids = $a->related($pagea,"page");
$adapter->exec("TRUNCATE __log");
$adapter->addEventListener("sql_exec", $querycounter);
asrt($querycounter->counter,0); //confirm counter works
asrt(intval($adapter->getCell("SELECT count(*) FROM __log")),0);
asrt($querycounter->counter,1); //confirm counter works
$querycounter->counter=0;
$logger->preLoad("page",$ids);
asrt($querycounter->counter,2); //confirm counter works
asrt(count($ids),3);
asrt(count($logger->testingOnly_getStash()),3); //stash filled with ids
asrt(intval($adapter->getCell("SELECT count(*) FROM __log")),4);
$querycounter->counter=0;
$pages = $redbean->batch("page",$ids);
asrt($querycounter->counter,1);
$querycounter->counter=0;
$pages = $redbean->batch("page",$ids);
asrt($querycounter->counter,4); //compare with normal batch without preloading
//did we save queries (3 is normal, 1 is with preloading)
asrt(intval($adapter->getCell("SELECT count(*) FROM __log")),7);
asrt(count($logger->testingOnly_getStash()),0); //should be used up




testpack("Transactions");
$adapter->startTransaction(); pass();
$adapter->rollback(); pass();
$adapter->startTransaction(); pass();
$adapter->commit(); pass();

testpack("Test Frozen ");
$redbean->freeze( true );
$page = $redbean->dispense("page");
$page->sections = 10;
$page->name = "half a page";
try{$id = $redbean->store($page); fail();}catch(RedBean_Exception_SQL $e){ pass(); }
$redbean->freeze( false );


testpack("Test Developer Interface API");
$post = $redbean->dispense("post");
$post->title = "My First Post";
$post->created = time();
$id = $redbean->store( $post );
$post = $redbean->load("post",$id);
$redbean->trash( $post );
pass();


testpack("Test Finding");
$keys = $adapter->getCol("SELECT id FROM page WHERE `name` LIKE '%John%'");
asrt(count($keys),2);
$pages = $redbean->batch("page", $keys);
asrt(count($pages),2);


testpack("Test (UN)Common Scenarios");
$page = $redbean->dispense("page");
$page->name = "test page";
$id = $redbean->store($page);
$user = $redbean->dispense("user");
$a->unassociate($user,$page); pass(); //no error
$a->unassociate($page,$user); pass(); //no error
$a->clearRelations($page, "user"); pass(); //no error
$a->clearRelations($user, "page"); pass(); //no error
$a->associate($user,$page); pass();
asrt(count($a->related( $user, "page")),1);
asrt(count($a->related( $page, "user")),1);
$a->clearRelations($user, "page"); pass(); //no error
asrt(count($a->related( $user, "page")),0);
asrt(count($a->related( $page, "user")),0);
$page = $redbean->load("page",$id); pass();
asrt($page->name,"test page");

testpack("Test Plugins: Trees ");
$tm = new RedBean_TreeManager($toolbox);
$subpage1 = $redbean->dispense("page");
$subpage2 = $redbean->dispense("page");
$subpage3 = $redbean->dispense("page");
$tm->attach( $page, $subpage1 );
asrt(count($tm->children($page)),1);
$tm->attach( $page, $subpage2 );
asrt(count($tm->children($page)),2);
$tm->attach( $subpage2, $subpage3 );
asrt(count($tm->children($page)),2);
asrt(count($tm->children($subpage2)),1);
asrt(intval($subpage1->parent_id),intval($id));

testpack("Test Integration Pre-existing Schema");
$adapter->exec("ALTER TABLE `page` CHANGE `name` `name` VARCHAR( 254 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ");
$page = $redbean->dispense("page");
$page->name = "Just Another Page In a Table";
$cols = $writer->getColumns("page");
asrt($cols["name"],"varchar(254)");
//$pdo->setDebugMode(1);
$redbean->store( $page );
pass(); //no crash?
$cols = $writer->getColumns("page");
asrt($cols["name"],"varchar(254)"); //must still be same


testpack("Test Plugins: Optimizer");
$one = $redbean->dispense("one");
$one->col = str_repeat('a long text',100);
$redbean->store($one);
require("RedBean/Plugins/Optimizer.php");
$optimizer = new Optimizer( $toolbox );
$redbean->addEventListener("update", $optimizer);
$writer  = $toolbox->getWriter();
$cols = $writer->getColumns("one");
asrt($cols["col"],"text");
$one->col = NULL;
$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"text");
$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"set('1')");

$one->col = str_repeat('a long text',100);
$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"text");
$one->col = 12;
$redbean->store($one);$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"tinyint(3) unsigned");

$one->col = str_repeat('a long text',100);
$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"text");
$one->col = 9000;
$redbean->store($one);$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"int(11) unsigned");

$one->col = str_repeat('a long text',100);
$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"text");
$one->col = 1.23;
$redbean->store($one);$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"double");
$one->col = str_repeat('a long text',100);
$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"text");
$one->col = "short text";
$redbean->store($one);$redbean->store($one);
$cols = $writer->getColumns("one");
asrt($cols["col"],"varchar(255)");


testpack("Test RedBean Extended Journaling with manual Opened modification");
$page = $redbean->dispense("page");
$id = $redbean->store($page);
$page = $redbean->load("page",$id);
$page->name = "antique one";
$redbean->store($page);
$newpage = $redbean->dispense("page");
$newpage->id  = $id;
$newpage->name = "new one";
try{ $redbean->store($newpage); fail(); }catch(Exception $e){ pass(); }
$newpage = $redbean->dispense("page");
$newpage->id  = $id;
$newpage->name = "new one";
$newpage->setMeta("opened",$page->getMeta("opened"));
try{ $redbean->store($newpage); pass(); }catch(Exception $e){ fail(); }



testpack("Test Logger issue");
//issue#Michiel
$rb=$redbean;
$pdo = $adapter->getDatabase();
//$pdo->setDebugMode(1);
$l = $rb->dispense("logentry");
$rb->store($l);
$l = $rb->dispense("admin");
$rb->store($l);
$l = $rb->dispense("logentry");
$rb->store($l);
$l = $rb->dispense("admin");
$rb->store($l);
$admin = $rb->load('admin' , 1);
$a = new RedBean_AssociationManager($toolbox);
$log = $rb->load('logentry' , 1);
$a->associate($log, $admin); //throws exception
$log2 = $rb->load('logentry' , 2);
$a->associate($log2, $admin);
pass();//no exception? still alive? proficiat.. pass!



testpack("Test Query Writer MySQL");
$adapter->exec("DROP TABLE IF EXISTS testtable");
asrt(in_array("testtable",$adapter->getCol("show tables")),false);
$writer->createTable("testtable");
asrt(in_array("testtable",$adapter->getCol("show tables")),true);
asrt(count(array_diff($writer->getTables(),$adapter->getCol("show tables"))),0);
asrt(count(array_keys($writer->getColumns("testtable"))),1);
asrt(in_array("id",array_keys($writer->getColumns("testtable"))),true);
asrt(in_array("c1",array_keys($writer->getColumns("testtable"))),false);
$writer->addColumn("testtable", "c1", 1);
asrt(count(array_keys($writer->getColumns("testtable"))),2);
asrt(in_array("c1",array_keys($writer->getColumns("testtable"))),true);
foreach($writer->sqltype_typeno as $key=>$type){asrt($writer->code($key),$type);}
asrt($writer->code("unknown"),99);
asrt($writer->scanType(false),0);
asrt($writer->scanType(NULL),0);
asrt($writer->scanType(2),1);
asrt($writer->scanType(255),1);
asrt($writer->scanType(256),2);
asrt($writer->scanType(-1),3);
asrt($writer->scanType(1.5),3);
asrt($writer->scanType("abc"),4);
asrt($writer->scanType(str_repeat("lorem ipsum",100)),5);
$writer->widenColumn("testtable", "c1", 2);
$cols=$writer->getColumns("testtable");asrt($writer->code($cols["c1"]),2);
$writer->widenColumn("testtable", "c1", 3);
$cols=$writer->getColumns("testtable");asrt($writer->code($cols["c1"]),3);
$writer->widenColumn("testtable", "c1", 4);
$cols=$writer->getColumns("testtable");asrt($writer->code($cols["c1"]),4);
$writer->widenColumn("testtable", "c1", 5);
$cols=$writer->getColumns("testtable");asrt($writer->code($cols["c1"]),5);
$id = $writer->insertRecord("testtable", array("c1"), array(array("lorem ipsum")));
$row = $writer->selectRecord("testtable", array($id));
asrt($row[0]["c1"],"lorem ipsum");
$writer->updateRecord("testtable", array(array("property"=>"c1","value"=>"ipsum lorem")), $id);
$row = $writer->selectRecord("testtable", array($id));
asrt($row[0]["c1"],"ipsum lorem");
$writer->deleteRecord("testtable", $id);
$row = $writer->selectRecord("testtable", array($id));
asrt($row,NULL);
//$pdo->setDebugMode(1);

$writer->addColumn("testtable", "c2", 2);
try{ $writer->addUniqueIndex("testtable", array("c1","c2")); fail(); //should fail, no content length blob
}catch(RedBean_Exception_SQL $e){ pass(); }
$writer->addColumn("testtable", "c3", 2);
try{ $writer->addUniqueIndex("testtable", array("c2","c3")); pass(); //should fail, no content length blob
}catch(RedBean_Exception_SQL $e){ fail(); }
$a = $adapter->get("show index from testtable");
asrt(count($a),3);
asrt($a[1]["Key_name"],"UQ_64b283449b9c396053fe1724b4c685a80fd1a54d");
asrt($a[2]["Key_name"],"UQ_64b283449b9c396053fe1724b4c685a80fd1a54d");

//Section D Security Tests
testpack("Test RedBean Security - bean interface ");
asrt(in_array("hack",$adapter->getCol("show tables")),true);
$bean = $redbean->load("page","13; drop table hack");
asrt(in_array("hack",$adapter->getCol("show tables")),true);
try{ $bean = $redbean->load("page where 1; drop table hack",1); }catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
$bean = $redbean->dispense("page");
$evil = "; drop table hack";
$bean->id = $evil;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
unset($bean->id);
$bean->name = "\"".$evil;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
$bean->name = "'".$evil;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
$bean->$evil = 1;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
unset($bean->$evil);
$bean->id = 1;
$bean->name = "\"".$evil;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
$bean->name = "'".$evil;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
$bean->$evil = 1;
try{$redbean->store($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);
try{$redbean->trash($bean);}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);



$adapter->exec("drop table if exists sometable");
testpack("Test RedBean Security - query writer");
try{$writer->createTable("sometable` ( `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT , PRIMARY KEY ( `id` ) ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; drop table hack; --");}catch(Exception $e){}
asrt(in_array("hack",$adapter->getCol("show tables")),true);

//print_r( $adapter->get("select id from page where id = 1; drop table hack") );
//asrt(in_array("hack",$adapter->getCol("show tables")),true);
//$bean = $redbean->load("page","13);show tables; ");
//exit;

testpack("Test ANSI92 issue in clearrelations");
$pdo->Execute("DROP TABLE IF EXISTS book");
$pdo->Execute("DROP TABLE IF EXISTS author");
$pdo->Execute("DROP TABLE IF EXISTS book_author");
$pdo->Execute("DROP TABLE IF EXISTS author_book");
$redbean = $toolbox->getRedBean();
$a = new RedBean_AssociationManager( $toolbox );
$book = $redbean->dispense("book");
$author1 = $redbean->dispense("author");
$author2 = $redbean->dispense("author");
$book->title = "My First Post";
$author1->name="Derek";
$author2->name="Whoever";
$a->set1toNAssoc($book,$author1);
$a->set1toNAssoc($book, $author2);
pass();
$pdo->Execute("DROP TABLE IF EXISTS book");
$pdo->Execute("DROP TABLE IF EXISTS author");
$pdo->Execute("DROP TABLE IF EXISTS book_author");
$pdo->Execute("DROP TABLE IF EXISTS author_book");
$redbean = $toolbox->getRedBean();
$a = new RedBean_AssociationManager( $toolbox );
$book = $redbean->dispense("book");
$author1 = $redbean->dispense("author");
$author2 = $redbean->dispense("author");
$book->title = "My First Post";
$author1->name="Derek";
$author2->name="Whoever";
$a->associate($book,$author1);
$a->associate($book, $author2);
pass();


$pdo->Execute("DROP TABLE IF EXISTS book");
$pdo->Execute("DROP TABLE IF EXISTS author");
$pdo->Execute("DROP TABLE IF EXISTS book_author");
$pdo->Execute("DROP TABLE IF EXISTS author_book");
$redbean = $toolbox->getRedBean();
$a = new RedBean_AssociationManager( $toolbox );
$book = $redbean->dispense("book");
$author1 = $redbean->dispense("author");
$author2 = $redbean->dispense("author");
$book->title = "My First Post";
$author1->name="Derek";
$author2->name="Whoever";
$a->unassociate($book,$author1);
$a->unassociate($book, $author2);
pass();
$redbean->trash($redbean->dispense("bla"));
pass();
$bean = $redbean->dispense("bla");
$bean->name = 1;
$bean->id = 2;
$redbean->trash($bean);
pass();
printtext("\nALL TESTS PASSED. REDBEAN SHOULD WORK FINE.\n");
