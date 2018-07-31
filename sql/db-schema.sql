-- Adminer 4.6.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

DROP DATABASE IF EXISTS `nodedb_mirror`;
CREATE DATABASE `nodedb_mirror` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `nodedb_mirror`;

DROP TABLE IF EXISTS `statuses`;
CREATE TABLE `statuses` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `subnets`;
CREATE TABLE `subnets` (
  `id` int(10) unsigned NOT NULL,
  `addr` int(10) unsigned NOT NULL,
  `mask` int(10) unsigned NOT NULL,
  `type` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `suburbs`;
CREATE TABLE `suburbs` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `state` varchar(64) NOT NULL,
  `postcode` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `nodes`;
CREATE TABLE `nodes` (
  `id` int(10) unsigned NOT NULL,
  `suburb_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `region` varchar(64) NOT NULL,
  `zone` varchar(64) NOT NULL,
  `lat` float NULL,
  `lng` float NULL,
  `elevation` float NULL,
  `antHeight` float NULL,
  `asNum` int(10) unsigned NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `suburb_id` (`suburb_id`),
  KEY `user_id` (`user_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `nodes_ibfk_1` FOREIGN KEY (`suburb_id`) REFERENCES `suburbs` (`id`),
  CONSTRAINT `nodes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `nodes_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hosts`;
CREATE TABLE `hosts` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `subnet_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `fqdn` varchar(255) NOT NULL,
  `addr` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `node_id` (`node_id`),
  KEY `subnet_id` (`subnet_id`),
  CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`),
  CONSTRAINT `hosts_ibfk_2` FOREIGN KEY (`subnet_id`) REFERENCES `subnets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `node_subnet`;
CREATE TABLE `node_subnet` (
  `node_id` int(10) unsigned NOT NULL,
  `subnet_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `node_id_subnet_id` (`node_id`,`subnet_id`),
  KEY `subnet_id` (`subnet_id`),
  KEY `node_id` (`node_id`),
  CONSTRAINT `node_subnet_ibfk_1` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`),
  CONSTRAINT `node_subnet_ibfk_2` FOREIGN KEY (`subnet_id`) REFERENCES `subnets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 2018-07-31 03:50:02
