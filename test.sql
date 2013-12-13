
        CREATE TABLE IF NOT EXISTS `categories` (
          
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`),
                
            `name` varchar(256)  NOT NULL 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        
        ALTER TABLE `categories`
        
                MODIFY COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                MODIFY COLUMN `name` varchar(256)  NOT NULL ;
        
        CREATE TABLE IF NOT EXISTS `products` (
          
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`),
                
            `name` varchar(256)  NOT NULL ,
            `description` varchar(512)  NOT NULL ,
            `price` float  NOT NULL ,
            `priceWas` float  NOT NULL ,
            `image` blob  NOT NULL ,
            `date` datetime  NOT NULL 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        
        ALTER TABLE `products`
        
                ADD COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                ADD COLUMN `name` varchar(256)  NOT NULL ,
                ADD COLUMN `description` varchar(512)  NOT NULL ,
                ADD COLUMN `price` float  NOT NULL ,
                ADD COLUMN `priceWas` float  NOT NULL ,
                ADD COLUMN `image` blob  NOT NULL ,
                ADD COLUMN `date` datetime  NOT NULL ;
        