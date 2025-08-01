ALTER TABLE `users` ADD `role` VARCHAR(255) NOT NULL DEFAULT 'user' AFTER `password`;
UPDATE `users` SET `role` = 'admin' WHERE `id` = 1;
