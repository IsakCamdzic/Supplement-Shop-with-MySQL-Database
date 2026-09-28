CREATE VIEW top10_proizvoda AS
SELECT 
    p.id_proizvoda,
    p.naziv,
    p.proizvodjac,
    k.naziv_kategorije,
    SUM(sn.kolicina) AS ukupno_prodano,
    SUM(sn.kolicina * sn.cijena) AS ukupna_prodaja
FROM stavke_narudzbe sn
JOIN proizvod p ON sn.id_proizvoda = p.id_proizvoda
JOIN kategorija k ON p.id_kategorije = k.id_kategorije
JOIN narudzba n ON sn.id_narudzbe = n.id_narudzbe
WHERE n.datum >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
AND n.status_narudzbe != 'Otkazano'
GROUP BY p.id_proizvoda, p.naziv, p.proizvodjac, k.naziv_kategorije
ORDER BY ukupna_prodaja DESC
LIMIT 10;

select * from top10_proizvoda;