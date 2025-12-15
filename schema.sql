-- appdb.analytics definition

CREATE TABLE `analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `amount` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.config definition

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `appearance` tinyint(4) DEFAULT NULL,
  `movingbg` tinyint(1) DEFAULT NULL,
  `sidebarid` int(11) DEFAULT NULL,
  `sidebars` tinyint(1) DEFAULT NULL,
  `font` int(11) DEFAULT NULL,
  `freakmode` tinyint(1) DEFAULT 0,
  `mirrorsidebars` tinyint(1) DEFAULT 1,
  `indeximage` int(11) DEFAULT 0,
  `emojidex` tinyint(1) DEFAULT 1,
  `light_mode` tinyint(1) DEFAULT 0,
  `ads` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.economy definition

CREATE TABLE `economy` (
  `id` int(11) NOT NULL,
  `money` int(11) DEFAULT 0,
  `inv` text DEFAULT NULL,
  `lastbuxclaim` int(11) DEFAULT 1577836800,
  `pocket_money` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.feed definition

CREATE TABLE `feed` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `content` text DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `uploadtimestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.group_messages definition

CREATE TABLE `group_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(100) DEFAULT NULL,
  `group` int(11) DEFAULT NULL,
  `msgid` int(11) DEFAULT 1,
  `author` int(11) DEFAULT NULL,
  `creationtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.groups definition

CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `public` tinyint(1) DEFAULT 1,
  `owner` int(11) DEFAULT NULL,
  `desc` varchar(1000) DEFAULT NULL,
  `icon` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.interaction definition

CREATE TABLE `interaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_who` int(11) DEFAULT NULL,
  `to_what` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.items definition

CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `asset` text DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `value` int(10) unsigned DEFAULT NULL,
  `public` tinyint(1) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `type` tinyint(2) DEFAULT NULL,
  `uploadts` int(11) DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `approver` int(11) DEFAULT NULL,
  `hat_texture` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.onetimelinks definition

CREATE TABLE `onetimelinks` (
  `creationdate` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `optionalvalue` varchar(100) DEFAULT NULL,
  `link` varchar(100) NOT NULL,
  PRIMARY KEY (`link`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.profiles definition

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `equipped` varchar(256) DEFAULT NULL,
  `colors` text DEFAULT '{"head":1009,"trso":23,"lleg":301,"rleg":301,"larm":1009,"rarm":1009}',
  `country` varchar(4) DEFAULT 'NONE',
  `desc` text DEFAULT "I'm new to LSDBLOX!",
  `showposts` tinyint(1) DEFAULT 1,
  `showinventory` tinyint(1) DEFAULT 1,
  `showlastseen` tinyint(1) DEFAULT 1,
  `showcountry` tinyint(1) DEFAULT 0,
  `showfollowing` tinyint(1) DEFAULT 1,
  `showfollowers` tinyint(1) DEFAULT 1,
  `showmutuals` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.reports definition

CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assettype` int(11) DEFAULT NULL,
  `assetid` int(11) DEFAULT NULL,
  `information` text DEFAULT NULL,
  `resolved` int(11) DEFAULT 0,
  `resolvedby` int(11) DEFAULT NULL,
  `submitter` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.social definition

CREATE TABLE `social` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `follower` int(11) DEFAULT NULL,
  `following` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.transactions definition

CREATE TABLE `transactions` (
  `id` varchar(128) NOT NULL,
  `issuer` int(11) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `asset` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- appdb.users definition

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) DEFAULT NULL,
  `pass` varchar(100) DEFAULT NULL,
  `discordid` varchar(100) DEFAULT NULL,
  `invkey` varchar(100) DEFAULT NULL,
  `registerts` int(11) DEFAULT NULL,
  `authuuid` text DEFAULT NULL,
  `isoperator` tinyint(1) DEFAULT 0,
  `ppack` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `pass` (`pass`),
  UNIQUE KEY `discordtag` (`discordid`),
  UNIQUE KEY `invkey` (`invkey`),
  UNIQUE KEY `registerts` (`registerts`),
  UNIQUE KEY `authuuid` (`authuuid`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;