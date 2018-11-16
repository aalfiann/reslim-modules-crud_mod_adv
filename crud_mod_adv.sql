SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crud_mod
-- ----------------------------
DROP TABLE IF EXISTS `crud_mod_adv`;
CREATE TABLE `crud_mod_adv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Fullname` varchar(50) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Telp` varchar(15) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Website` varchar(50) DEFAULT NULL,
  `Custom_id` varchar(255) DEFAULT NULL,
  `Custom_field` text,
  `Created_at` datetime NOT NULL,
  `Created_by` varchar(50) NOT NULL,
  `Updated_at` datetime DEFAULT NULL,
  `Updated_by` varchar(50) DEFAULT NULL,
  `Updated_sys` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `Created_at` (`Created_at`),
  KEY `Created_by` (`Created_by`),
  KEY `Custom_id` (`Custom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET FOREIGN_KEY_CHECKS=1;