CREATE TABLE `sms_list_tz` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `TEXT` text NOT NULL,
  `IS_SENDED` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;