-- Crea il database (usa IF NOT EXISTS per evitare errori se esiste già)
-- CREATE DATABASE IF NOT EXISTS tomasoni_user;
-- USE tomasoni_user;
USE if0_39069812_moviedb;

-- Tabella utente
CREATE TABLE IF NOT EXISTS utente (
	username VARCHAR(20) UNIQUE,
	password VARCHAR(20),
	dataR DATE,
	PRIMARY KEY (username)
);

-- Tabella film
CREATE TABLE IF NOT EXISTS film (
	IDfilm INT,
	percorso VARCHAR(100),
	titolo VARCHAR(100),
	PRIMARY KEY (IDfilm)
);

-- Tabella preferisce
CREATE TABLE IF NOT EXISTS preferisce (
	IDfilm INT,
	username VARCHAR(20),
	FOREIGN KEY (IDfilm) REFERENCES film(IDfilm) ON DELETE CASCADE,
	FOREIGN KEY (username) REFERENCES utente(username) ON DELETE CASCADE
);

-- Tabella recensione
CREATE TABLE IF NOT EXISTS recensione (
	username VARCHAR(20),
	IDfilm INT,
	IDrecensione INT AUTO_INCREMENT,
	voto INT,
	titolo VARCHAR(50),
	testo VARCHAR(5000),
	PRIMARY KEY (IDrecensione),
	FOREIGN KEY (IDfilm) REFERENCES film(IDfilm) ON DELETE CASCADE,
	FOREIGN KEY (username) REFERENCES utente(username) ON DELETE CASCADE
);

-- Inserimento dati utente (corretto il numero di campi)
INSERT INTO utente (username,  password, dataR)
VALUES ('admin', 'adminpass', '2020-05-30');
