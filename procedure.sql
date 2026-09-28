DELIMITER //
CREATE PROCEDURE promijeni_status_narudzbi(
    IN novi_status VARCHAR(20),
    IN datum_od DATE,
    IN datum_do DATE
)
BEGIN
    -- Provjeri da li je status validan
    IF novi_status NOT IN ('Na cekanju', 'Potvrdjeno', 'Isporuceno', 'Otkazano', 'Odbijeno') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nevalidan status narudžbe!';
    ELSE
        UPDATE narudzba n
        JOIN racun r ON n.id_narudzbe = r.id_narudzbe
        SET n.status_narudzbe = novi_status
        WHERE DATE(r.datum_izdavanja) BETWEEN datum_od AND datum_do;
    END IF;
END //
DELIMITER ;

SET SQL_SAFE_UPDATES = 0;
CALL promijeni_status_narudzbi('Isporuceno', curdate()- INTERVAL 30 DAY, curdate());
SET SQL_SAFE_UPDATES = 1;
