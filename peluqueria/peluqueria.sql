CREATE DATABASE peluqueria;
drop database peluqueria;

USE peluqueria;

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    telefono VARCHAR(15)
);

CREATE TABLE peluqueros (
    id_peluquero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    especialidad VARCHAR(50)
);

CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_peluquero INT,
    fecha_hora DATETIME,
    servicio VARCHAR(50),

    FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente),

    FOREIGN KEY (id_peluquero)
        REFERENCES peluqueros(id_peluquero)
);

INSERT INTO clientes (nombre, telefono)
VALUES
('Carlos', '600111222'),
('Laura', '600333444');

INSERT INTO peluqueros (nombre, especialidad)
VALUES
('Pedro', 'Corte'),
('Ana', 'Color');

INSERT INTO reservas
(id_cliente, id_peluquero, fecha_hora, servicio)
VALUES
(1, 1, '2026-11-15 10:00:00', 'Corte de pelo'),
(2, 2, '2026-11-15 11:30:00', 'Tinte');


delimiter $$

-- Si el procedimiento ya existía, se borra para poder crearlo de nuevo.
drop procedure if exists AltaReserva$$

create procedure AltaReserva(
    in id_cliente int,   			-- ENTRADA: nombre del producto
    in id_peluquero int,           -- ENTRADA: precio del producto
    in fecha_hora timestamp,
	in servicio	VARCHAR(50),									-- ENTRADA: unidades disponibles
    out _res int                       -- SALIDA: código de resultado (ver arriba)
)
begin
    -- Validación 1: el nombre no puede estar vacío ni ser nulo
    if (_nombre_producto LIKE "" or _nombre_producto is null) then
        set _res = -2;  -- Nombre vacío

    -- Validación 2: el precio no puede ser nulo ni menor de 1 €
    elseif (_precio IS NULL or _precio < 1) then
        set _res = -3;  -- Precio no válido

    -- Validación 3: el stock no puede ser nulo ni menor de 1 unidad
    elseif (_stock IS NULL or _stock < 1) then
        set _res = -4;  -- Stock no válido

    -- Si pasa todas las validaciones, se guarda el producto
    else
        insert into RegistrarProductos(nombre_producto, precio, stock)
        values (_nombre_producto, _precio, _stock);
        set _res = 0;   -- Todo correcto
    end if;
end$$

-- Volvemos a poner ";" como delimitador normal.
delimiter ;