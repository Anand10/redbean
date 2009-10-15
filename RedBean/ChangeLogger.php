<?php
/**
 * RedBean ChangeLogger
 * Shields you from race conditions automatically.
 * @package 		RedBean/ChangeLogger.php
 * @description		Shields you from race conditions automatically.
 * @author			Gabor de Mooij
 * @license			BSD
 */
class RedBean_ChangeLogger implements RedBean_Observer {

    /**
     * @var RedBean_DBAdapter
     */
    private $writer;

	/**
	 * Constructor, requires a writer
	 * @param RedBean_QueryWriter $writer
	 */
    public function __construct(RedBean_QueryWriter $writer) {
        $this->writer = $writer;
        $this->writer->cleanUpLog();
    }

	/**
	 * Throws an exception if information in the bean has been changed
	 * by another process or bean.
	 * @param string $event
	 * @param RedBean_OODBBean $item
	 */
    public function onEvent( $event, $item ) {
        $id = $item->id;
        if (! ((int) $id)) return;
        $type = $item->__info["type"];
        if ($event=="open") {
            $insertid = $this->writer->insertRecord("__log",array("action","tbl","itemid"),
            array(1,  $type, $id));
            $item->__info["opened"] = $insertid;
        }
        if ($event=="update") {
            $oldid = $item->__info["opened"];
            $r = $this->writer->getLoggedChanges($type,$id, $oldid);
            if ($r) { throw new RedBean_Exception_FailedAccessBean("Locked, failed to access (type:$type, id:$id)"); }
            $newid = $this->writer->insertRecord("__log",array("action","tbl","itemid"),
            array(2,  $type, $id));
            $item->__info["opened"] = $newid;
        }
    }
}