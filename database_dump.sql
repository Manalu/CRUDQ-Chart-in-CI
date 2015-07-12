-- Table structure for table `unidades_organizacionais`
CREATE TABLE IF NOT EXISTS unidades_organizacionais ( id bigint(20) NOT NULL AUTO_INCREMENT, name varchar(255) NOT NULL,
cnpj varchar(14) DEFAULT NULL,
PRIMARY KEY (id),
UNIQUE KEY `BY_CNPJ_IX` (cnpj) );

-- Table structure for table `unidades_organizacionais_tree_paths`
CREATE TABLE IF NOT EXISTS unidades_organizacionais_tree_paths ( ancestor bigint(20) NOT NULL,
descendant bigint(20) NOT NULL,
PRIMARY KEY (ancestor, descendant),
FOREIGN KEY (ancestor) REFERENCES unidades_organizacionais(id), FOREIGN KEY (descendant) REFERENCES unidades_organizacionais(id)
);