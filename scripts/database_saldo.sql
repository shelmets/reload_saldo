create database if not exists saldo_lab;

use saldo_lab;

create table if not exists charges (id_charge int UNSIGNED NOT NULL AUTO_INCREMENT,
number_flat int UNSIGNED NOT NULL, 
date date NOT NULL,
cash int UNSIGNED NOT NULL, 
PRIMARY KEY (id_charge));

create table if not exists payments (id_payment int UNSIGNED NOT NULL AUTO_INCREMENT, 
number_flat int UNSIGNED NOT NULL, 
date date NOT NULL, 
cash int UNSIGNED NOT NULL, 
PRIMARY KEY (id_payment));

create table if not exists saldo (id_saldo int NOT NULL AUTO_INCREMENT, 
number_flat int UNSIGNED NOT NULL, 
month date NOT NULL,
payment int UNSIGNED NOT NULL,
charge int UNSIGNED NOT NULL,
PRIMARY KEY (id_saldo));


