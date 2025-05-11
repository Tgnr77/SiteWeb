create database if not exists cours_data_base;
use cours_data_base;
create table Clients (
id_client INT auto_increment Primary key, 
nom VARCHAR(100) NOT NULL,
date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP  );
INSERT INTO clients (nom, date_inscription) VALUES ('Amine', '2025-03-04');
SET SQL_SAFE_UPDATES = 0;
UPDATE clients SET nom = 'Durand' WHERE nom = 'Amine';
DELETE FROM clients WHERE nom = 'Durand';
Select * from Clients; 
SELECT * FROM clients WHERE nom = 'Paulo';
SELECT * FROM clients WHERE date_inscription = '2023-01-01';
SELECT nom, date_inscription FROM Clients
ORDER BY id_client ASC;
SELECT * FROM clients ORDER BY date_inscription DESC;
SELECT COUNT(*) AS nombre_total_clients FROM clients;
SELECT nom, date_inscription FROM clients;
SELECT id_client, nom, date_inscription AS date_ajout_client FROM clients;
SELECT * FROM clients ORDER BY date_inscription DESC LIMIT 5;
SELECT * FROM clients ORDER BY date_inscription ASC LIMIT 1;
SELECT * FROM clients WHERE date_inscription BETWEEN '2022-01-01' AND '2023-01-01';
SELECT * FROM clients WHERE id_client IN (1, 3, 5);
SELECT * FROM clients WHERE nom LIKE 'D%';
SELECT MAX(id_client) FROM clients;
SELECT ROUND(123.456);
SELECT YEAR(date_inscription) AS annee, COUNT(*) AS nombre_clients
FROM clients
GROUP BY YEAR(date_inscription);
CREATE TABLE commandes (
    id_commande INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    montant DECIMAL(10,2),
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
);

SELECT clients.nom, commandes.montant
FROM clients
JOIN commandes ON clients.id_client = commandes.id_client;












