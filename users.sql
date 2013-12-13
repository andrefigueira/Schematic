
        ALTER TABLE `users`
        
                MODIFY COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                MODIFY COLUMN `name` varchar(256)  NOT NULL ,
                MODIFY COLUMN `username` varchar(256)  NOT NULL ,
                MODIFY COLUMN `email` varchar(256)  NOT NULL ,
                MODIFY COLUMN `date` varchar(256)  NOT NULL ,
                MODIFY COLUMN `profile` varchar(256)  NOT NULL ,
                ADD COLUMN `password` varchar(1024)  NOT NULL ;
        