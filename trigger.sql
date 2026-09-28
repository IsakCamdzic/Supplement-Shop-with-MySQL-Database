DELIMITER //
CREATE TRIGGER trg_stavke_narudzbe
AFTER INSERT ON stavke_narudzbe
FOR EACH ROW
BEGIN
    DECLARE trenutna_kolicina INT;
    
    -- 1. dio: Provjera i ažuriranje zaliha
    SELECT kolicina_na_stanju INTO trenutna_kolicina
    FROM proizvod
    WHERE id_proizvoda = NEW.id_proizvoda;
    
    IF trenutna_kolicina < NEW.kolicina THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nema dovoljno proizvoda na stanju!';
    ELSE
        UPDATE proizvod
        SET kolicina_na_stanju = kolicina_na_stanju - NEW.kolicina
        WHERE id_proizvoda = NEW.id_proizvoda;
    END IF;
    
    -- 2. dio: Kreiranje/ažuriranje računa
    IF EXISTS (SELECT 1 FROM racun WHERE id_narudzbe = NEW.id_narudzbe) THEN
        UPDATE racun
        SET ukupan_iznos = (
            SELECT SUM(kolicina * cijena)
            FROM stavke_narudzbe
            WHERE id_narudzbe = NEW.id_narudzbe
        )
        WHERE id_narudzbe = NEW.id_narudzbe;
    ELSE
        INSERT INTO racun (id_narudzbe, status_placanja, ukupan_iznos)
        VALUES (NEW.id_narudzbe, 'Na cekanju', NEW.kolicina * NEW.cijena);
    END IF;
END //
DELIMITER ;