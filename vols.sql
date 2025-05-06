-- Créer la base de données
CREATE DATABASE IF NOT EXISTS zenith_airlines;

-- Utiliser la base de données
USE zenith_airlines;

-- Table des vols
-- CREATE TABLE vols (
    -- id_vol INT AUTO_INCREMENT PRIMARY KEY,
    -- origine VARCHAR(100) NOT NULL,
   -- destination VARCHAR(100) NOT NULL,
  --  date_depart DATETIME NOT NULL,
  --  date_arrivee DATETIME NOT NULL,
 --   prix DECIMAL(10, 2) NOT NULL,
 --   statut VARCHAR(50) DEFAULT 'à l\'heure',
 --   compagnie VARCHAR(100) NOT NULL
-- );

-- Table des utilisateurs
-- CREATE TABLE utilisateurs (
  -- id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
  --  nom VARCHAR(100) NOT NULL,
  --  email VARCHAR(100) NOT NULL UNIQUE,
 --   mot_de_passe VARCHAR(255) NOT NULL,
 --   telephone VARCHAR(20),
  --  date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
 -- );

-- Table des réservations
-- CREATE TABLE reservations (
--    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
   -- id_vol INT NOT NULL,
--    id_utilisateur INT NOT NULL,
 --   date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP,
 --   statut VARCHAR(50) DEFAULT 'confirmé',
 --   FOREIGN KEY (id_vol) REFERENCES vols(id_vol),
 --   FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur)
-- );

-- INSERT INTO vols (origine, destination, date_depart, date_arrivee, prix, statut, compagnie)
-- VALUES
-- ('Paris', 'New York', '2025-02-01 10:00:00', '2025-02-01 14:00:00', 450.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Tokyo', 'Paris', '2025-02-05 15:30:00', '2025-02-05 22:00:00', 650.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Londres', 'Madrid', '2025-02-10 08:00:00', '2025-02-10 10:30:00', 120.00, 'retardé', 'Zenith Airlines');

 -- INSERT INTO utilisateurs (nom, email, mot_de_passe, telephone)
-- VALUES
-- ('Alice Dupont', 'alice.dupont@example.com', 'hashed_password_1', '0601020304'),
-- ('Jean Martin', 'jean.martin@example.com', 'hashed_password_2', '0612345678');

-- INSERT INTO reservations (id_vol, id_utilisateur, statut)
-- VALUES
-- (1, 1, 'confirmé'),
-- (2, 2, 'confirmé');

-- SHOW CREATE TABLE reservations;

-- INSERT INTO vols (origine, destination, date_depart, date_arrivee, prix, statut, compagnie)
-- VALUES
-- Vols en Europe
-- ('Paris', 'Londres', '2025-02-01 08:00:00', '2025-02-01 09:30:00', 120.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Berlin', 'Madrid', '2025-02-02 14:00:00', '2025-02-02 17:00:00', 150.00, 'retardé', 'Zenith Airlines'),
-- ('Rome', 'Athènes', '2025-02-03 12:00:00', '2025-02-03 14:30:00', 100.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Amsterdam', 'Vienne', '2025-02-04 09:00:00', '2025-02-04 11:00:00', 90.00, 'à l\'heure', 'Zenith Airlines'),

-- Vols intercontinentaux
-- ('Paris', 'New York', '2025-02-10 10:00:00', '2025-02-10 14:00:00', 450.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Tokyo', 'Paris', '2025-02-12 15:30:00', '2025-02-12 22:30:00', 700.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Londres', 'Dubai', '2025-02-15 18:00:00', '2025-02-15 23:30:00', 400.00, 'annulé', 'Zenith Airlines'),
-- ('Los Angeles', 'Sydney', '2025-02-20 22:00:00', '2025-02-21 07:00:00', 850.00, 'à l\'heure', 'Zenith Airlines'),

-- Vols domestiques
-- ('Paris', 'Marseille', '2025-02-05 07:00:00', '2025-02-05 08:30:00', 60.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Lyon', 'Nice', '2025-02-06 13:00:00', '2025-02-06 14:15:00', 70.00, 'retardé', 'Zenith Airlines'),
-- ('Bordeaux', 'Toulouse', '2025-02-07 18:00:00', '2025-02-07 18:45:00', 50.00, 'à l\'heure', 'Zenith Airlines'),

-- Vols en Asie
-- ('Tokyo', 'Hong Kong', '2025-02-18 12:00:00', '2025-02-18 16:00:00', 300.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Shanghai', 'Bangkok', '2025-02-19 09:00:00', '2025-02-19 12:30:00', 250.00, 'à l\'heure', 'Zenith Airlines'),

-- Vols en Amérique
-- ('New York', 'Chicago', '2025-02-14 08:00:00', '2025-02-14 10:30:00', 200.00, 'à l\'heure', 'Zenith Airlines'),
-- ('Miami', 'Los Angeles', '2025-02-16 15:00:00', '2025-02-16 19:00:00', 350.00, 'retardé', 'Zenith Airlines');

-- ALTER TABLE utilisateurs
-- ADD COLUMN prenom VARCHAR(100) NOT NULL AFTER nom;
-- ALTER TABLE utilisateurs
-- ADD COLUMN token VARCHAR(255) NULL;

-- Table Panier
 -- CREATE TABLE panier (
   -- id_panier INT AUTO_INCREMENT PRIMARY KEY,
   -- id_utilisateur INT NOT NULL,
   -- id_vol INT NOT NULL,
   -- quantite INT DEFAULT 1,
   -- date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   -- FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur),
   -- FOREIGN KEY (id_vol) REFERENCES vols(id_vol)
-- );

-- ALTER TABLE utilisateurs ADD COLUMN token_expire DATETIME NULL;





