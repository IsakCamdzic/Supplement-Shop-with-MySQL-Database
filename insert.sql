INSERT INTO kategorija VALUES (4,'Aminokiseline'),
(2,'Kreatin'),
(7,'Mass gaineri'),
(3,'Pre-workout'),
(1,'Protein'),
(5,'Ugljikohidrati'),
(6,'Vitamini i minerali');

INSERT INTO kupac VALUES (1,'Isak','Camdzic','isak.camdzic@gmail.com','Sabana Zahirovica 7, 75000 Tuzla'),
(2,'Marko','Markovic','marko.markovic@gmail.com','Kralja Petra I Karađorđevića 45, 78000 Banja Luka'),
(3,'Ana','Ivic','ana.ivic@gmail.com','Ulica kneza Branimira 8, 88000 Mostar'),
(4,'Lejla','Kovacevic','lejla.kovacevic@gmail.com','Mehmedalije Maka Dizdara 22, 75000 Tuzla'),
(5,'Ivan','Horvat','ivan.horvat@gmail.com','Ante Starčevića 15, 88000 Mostar'),
(6,'Emir','Suljic','emir.suljic@gmail.com','Alije Izetbegovića 33, 72000 Zenica'),
(7,'Jasmina','Begovic','jasmina.begovic@gmail.com','Maršala Tita 101, 71000 Sarajevo'),
(8,'Adnan','Basic','adnan.basic@gmail.com','Bosanska 5, 77000 Bihać'),
(9,'Maja','Peric','maja.peric@gmail.com','Nova ulica 10, 75000 Tuzla'),
(10,'Nikola','Jovanovic','nikola.jovanovic@gmail.com','Vidovdanska 27, 78000 Banja Luka');



