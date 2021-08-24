<?php

/*
 * Copyright 2021 WPPConnect Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace WPPConnect;

use WPPConnect\Db\Adapter;
use WPPConnect\Helpers\Util;

require '../../vendor/autoload.php';

$config = require_once('../config/config.php');

#SQLite
#$adapter = new Adapter($config, 'sqlite');

#Postgres
#$adapter = new Adapter($config, 'postgres');

#MySQL - Default
$adapter = new Adapter($config);
$util = new Util() ;

#Connect
$adapter->connect();

#Disconnect
$adapter->disconnect();

#Create Table
$adapter->createTable('mensagem', [
    'id' => 'serial PRIMARY KEY', // use INTEGER instead of SERIAL on sqlite to get auto ids
    'session' => 'varchar(255)',
    'content' => 'varchar(255)'
]);

#Insert
$adapter->insert('mensagem', ['session' => 'MySessionName', 'content' => 'MyContent']);

#Update
$adapter->update('mensagem', ['content' => 'MyContent_Edit'], ['id' => 1]);

#Update
$adapter->delete('mensagem', ['id' => 1]);

#Truncate Table
$adapter->truncateTable('mensagem');

#Drop Table
$adapter->dropTable('mensagem');

#Get All Tables
$adapter->getAllTables();

#Fetch
$adapter->fetchAll('mensagem', ['id', 'content'], ['id' => 1], [], [], ['id'], 'ASC', 3);
$adapter->fetchAll('mensagem');
$adapter->fetchRow('mensagem', [], [], [], [], ['id'], 'ASC');

#Exec / Query
$adapter->exec('SELECT * FROM mensagem WHERE id = 1');
$adapter->query('SELECT * FROM mensagem WHERE id = ?', 1);

#Others
$adapter->count('mensagem');
$adapter->getColumns('mensagem');
$adapter->hasTable('mensagem');
$adapter->hasColumn('mensagem', 'id');
$adapter->getColumnDatatype('mensagem', 'id');
$adapter->getPrimaryKey('mensagem');

#Foreign Keys
$adapter->getForeignKeys('session');
$adapter->getForeignTablesOut('session');
$adapter->getForeignTablesIn('mensagem');
$adapter->isForeignKey('session', 'id');

#Debug Query
$adapter->debugQuery("SELECT * FROM mensagem WHERE id = ? or id = ?", [1, 2]);
