SELECT id, email FROM `players` WHERE `email` NOT REGEXP "^[a-zA-Z0-9][a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]*?[a-zA-Z0-9._-]?@[a-zA-Z0-9][a-zA-Z0-9._-]*?[a-zA-Z0-9]?\\.[a-zA-Z]{2,63}$"



SELECT count(*) as cantidad, inscription_id FROM `assists` WHERE month = month GROUP by inscription_id HAVING cantidad > 1;s