
        ALTER TABLE `products`
        
                MODIFY COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                MODIFY COLUMN `name` varchar(256)  NOT NULL ,
                MODIFY COLUMN `description` varchar(512)  NOT NULL ,
                MODIFY COLUMN `price` float  NOT NULL ,
                MODIFY COLUMN `priceWas` float  NOT NULL ,
                MODIFY COLUMN `image` blob  NOT NULL ,
                MODIFY COLUMN `date` datetime  NOT NULL ;
        