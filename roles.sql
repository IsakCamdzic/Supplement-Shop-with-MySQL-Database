USE supplementTrgovina;

CREATE ROLE 'admin';
CREATE ROLE 'skladistar';
CREATE ROLE 'dostavljac';
CREATE ROLE 'kupac';


GRANT ALL PRIVILEGES ON supplementTrgovina.* TO 'admin';


GRANT SELECT, UPDATE ON supplementTrgovina.proizvod TO 'skladistar';
GRANT SELECT ON supplementTrgovina.kategorija TO 'skladistar';
GRANT SELECT ON supplementTrgovina.narudzba TO 'skladistar';
GRANT SELECT ON supplementTrgovina.stavke_narudzbe TO 'skladistar';

-- =====================
-- DOSTAVLJAC
-- =====================
GRANT SELECT ON supplementTrgovina.proizvod TO 'dostavljac';
GRANT SELECT ON supplementTrgovina.kategorija TO 'dostavljac';
GRANT SELECT, UPDATE ON supplementTrgovina.narudzba TO 'dostavljac';
GRANT SELECT ON supplementTrgovina.stavke_narudzbe TO 'dostavljac';

-- =====================
-- KUPAC
-- =====================
GRANT SELECT ON supplementTrgovina.proizvod TO 'kupac';
GRANT SELECT ON supplementTrgovina.kategorija TO 'kupac';
GRANT INSERT, SELECT ON supplementTrgovina.narudzba TO 'kupac';
GRANT INSERT, SELECT ON supplementTrgovina.stavke_narudzbe TO 'kupac';
GRANT INSERT, SELECT, UPDATE, DELETE ON supplementTrgovina.korpa TO 'kupac';
GRANT INSERT, SELECT, UPDATE, DELETE ON supplementTrgovina.stavke_korpe TO 'kupac';
GRANT SELECT ON supplementTrgovina.racun TO 'kupac';

-- =====================
-- Kreiranje korisnika i dodjela rola
-- =====================
CREATE USER 'admin_user'@'localhost' IDENTIFIED BY 'Admin123!';
CREATE USER 'skladistar_user'@'localhost' IDENTIFIED BY 'Skladistar123!';
CREATE USER 'dostavljac_user'@'localhost' IDENTIFIED BY 'Dostavljac123!';
CREATE USER 'kupac_user'@'localhost' IDENTIFIED BY 'Kupac123!';

GRANT 'admin' TO 'admin_user'@'localhost';
GRANT 'skladistar' TO 'skladistar_user'@'localhost';
GRANT 'dostavljac' TO 'dostavljac_user'@'localhost';
GRANT 'kupac' TO 'kupac_user'@'localhost';

-- Aktivacija rola za korisnike
SET DEFAULT ROLE 'admin' TO 'admin_user'@'localhost';
SET DEFAULT ROLE 'skladistar' TO 'skladistar_user'@'localhost';
SET DEFAULT ROLE 'dostavljac' TO 'dostavljac_user'@'localhost';
SET DEFAULT ROLE 'kupac' TO 'kupac_user'@'localhost';

FLUSH PRIVILEGES;