INSERT INTO proizvod (naziv,cijena,opis,kolicina_na_stanju,id_kategorije, proizvodjac) VALUES 
('BCAA Pak 400g',50.00,'BCAA PAK  je proizvod baziran na visokokvalitetnim BCAA amino kiselinama (L-leucin, L-izoleucin i L-valin) u optimalnom omjeru 2:1:1 , što garantuje odlične performanse  i podršku regenerativnim procesima . Velika prednost ovog proizvoda je njegova senzacionalna rastvorljivost zahvaljujući upotrebi instantizovanog oblika aminokiselina . Formula je obogaćena setom vitamina (C, B6 i B12). Zahvaljujući takvim aditivima, tijelo će u kratkom vremenu moći nadoknaditi sve nedostatke. Vrijedi naglasiti djelovanje vitamina C na imuni sistem i snižavanje nivoa kortizolašto se prevodi u povećanu razgradnju tjelesnih tkiva (uključujući misice).',10,4,'6PAK'),
('6PAK Citrulline 200g',29.00,'Upotreba citrulina utiče na zasićenje azotnog oksida u krvi , što rezultira vazodilatacijom . Veći protok krvi direktno utiče na efikasniju isporuku kiseonika i hranljivih materija do mišićnih ćelija, što vam omogućava da uložite još više napora . Također je vrijedno napomenuti da citrulin ima blagotvoran učinak na smanjenje umora . Osim toga, ima energetska svojstva i smanjuje osjećaj bolova u mišićima , zahvaljujući čemu trening može biti duži i još intenzivniji.',12,4,'6PAK'),
('Taurin 900 90Caps',15.00,'Fokus i izdržljivost tokom treninga. Podrška tokom programa mršavljenja. Čist kristalni oblik',30,4,'Trec'),
('Arginin AAKG 240g',35.00,'NO (nitric oxide) boosteri važni za izgradnju mišićne mase, a arginin je njihov glavni “igrač”. U obliku AAKG praha, ova aminokiselina postaje nezamjenjiv saveznik u metaboličkim procesima unutar ćelijske matrice – za onaj poznati osjećaj “muscle pumpa”, i to znatno duže!',17,4,'Trec'),
('Beta Alanine 700 90Caps',25.00,'Jedna porcija našeg beta-alanina sadrži 100% referentnih nutrijenata za vitamin B6? Ovaj vitamin je važan za fizički aktivne osobe jer doprinosi smanjenju umora i umora te održavanju pravilnog energetskog metabolizma.',30,4,'Trec'),
('Myprotein Impact Whey Protein 2.5kg',149.00,'Impact Whey Protein®  je bogat izvor koncentrata proteina sirutke iz vegetarijanskog slatkog sira, direktno od vodećih svjetskih proizvođača sirutke. Time što sadrži najvišu biološku vrijednost (BV), višu od bilo kojeg drugog proteina, koncentrat proteina sirutke ima visoku razinu esencijalnih i ne-esencijalnih aminokiselina. Proizvodni proces koristi jedinstvenu kombinaciju tehnologije membranske filtracije i sušenja pri niskoj temperaturi i pritisku. To osigurava precizno odvajanje i koncentraciju proteina te očuvanje prirodne funkcije i visokih prehrambenih vrijednosti čime jamčimo vrhunsku kvalitetu koncentrata proteina sirutke.',55,1,'Myprotein'),
('Mutant Whey Protein 2.27kg',55.00,'Odlična kombinacija 5 vrsta proteina sirutke bogatih bioaktivnim peptidima.\nMutant Whey by Mutant miješa 5 vrsta whey proteina, uključujući hidrolizirani protein surutke i ActiNOS®, pružajući visok sadržaj BCAA i glutamina. Kombinacija proteina Mutant Whey savršena je za brzu apsorpciju koja pogoduje oporavku i održavanju mišića.',17,1,'Mutant'),
('Gold Standard Wey 100% 2.28kg',189.00,'Whey protein izolat najčišći je oblik whey proteina, 90% proteina po težini. Oni su najčistiji i najskuplji oblik whey proteina koji postoji. Zato su smješteni među prvim sastojcima koje možete naći na Gold standard deklaraciji. Koristeći whey protein izolate kao glavne sastojke, uspješno smo spojili 24g najbržih proteina za mišićnu izgradnju u svakom serviranju- sa puno manje masti, kolesterola, laktoze i ostalih stvari koje vam ne trebaju. Nema sumnje da je ovo standard po kojem se mjere svi ostali proteini.',33,1,'Optimum Nutrition'),
('Azgard Matrix Whey Protein 3kg',145.00,'Azgard Nutrition 100% Azgard Protein Matrix je formula za izgradnju mišića dizajnirana za sve sportiste koji traže povećanje mišića, snagu, bolje performanse i brže rezultate. 100% Azgard Protein Matrix potiče prvenstveno od 2 vrste proteina surutke, najpopularnijih izvora proteina dostupnih sportistima.',50,1,'Azgard'),
('Optimum Nutrition Creatine Powder Micronized 187g',35.00,'Čisti kreatin monohidrat je bezokusan, bijeli kristalinični prah. Nalazi se u mnogim namirnicama, a u naročito velikim koncentracijama ima ga u crvenom mesu. Kreatin povećava nivo fosforokreatina u mišićima, što pak ima za posljedicu povećanje ATP-a. ATP je energetski nosioc substrata u mišićima koji im omogućuje kontrakciju i generira snagu.',3,2,'Optimum Nutrition'),
('6PAK Creatine Monohydrate 120Caps',35.00,'Kreatin iz 6PAK – je suplement najvišeg kvaliteta, bez suvišnih aditiva. Zahvaljujući tome, svaka porcija je bogat izvor čistog kreatin monohidrata . Samo 3 g dnevno je dovoljno da osjetite njegov puni potencijal. Proizvod je dostupan u obliku kapsula. Zahvaljujući njima, suplementacija je brza i bez problema.',7,2,'6PAK'),
('Muscletech Cell-Tech 1.1kg',69.00,'CELL-TECH isporučuje 7G HPLC-certificiranog kreatin monohidrata i 3G kreatina HCl koji pomaže u oporavku mišića između setova, pojačava mišićne preformanse, i gradi više mišićne mase!',14,2,'Muscletech'),
('GENIUS NUTRITION Whisper Pre-workout',69.00,'Najnovija pre-workout formula iz Genius Nutrition prava je revolucija u suplementaciji! Sadrži moćan spoj makronutrijenata koji ne samo da poboljšavaju performanse tijela već i uma, omogućavajući ti da napraviš još nekoliko ponavljanja bez gubitka fokusa.',10,3,'Genius Nutrition'),
('JACK 3D ADVANCED',65.00,'Ovaj novi, precizni kompleks cilja na bezbroj MOA-e za nevjerojatan osjećaj koji je toliko jedinstven i jak da će vam bukvalno trebati malo vremena da se pilagodite. Morate biti apsolutno sigurni da započinjete s 1 kašičicom i NIKADA ne prelazite 2 u bilo kojem trenutku.',13,3,'Jack 3D'),
('Animal Pump',145.00,'Uzimajte svaki dan 1 paketić, i to 30 – 45 minuta prije treninga, najbolje na prazan želudac. Svaki paketić sadrži potpunu dnevnu dozu kreatina, i zato se preporučuje uzimati ga svaki dan, kako biste postigli optimalne rezultate. Crvenu (stimulirajuću) kapsulu možete izostaviti u dan kada ne trenirate ili ako trenirate kasno navečer.',1,3,'Animal'),
('Trec Isotonic Sport 1000g',35.00,'ISOTONIC SPORT je dodatak ishrani za sportiste. Isotonic je formula ugljikohidrata i elektrolita koja hidrira tijelo tokom intenzivnih fizičkih vježbi.',67,5,'Trec'),
('Self Omninutrition Hydro Plus 400 g',16.50,'Izotonicno pice. Sadrzi ugljikohidrate, minerale i vitamine, uspotavlja ravnotezu minerala. Pospjesuje misicne funkcije',9,5,'Self Omninutrition'),
('Battery Zinc 90Caps',16.00,'Kontrolira rad imunoloskog sistema, sudjeluje u metabolizmu makronutrijenata, doprinosi zdravoj kozi, kosi, noktima i kostima.',32,6,'Battery'),
('IronMaxx MG Magnesium, 130Caps',27.00,'150mg magnezija po kapsuli. Sprjecava nastanak misicnih grceva. Ublazava negativne ucinke stresa.',44,6,'IronMaxx'),
('Olimp Selen 110 MCG, 120Caps',15.90,'Prehrambeni dodatak s organskim oblikom selena koji se odlicno apsorbira.',22,6,'Olimp'),
('Mutant Mass Gainer 2.27kg',85.00,'Dizajniran je za najbrže nabacivanje mišićne mase i prilagođen svakom metabolizmu!Što se proteinskog dijela tiče: sastoji se od deset različitih izvora najčistijih proteina. Uključuje kombinaciju brzodijelujućih(WHEY PROTEIN KONCENTRAT, WHEY PROTEIN IZOLAT, WHEY PROTEIN HIDROLIZAT) i sporo otpuštajućih proteina(MICELARNI KAZEIN, MLIJEČNI PROTEIN KONCENTRAT, KALCIJ KAZEINAT, ALBUMIN I MLIJEČNI PROTEIN IZOLAT).',3,7,'Mutant'),
('Optimum Nutrition Serious Mass 2.74kg',99.00,'Ozbiljno povećanje tjelesne mase zahtjeva ozbiljne mjere. Za povećanje od samo pola kilograma tjelesne mase potrebno je ekstra 3500 kalorija i preko toga što je neophodno za svakodnevno održavanje. Svakako, osobe sa visokim metabolizmom znaju da je teško konzumirati dovoljno cijelovite hrane da dostizanje ovog kalorijskog unosa. Šta još, pripremanje obroka od cijele visoko kalorične hrane nije uvijek jednostavno i lako kao mešanje nutritivno-koncetrovanog šejka.',23,7,'Optimum Nutrition'),
('Optimum Whey 1kg',75.00,'Kvalitetan whey protein',20,1,'Optimum Nutrition');



