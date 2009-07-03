<!--wikidb should be replaced by the database name-->

CREATE TABLE `wikidb`.`model` (
`rev_id` INT( 10 ) NOT NULL ,
`session_id` VARCHAR( 50 ) NOT NULL ,
`blob_info` LONGBLOB NULL ,
`causal_barrier` BLOB NULL ,
PRIMARY KEY ( `rev_id` , `session_id` )
) ENGINE = InnoDB CHARACTER SET binary;

--CREATE TABLE `wikidb`.`patchs` (
--`id` INT( 10 ) NOT NULL ,
--`patch_id` VARCHAR( 50 ) NOT NULL ,
--`operations` LONGBLOB NOT NULL ,
--`is_active` INT( 8 ) NULL ,
--`rev_id` INT( 8 ) NOT NULL ,
--`page_id` INT( 10 ) NOT NULL ,
--PRIMARY KEY ( `id`, `page_id` )
--) ENGINE = InnoDB CHARACTER SET binary;
--
-- CREATE TABLE `wikidb`.`site` (
--`site_id` INT( 3 ) NOT NULL AUTO_INCREMENT,
--`site_url` VARCHAR( 70 ) NOT NULL ,
--`site_name` VARCHAR( 50 ) NOT NULL ,
--PRIMARY KEY ( `site_id` )
--) ENGINE = InnoDB CHARACTER SET binary;
--
-- CREATE TABLE `wikidb`.`site_cnt` (
--`site_id` INT( 3 ) NOT NULL ,
--`page_title` VARCHAR( 255 ) NOT NULL ,
--`counter` INT( 10 ) NULL ,
--PRIMARY KEY ( `site_id` , `page_title` )
--) ENGINE = InnoDB CHARACTER SET binary;

 CREATE TABLE `wikidb`.`p2p_clock` (
`value` BIGINT( 18 ) NOT NULL DEFAULT '0'
) ENGINE = InnoDB;