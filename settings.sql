
        ALTER TABLE `settings`
        
                MODIFY COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                MODIFY COLUMN `type` varchar(256)  NOT NULL ,
                MODIFY COLUMN `copyright` varchar(256)  NOT NULL ,
                ADD COLUMN `analytics` varchar(512)  NOT NULL ;
        