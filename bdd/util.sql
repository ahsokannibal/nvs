UPDATE perso INNER JOIN (SELECT id_perso, SUM(poids_objet) as charge_total FROM objet, perso_as_objet
						WHERE perso_as_objet.id_objet = objet.id_objet
						GROUP BY id_perso ORDER BY id_perso) tb1
                        ON tb1.id_perso = perso.id_perso
SET perso.charge_perso = tb1.charge_total 