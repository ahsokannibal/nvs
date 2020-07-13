ALTER TABLE `phpbb_users` ADD `id_perso` INT NULL AFTER `user_reminded_time`;

UPDATE `phpbb_users` INNER JOIN (SELECT id_perso, nom_perso FROM perso) tb1
ON tb1.nom_perso = phpbb_users.username
SET phpbb_users.id_perso = tb1.id_perso