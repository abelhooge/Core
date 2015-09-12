SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `hi_config` (
  `id` int(11) NOT NULL,
  `file` varchar(32) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hi_log` (
  `id` int(11) NOT NULL,
  `type` varchar(8) NOT NULL,
  `message` varchar(255) NOT NULL,
  `logFile` varchar(255) NOT NULL,
  `logLine` int(11) NOT NULL,
  `context` text NOT NULL,
  `runtime` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hi_user_data` (
  `data_id` int(11) NOT NULL,
  `data_user_id` int(11) NOT NULL,
  `data_key` varchar(255) NOT NULL,
  `data_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hi_user_emails` (
  `email_id` int(11) NOT NULL,
  `email_user_id` int(11) NOT NULL,
  `email_text` varchar(64) NOT NULL,
  `email_primary` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hi_user_permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_tag_id` int(11) NOT NULL,
  `permission_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hi_user_sessions` (
  `session_id` int(11) NOT NULL,
  `session_hash` varchar(255) NOT NULL,
  `session_user_id` int(11) NOT NULL,
  `session_info` text NOT NULL,
  `session_ip` varchar(64) NOT NULL,
  `session_start` datetime NOT NULL,
  `session_end` datetime NOT NULL,
  `session_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hi_user_tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(64) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

INSERT INTO `hi_user_tags` (`tag_id`, `tag_name`) VALUES
(1, 'ACTIVE'),
(2, 'BLOCKED'),
(3, 'ADMIN');

CREATE TABLE IF NOT EXISTS `hi_user_users` (
  `user_id` int(11) NOT NULL,
  `user_username` varchar(32) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL COMMENT 'Primary Email of the user',
  `user_verify_code` varchar(16) NOT NULL COMMENT 'Verification code used in email to verify user email'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `hi_config`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `hi_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `hi_user_data`
  ADD PRIMARY KEY (`data_id`);

ALTER TABLE `hi_user_emails`
  ADD PRIMARY KEY (`email_id`);

ALTER TABLE `hi_user_permissions`
  ADD PRIMARY KEY (`permission_id`);

ALTER TABLE `hi_user_sessions`
  ADD PRIMARY KEY (`session_id`);

ALTER TABLE `hi_user_tags`
  ADD PRIMARY KEY (`tag_id`);

ALTER TABLE `hi_user_users`
  ADD PRIMARY KEY (`user_id`);


ALTER TABLE `hi_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hi_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hi_user_data`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hi_user_emails`
  MODIFY `email_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hi_user_permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hi_user_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hi_user_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
ALTER TABLE `hi_user_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;