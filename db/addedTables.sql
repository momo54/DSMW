
DROP TABLE IF EXISTS `wikidb`.`model`;
DROP TABLE IF EXISTS `wikidb`.`p2p_params`;

CREATE TABLE `wikidb`.`model` (
`rev_id` INT( 10 ) NOT NULL ,
`session_id` VARCHAR( 50 ) NOT NULL ,
`blob_info` LONGBLOB NULL ,
`causal_barrier` BLOB NULL ,
PRIMARY KEY ( `rev_id` , `session_id` )
) ENGINE = InnoDB CHARACTER SET binary;

 CREATE TABLE `wikidb`.`p2p_params` (
`value` BIGINT( 18 ) NOT NULL DEFAULT '0',
 `server_id` VARCHAR( 40 ) NOT NULL DEFAULT '0'
) ENGINE = InnoDB  DEFAULT CHARSET = latin1;

INSERT INTO `wikidb`.`p2p_params` (`value`, `server_id`) VALUES ('0', '0');