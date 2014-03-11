<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table if not exists LowestPrice_history(id int not null auto_increment primary key, sku varchar(50), cur_timestamp TIMESTAMP(8));
		
SQLTEXT;

$installer->run($sql);
//add attribute and apply to correct set
$installer->endSetup();
	 