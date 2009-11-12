<?php

/*

                   .______.
_______   ____   __| _/\_ |__   ____ _____    ____
\_  __ \_/ __ \ / __ |  | __ \_/ __ \\__  \  /    \
 |  | \/\  ___// /_/ |  | \_\ \  ___/ / __ \|   |  \
 |__|    \___  >____ |  |___  /\___  >____  /___|  /
             \/     \/      \/     \/     \/     \/

 Written by Gabor de Mooij Copyright (c) 2009

 */

/**
 * RedBean Loader
 * @package 		RedBean/redbean.inc.php
 * @description		If you do not use a pre-packaged version of RedBean
 *					you can use this RedBean loader to include all RedBean
 *					files for you.
 * @author			Gabor de Mooij
 * @license			BSD
 */


//Load Core Inteface
require("ObjectDatabase.php");

//Load Database drivers
require("Driver.php");
require("Driver/PDO.php");

//Load Infrastructure
require("OODBBean.php");
require("Observable.php");
require("Observer.php");

//Load Database Adapters
require("Adapter.php");
require("Adapter/DBAdapter.php");

//Load SQL drivers
require("QueryWriter.php");
require("QueryWriter/MySQL.php");

//Load required Exceptions
require("Exception.php");
require("Exception/SQL.php");
require("Exception/Security.php");
require("Exception/FailedAccessBean.php");

//Load Core functionality
require("OODB.php");
require("ToolBox.php");

//Load extended functionality
require("AssociationManager.php");
require("TreeManager.php");
require("Setup.php");

//Load the default plugins
require("Plugin/ChangeLogger.php");
require("Plugin/Cache.php");
