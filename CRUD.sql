-- SELECT svih proizvoda sa nazivom kategorije
SELECT 
    p.id_proizvoda,
    p.naziv,
    p.cijena,
    p.kolicina_na_stanju,
    k.naziv_kategorije
FROM proizvod p
JOIN kategorija k ON p.id_kategorije = k.id_kategorije;

-- Pregled svih narudžbi sa imenom kupca
-- COALENCE sluzi za vracanja prve vrijednosti s lijeva na desno koja NIJE NULL
-- U ovom slucaju ako je adresa_dostave = NULL onda uzima adresu iz relacije kupac kao adresu_isporuke
SELECT 
    n.id_narudzbe,
    k.ime,
    k.prezime,
    COALESCE(n.adresa_dostave, k.adresa) AS adresa_isporuke,
    n.datum,
    n.status_narudzbe
FROM narudzba n
JOIN kupac k ON n.id_kupca = k.id_kupca;

-- Pregled stavki određene narudžbe sa nazivom proizvoda
SELECT 
    sn.id_narudzbe,
    p.naziv,
    sn.kolicina,
    sn.cijena,
    (sn.kolicina * sn.cijena) AS ukupno
FROM stavke_narudzbe sn
JOIN proizvod p ON sn.id_proizvoda = p.id_proizvoda
WHERE sn.id_narudzbe = 1;

-- Pregled svih računa sa statusom narudžbe i imenom kupca
SELECT 
    r.id_racuna,
    k.ime,
    k.prezime,
    r.datum_izdavanja,
    r.ukupan_iznos,
    r.status_placanja,
    n.status_narudzbe
FROM racun r
JOIN narudzba n ON r.id_narudzbe = n.id_narudzbe
JOIN kupac k ON n.id_kupca = k.id_kupca;

-- Pregled proizvoda koji imaju malo zaliha (manje od 5)
SELECT 
    naziv,
    kolicina_na_stanju,
    proizvodjac
FROM proizvod
WHERE kolicina_na_stanju < 5
ORDER BY kolicina_na_stanju ASC;

-- Pregled korpe određenog kupca sa ukupnim iznosom
SELECT 
    k.ime,
    k.prezime,
    p.naziv,
    sk.kolicina,
    sk.cijena,
    (sk.kolicina * sk.cijena) AS ukupno
FROM stavke_korpe sk
JOIN korpa ko ON sk.id_korpe = ko.id_korpe
JOIN kupac k ON ko.id_kupca = k.id_kupca
JOIN proizvod p ON sk.id_proizvoda = p.id_proizvoda
WHERE ko.id_kupca = 1;

-- Ukupna prodaja po kategoriji
SELECT 
    kat.naziv_kategorije,
    COUNT(sn.id_proizvoda) AS broj_prodanih,
    SUM(sn.kolicina * sn.cijena) AS ukupna_prodaja
FROM stavke_narudzbe sn
JOIN proizvod p ON sn.id_proizvoda = p.id_proizvoda
JOIN kategorija kat ON p.id_kategorije = kat.id_kategorije
GROUP BY kat.naziv_kategorije
ORDER BY ukupna_prodaja DESC;


-- INSERT 
-- Registracija novog kupca
INSERT INTO kupac (ime, prezime, email, adresa)
VALUES ('Mirza', 'Mirzic', 'mirza@gmail.com', 'Titova 5, 71000 Sarajevo');

-- Dodavanje novog proizvoda
INSERT INTO proizvod (naziv, cijena, opis, kolicina_na_stanju, id_kategorije, proizvodjac)
VALUES ('Optimum Whey 1kg', 75.00, 'Kvalitetan whey protein', 20, 1, 'Optimum Nutrition');

-- Kreiranje korpe za kupca
INSERT INTO korpa (id_kupca)
VALUES (1);

-- Dodavanje proizvoda u korpu
INSERT INTO stavke_korpe (id_korpe, id_proizvoda, kolicina, cijena)
VALUES (1, 23, 1, 75.00);

-- Kreiranje narudžbe
INSERT INTO narudzba (id_kupca, adresa_dostave, status_narudzbe)
VALUES (1, NULL, 'Na cekanju');

-- Dodavanje stavki narudžbe
INSERT INTO stavke_narudzbe (id_narudzbe, id_proizvoda, kolicina, cijena)
VALUES (11, 23, 1, 75.00),
       (11, 10, 2, 35.00);
       
-- UPDATE
-- Izmjena statusa narudžbe
UPDATE narudzba
SET status_narudzbe = 'Isporuceno'
WHERE id_narudzbe = 1;

-- Izmjena statusa plaćanja računa
UPDATE racun
SET status_placanja = 'Potvrdjeno'
WHERE id_narudzbe = 4;

-- Izmjena cijene proizvoda
UPDATE proizvod
SET cijena = 55.00
WHERE id_proizvoda = 7;

-- Izmjena količine proizvoda na stanju
UPDATE proizvod
SET kolicina_na_stanju = kolicina_na_stanju + 50
WHERE id_proizvoda = 6;

-- Izmjena adrese kupca
UPDATE kupac
SET adresa = 'Nova ulica 10, 75000 Tuzla'
WHERE id_kupca = 9;

-- Izmjena količine proizvoda u korpi
UPDATE stavke_korpe
SET kolicina = 3
WHERE id_korpe = 1 AND id_proizvoda = 6;

-- Dodjela zaposlenika narudžbi
UPDATE narudzba
SET id_zaposlenika = 3
WHERE id_narudzbe = 6;


-- DELETE
-- Brisanje proizvoda iz korpe
DELETE FROM stavke_korpe
WHERE id_korpe = 1 AND id_proizvoda = 6;

-- Brisanje cijele korpe (CASCADE briše i stavke)
DELETE FROM korpa
WHERE id_korpe = 1;

-- Otkazivanje narudžbe (samo status, ne brišemo)
UPDATE narudzba
SET status_narudzbe = 'Otkazano'
WHERE id_narudzbe = 6;

-- Brisanje kupca koji nema narudžbi
DELETE FROM kupac
WHERE id_kupca = 11;

-- Brisanje proizvoda koji nije nikad naručen
DELETE FROM proizvod
WHERE id_proizvoda = 22
AND id_proizvoda NOT IN (
    SELECT id_proizvoda FROM stavke_narudzbe
);