INSERT INTO zaposlenik (ime, prezime, uloga) VALUES
('Mujo', 'Mujic', 'Admin'),
('Haso', 'Hasic', 'Skladistar'),
('Fata', 'Fatic', 'Dostavljac'),
('Suljo', 'Suljic', 'Dostavljac');

INSERT INTO korpa (id_kupca) VALUES
(1),
(3),
(5),
(7),
(9);

INSERT INTO stavke_korpe (id_korpe, id_proizvoda, kolicina, cijena) VALUES
(1, 6,  1, 149.00),
(1, 10, 2, 35.00),
(2, 8,  1, 189.00),
(2, 13, 1, 69.00),
(3, 1,  3, 50.00),
(3, 18, 2, 16.00),
(4, 21, 1, 85.00),
(4, 7,  1, 156.00),
(5, 15, 2, 145.00),
(5, 4,  1, 35.00);

INSERT INTO narudzba (id_kupca, adresa_dostave, status_narudzbe, id_zaposlenika) VALUES
(1,  NULL,                                    'Isporuceno',  2),
(2,  NULL,                                    'Isporuceno',  3),
(3,  'Ferhadija 12, 71000 Sarajevo',          'Isporuceno',  3),
(4,  NULL,                                    'Potvrdjeno',  2),
(5,  'Kralja Tomislava 8, 88000 Mostar',      'Potvrdjeno',  4),
(6,  NULL,                                    'Na cekanju',  NULL),
(7,  NULL,                                    'Na cekanju',  NULL),
(8,  'Zmaja od Bosne 3, 71000 Sarajevo',      'Otkazano',    2),
(9,  NULL,                                    'Isporuceno',  3),
(10, 'Bulevar Mese Selimovica 15, 75000 Tuzla','Potvrdjeno', 4);

INSERT INTO stavke_narudzbe (id_narudzbe, id_proizvoda, kolicina, cijena) VALUES
(1,  6,  1, 149.00),
(1,  10, 2, 35.00),
(2,  8,  1, 189.00),
(2,  13, 1, 69.00),
(3,  1,  3, 50.00),
(3,  18, 2, 16.00),
(4,  21, 1, 85.00),
(4,  7,  1, 156.00),
(5,  15, 2, 145.00),
(5,  4,  1, 35.00),
(6,  11, 1, 35.00),
(6,  22, 1, 99.00),
(7,  9,  1, 145.00),
(7,  3,  2, 15.00),
(8,  14, 1, 65.00),
(9,  2,  2, 29.00),
(9,  19, 1, 27.00),
(10, 6,  1, 149.00),
(10, 20, 2, 15.90);

INSERT INTO racun (id_narudzbe, status_placanja, ukupan_iznos) VALUES
(1,  'Potvrdjeno',  219.00),
(2,  'Potvrdjeno',  258.00),
(3,  'Potvrdjeno',  182.00),
(4,  'Na cekanju',  241.00),
(5,  'Na cekanju',  325.00),
(6,  'Na cekanju',  134.00),
(7,  'Na cekanju',  175.00),
(8,  'Odbijeno',    65.00),
(9,  'Potvrdjeno',  85.90),
(10, 'Na cekanju',  180.80);