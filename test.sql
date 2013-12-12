
        CREATE TABLE IF NOT EXISTS `products` (
          
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`),
                
            `name` varchar(256)  NOT NULL ,
            `description` varchar(1024)  NOT NULL ,
            `image` varchar(1024)  NOT NULL 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        
        ALTER TABLE `products`
        
                ADD COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                ADD COLUMN `name` varchar(256)  NOT NULL ,
                ADD COLUMN `description` varchar(1024)  NOT NULL ,
                ADD COLUMN `image` varchar(1024)  NOT NULL 
        