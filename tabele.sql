CREATE DATABASE IF NOT EXISTS supplementTrgovina;
USE supplementTrgovina;

CREATE TABLE kupac(
    id_kupca INT AUTO_INCREMENT PRIMARY KEY,
    ime      VARCHAR(50)  NOT NULL,
    prezime  VARCHAR(50)  NOT NULL,
    email    VARCHAR(100) NOT NULL UNIQUE,
    adresa   VARCHAR(255) DEFAULT NULL
);

CREATE TABLE kategorija(
    id_kategorije    INT AUTO_INCREMENT PRIMARY KEY,
    naziv_kategorije VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE proizvod(
    id_proizvoda       INT AUTO_INCREMENT PRIMARY KEY,
    naziv              VARCHAR(255)  NOT NULL,
    cijena             DECIMAL(8,2)  NOT NULL CHECK(cijena > 0),
    opis               TEXT          NOT NULL,
    kolicina_na_stanju INT           NOT NULL CHECK(kolicina_na_stanju >= 0),
    proizvodjac        VARCHAR(50)   NOT NULL,
    id_kategorije      INT           NOT NULL,
    FOREIGN KEY (id_kategorije) REFERENCES kategorija(id_kategorije) 
        ON DELETE RESTRICT
);

CREATE TABLE zaposlenik(
    id_zaposlenika INT AUTO_INCREMENT PRIMARY KEY,
    ime            VARCHAR(50) NOT NULL,
    prezime        VARCHAR(50) NOT NULL,
    uloga          ENUM('Admin', 'Skladistar', 'Dostavljac') NOT NULL
);

CREATE TABLE narudzba(
    id_narudzbe     INT AUTO_INCREMENT PRIMARY KEY,
    id_kupca        INT,
    adresa_dostave  VARCHAR(255) DEFAULT NULL,
    datum           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_narudzbe ENUM('Na cekanju','Potvrdjeno','Isporuceno','Otkazano','Odbijeno') 
                    NOT NULL DEFAULT 'Na cekanju',
    id_zaposlenika  INT,
    FOREIGN KEY (id_kupca)       REFERENCES kupac(id_kupca) 
        ON DELETE RESTRICT,
    FOREIGN KEY (id_zaposlenika) REFERENCES zaposlenik(id_zaposlenika) 
        ON DELETE SET NULL
);

CREATE TABLE stavke_narudzbe(
    id_narudzbe  INT,
    id_proizvoda INT,
    kolicina     INT         NOT NULL CHECK(kolicina > 0),
    cijena       DECIMAL(8,2) NOT NULL,
    PRIMARY KEY(id_narudzbe, id_proizvoda),
    FOREIGN KEY (id_narudzbe)  REFERENCES narudzba(id_narudzbe)  
        ON DELETE CASCADE,
    FOREIGN KEY (id_proizvoda) REFERENCES proizvod(id_proizvoda) 
        ON DELETE RESTRICT
);

CREATE TABLE korpa(
    id_korpe INT AUTO_INCREMENT PRIMARY KEY,
    id_kupca INT,
    datum    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kupca) REFERENCES kupac(id_kupca) 
        ON DELETE CASCADE
);

CREATE TABLE stavke_korpe(
    id_korpe     INT,
    id_proizvoda INT,
    kolicina     INT          NOT NULL DEFAULT 1 CHECK(kolicina > 0),
    cijena       DECIMAL(8,2) NOT NULL,
    PRIMARY KEY(id_korpe, id_proizvoda),
    FOREIGN KEY (id_korpe)     REFERENCES korpa(id_korpe)       
        ON DELETE CASCADE,
    FOREIGN KEY (id_proizvoda) REFERENCES proizvod(id_proizvoda) 
        ON DELETE RESTRICT
);

CREATE TABLE racun(
    id_racuna       INT AUTO_INCREMENT PRIMARY KEY,
    id_narudzbe     INT          UNIQUE NOT NULL,
    datum_izdavanja TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    status_placanja ENUM('Potvrdjeno','Odbijeno','Na cekanju') 
                    NOT NULL DEFAULT 'Na cekanju',
    ukupan_iznos    DECIMAL(8,2) NOT NULL CHECK(ukupan_iznos > 0),
    FOREIGN KEY (id_narudzbe) REFERENCES narudzba(id_narudzbe) 
        ON DELETE CASCADE
);