use sakila;
-- Procedimineto almacenado que devuela el numero total de alquileres que a hecha la compañia.
select * from payment;
use sakila;

drop procedure if exists TotalAlquileres;

delimiter $$
create procedure TotalAlquileres()
begin

select count(*) as total_alquileres from rental;

end $$
delimiter ;
call TotalAlquileres();


-- Procedimiento Almacenado que deuelva a los actores que se apelliden de una determinada forma.
use sakira;
drop procedure if exists actores_apellidan;

delimiter $$
select * from actor;
delimiter $$
create procedure ActoresApellidos(in p_last_name varchar(45))
begin
	select first_name, last_name from actor where last_name = p_last_name;
end $$

delimiter ;
call actores_apellidan('DAVIS');

-- Procedimiento Almacenado que devuelva a las peliculas en las que haya participado un actor determinado.

use sakira;
select * from film;
select * from film_actor;
select * from actor;

drop procedure if exists nombre_pelis;

delimiter $$

create procedure nombre_pelis(in nombre_actor varchar(25))
begin
    select f.title
    from film f
    inner join film_actor fa on fa.film_id = f.film_id
    inner join actor a       on a.actor_id = fa.actor_id
    where a.first_name = nombre_actor;
end$$

delimiter ;

call nombre_pelis('PENELOPE');


-- Procedimiento Almacenado que devuelva el dinero generado por un empleado concreto.

use sakira;

select * from film;
select * from payment;

drop procedure if exists dinero_total_empleado;

delimiter $$
create procedure dinero_total_empleado(
    in p_staff_id tinyint,
    out p_total_dinero decimal(7,2)
)
begin
    select sum(amount) into p_total_dinero
    from payment
    where staff_id = p_staff_id;
end $$
delimiter ;

-- 1. Llamamos al procedimiento
call dinero_total_empleado(1, @total);
-- 2. Mostramos el resultado
select @total as dinero_generado;







