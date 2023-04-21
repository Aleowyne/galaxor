DROP DATABASE IF EXISTS galaxor;
CREATE DATABASE IF NOT EXISTS galaxor COLLATE 'utf8mb4_unicode_ci';

USE galaxor;

CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(100) NOT NULL,
  mail_address VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS universe (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS galaxy (
  id INT AUTO_INCREMENT,
  universe_id INT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id, universe_id),
  FOREIGN KEY (universe_id) REFERENCES universe(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS solar_system (
  id INT AUTO_INCREMENT,
  galaxy_id INT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id, galaxy_id),
  FOREIGN KEY (galaxy_id) REFERENCES galaxy(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS resource (
  id TINYINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS planet_size (
  position TINYINT PRIMARY KEY,
  size SMALLINT NOT NULL
);

CREATE TABLE IF NOT EXISTS position_bonus (
  position TINYINT,
  resource_id TINYINT,
  bonus TINYINT NOT NULL,
  PRIMARY KEY (position, resource_id),
  FOREIGN KEY (position) REFERENCES planet_size(position),
  FOREIGN KEY (resource_id) REFERENCES resource(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS planet (
  id INT AUTO_INCREMENT,
  solar_system_id INT,
  name VARCHAR(50) NOT NULL,
  position TINYINT NOT NULL,
  user_id INT,
  PRIMARY KEY (id, solar_system_id),
  FOREIGN KEY (solar_system_id) REFERENCES solar_system(id) ON DELETE CASCADE,
  FOREIGN KEY (position) REFERENCES planet_size(position),
  FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS item (
  id VARCHAR(10) PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type enum('STRUCTURE', 'RECHERCHE', 'UNITE'),
  build_time VARCHAR(255) NOT NULL,
  attack_point VARCHAR(255) NOT NULL,
  defense_point VARCHAR(255) NOT NULL,
  freight_capacity VARCHAR(255) NOT NULL,
  img_filename VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS item_cost (
  item_id VARCHAR(10),
  resource_id TINYINT,
  quantity VARCHAR(255) NOT NULL,
  PRIMARY KEY (item_id, resource_id),
  FOREIGN KEY (item_id) REFERENCES item(id) ON DELETE CASCADE,
  FOREIGN KEY (resource_id) REFERENCES resource(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS item_prerequisite (
  item_id VARCHAR(10),
  required_item_id VARCHAR(10),
  level TINYINT NOT NULL,
  PRIMARY KEY (item_id, required_item_id),
  FOREIGN KEY (item_id) REFERENCES item(id) ON DELETE CASCADE,
  FOREIGN KEY (required_item_id) REFERENCES item(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS item_production (
  item_id VARCHAR(10),
  resource_id TINYINT,
  production VARCHAR(255) NOT NULL,
  PRIMARY KEY (item_id, resource_id),
  FOREIGN KEY (item_id) REFERENCES item(id) ON DELETE CASCADE,
  FOREIGN KEY (resource_id) REFERENCES resource(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS planet_resource (
  planet_id INT,
  resource_id TINYINT,
  quantity INT NOT NULL,
  last_time_calc TIMESTAMP NOT NULL,
  PRIMARY KEY (planet_id, resource_id),
  FOREIGN KEY (planet_id) REFERENCES planet(id) ON DELETE CASCADE,
  FOREIGN KEY (resource_id) REFERENCES resource(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS planet_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  planet_id INT NOT NULL,
  item_id VARCHAR(10) NOT NULL,
  level TINYINT NOT NULL,
  time_end_upgrade TIMESTAMP NOT NULL,
  FOREIGN KEY (planet_id) REFERENCES planet(id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES item(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS fight (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attack_planet INT NOT NULL,
  defense_planet INT NOT NULL,
  time_fight TIMESTAMP NOT NULL,
  victory BOOLEAN NOT NULL,
  FOREIGN KEY (attack_planet) REFERENCES planet(id),
  FOREIGN KEY (defense_planet) REFERENCES planet(id)
);

CREATE TABLE IF NOT EXISTS fight_item (
  fight_id INT,
  item_id INT,
  PRIMARY KEY (fight_id, item_id),
  FOREIGN KEY (fight_id) REFERENCES fight(id),
  FOREIGN KEY (item_id) REFERENCES planet_item(id)
);


INSERT INTO resource(name) VALUES 
  ('Métal'), ('Deutérium'), ('Energie'); 

INSERT INTO planet_size(position, size) VALUES
  (1, 90), (2, 100), (3, 110), (4, 120), (5, 130), 
  (6, 130), (7, 120), (8, 110), (9, 100), (10, 90);

INSERT INTO position_bonus(position, resource_id, bonus) VALUES
  (1, 1, 0), (2, 1, 5), (3, 1, 10), (4, 1, 15), (5, 1, 20),
  (6, 1, 15), (7, 1, 10), (8, 1, 5), (9, 1, 0), (10, 1, -5),
  (1, 2, -15), (2, 2, -10), (3, 2, -5), (4, 2, 0), (5, 2, 0),
  (6, 2, 10), (7, 2, 15), (8, 2, 20), (9, 2, 25), (10, 2, 30),
  (1, 3, 30), (2, 3, 20), (3, 3, 10), (4, 3, 5), (5, 3, 0),
  (6, 3, 0), (7, 3, -10), (8, 3, -20), (9, 3, -30), (10, 3, -40);

INSERT INTO item(id, name, type, build_time, attack_point, defense_point, freight_capacity, img_filename) VALUES
  ('LABO', 'Laboratoire de recherche', 'STRUCTURE', '(50*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('CHANTIER', 'Chantier spatial', 'STRUCTURE', '(50*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('USINE_NANI', 'Usine de nanites', 'STRUCTURE', '(600*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('MINE', 'Mine de métal', 'STRUCTURE', '(10*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('DEUTERIUM', 'Synthétiseur de deutérium', 'STRUCTURE', '(25*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('CENTR_SOL', 'Centrale solaire', 'STRUCTURE', '(10*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('CENTR_FUS', 'Centrale à fusion', 'STRUCTURE', '(120*2^L)(1-TECH_IA/100)^USINE_NANI', '0', '0', '0', 'none.jpg'),
  ('LASER', 'Artillerie laser', 'STRUCTURE', '10', '(100*1.05^L)*1.03^TECH_ARME', '25*1.05^TECH_BOUC', '0', 'none.jpg'),
  ('CANON_IONS', 'Canon à ions', 'STRUCTURE', '40', '(250*1.05^L)*1.03^TECH_ARME', '200*1.05^TECH_BOUC', '0', 'none.jpg'),
  ('BOUCLIER', 'Bouclier', 'STRUCTURE', '60*2^L', '0', '(2000*1.3^BOUCLIER)*1.05^TECH_BOUC', '0', 'none.jpg'),
  ('TECH_NRJ', 'Technologie Energie', 'RECHERCHE', '4*2^L*0.95^LABO', '0', '0', '0', 'none.jpg'),
  ('TECH_LASER', 'Technologie Laser', 'RECHERCHE', '2*2^L*0.95^LABO', '0', '0', '0', 'none.jpg'),
  ('TECH_IONS', 'Technologie Ions', 'RECHERCHE', '8*2^L*0.95^LABO', '0', '0', '0', 'none.jpg'),
  ('TECH_BOUC', 'Technologie Bouclier', 'RECHERCHE', '5*2^L*0.95^LABO', '0', '0', '0', 'none.jpg'),
  ('TECH_ARME', 'Technologie Armement', 'RECHERCHE', '6*2^L*0.95^LABO', '0', '0', '0', 'none.jpg'),
  ('TECH_IA', 'Technologie Intelligence Artificielle', 'RECHERCHE', '10*2^L*0.95^LABO', '0', '0', '0', 'none.jpg'),
  ('CHASSEUR', 'Chasseur', 'UNITE', '20*0.95^CHANTIER', '75*1.03^TECH_ARME', '50*1.05^TECH_BOUC', '0', 'none.jpg'),
  ('CROISEUR', 'Croiseur', 'UNITE', '120*0.95^CHANTIER', '400*1.03^TECH_ARME', '150*1.05^TECH_BOUC', '0', 'none.jpg'),
  ('TRANSP', 'Transporteur', 'UNITE', '55*0.95^CHANTIER', '0', '50*1.05^TECH_BOUC', '100000', 'none.jpg'),
  ('COLONIE', 'Vaisseau de colonisation', 'UNITE', '120*0.95^CHANTIER', '0', '50*1.05^TECH_BOUC', '0', 'none.jpg');

INSERT INTO item_cost(item_id, resource_id, quantity) VALUES
  ('LABO', 1, '1000*1.6^L'),
  ('LABO', 3, '500*1.6^L'),
  ('CHANTIER', 1, '500*1.6^L'),
  ('CHANTIER', 3, '500*1.6^L'),
  ('USINE_NANI', 1, '10000*1.6^L'),
  ('USINE_NANI', 3, '5000*1.6^L'),
  ('MINE', 1, '100*1.6^L'),
  ('MINE', 3, '10*1.6^L'),
  ('DEUTERIUM', 1, '200*1.6^L'),
  ('DEUTERIUM', 3, '50*1.6^L'),
  ('CENTR_SOL', 1, '150*1.6^L'),
  ('CENTR_SOL', 2, '20*1.6^L'),
  ('CENTR_FUS', 1, '5000*1.6^L'),
  ('CENTR_FUS', 2, '2000*1.6^L'),
  ('LASER', 1, '1500'),
  ('LASER', 2, '300'),
  ('CANON_IONS', 1, '5000'),
  ('CANON_IONS', 2, '1000'),
  ('BOUCLIER', 1, '10000*1.5^L'),
  ('BOUCLIER', 2, '5000*1.5^L'),
  ('BOUCLIER', 3, '1000*1.5^L'),
  ('TECH_NRJ', 2, '100'),
  ('TECH_LASER', 2, '300'),
  ('TECH_IONS', 2, '500'),
  ('TECH_BOUC', 2, '1000'),
  ('TECH_ARME', 2, '500'),
  ('TECH_IA', 2, '2000'),
  ('CHASSEUR', 1, '3000'),
  ('CHASSEUR', 2, '500'),
  ('CROISEUR', 1, '20000'),
  ('CROISEUR', 2, '5000'),
  ('TRANSP', 1, '6000'),
  ('TRANSP', 2, '1500'),
  ('COLONIE', 1, '10000'),
  ('COLONIE', 2, '10000');

INSERT INTO item_prerequisite(item_id, required_item_id, level) VALUES
  ('USINE_NANI', 'TECH_IA', 5),
  ('CENTR_FUS', 'TECH_NRJ', 10),
  ('LASER', 'TECH_LASER', 1),
  ('CANON_IONS', 'TECH_IONS', 1),
  ('BOUCLIER', 'TECH_BOUC', 1),
  ('TECH_NRJ', 'LABO', 1),
  ('TECH_LASER', 'LABO', 1),
  ('TECH_LASER', 'TECH_NRJ', 5),
  ('TECH_IONS', 'LABO', 1),
  ('TECH_IONS', 'TECH_LASER', 5),
  ('TECH_BOUC', 'LABO', 1),
  ('TECH_BOUC', 'TECH_NRJ', 8),
  ('TECH_BOUC', 'TECH_IONS', 2),
  ('TECH_ARME', 'LABO', 1),
  ('TECH_IA', 'LABO', 1),
  ('CHASSEUR', 'CHANTIER', 1),
  ('CROISEUR', 'CHANTIER', 1),
  ('CROISEUR', 'TECH_IONS', 4),
  ('TRANSP', 'CHANTIER', 1),
  ('COLONIE', 'CHANTIER', 1);

INSERT INTO item_production(item_id, resource_id, production) VALUES
  ('MINE', 1, '(3*1.5^(L-1))*(1+BONUS/100)'),
  ('DEUTERIUM', 2, '(1*1.5^(L-1))*(1+BONUS/100)'),
  ('CENTR_SOL', 3, '20*(1+BONUS/100)'),
  ('CENTR_FUS', 3, '50*(1+BONUS/100)');
