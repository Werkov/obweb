ALTER TABLE `system_user`
CHANGE `password` `password` varchar(64) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' COMMENT '' AFTER `login`,
COMMENT='Spoj patra ISA hier. pro běž. uživ. a reg-ného -- reg jeNULL';

