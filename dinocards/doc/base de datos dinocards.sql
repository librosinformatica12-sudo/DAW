create database if not exists dinocards;
use dinocards;

-- 1. tabla: usuarios
create table if not exists usuarios (
    id int auto_increment primary key,
    username varchar(64) unique null,
    email varchar(255) unique null,
    password_hash varchar(255) null,
    fecha_registro datetime null,
    ultima_obtencion datetime null
);

-- 2. tabla: dinosaurios
create table if not exists dinosaurios (
    id int auto_increment primary key,
    nombre varchar(64) unique null,
    especie varchar(128) null,
    periodo varchar(128) null,
    imagen_url varchar(128) null,
    altura decimal(4,2) null,
    largo decimal(4,2) null,
    peso decimal(6,3) null,
    hp int null,
    vigor int null,
    ataque int null,
    defensa int null,
    agilidad int null
);

-- 3. tabla: colecciones
create table if not exists colecciones (
    id int auto_increment primary key,
    usuario_id int null,
    dinousuario_id int null,
    fecha_adquisicion datetime null,
    constraint fk_colecciones_usuario foreign key (usuario_id) references usuarios(id) on delete set null,
    constraint fk_colecciones_dinosaurio foreign key (dinousuario_id) references dinosaurios(id) on delete set null
);