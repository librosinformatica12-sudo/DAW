use sakila;

delimiter $$
create procedure Titulos()
begin

	select title from film;


end$$
delimiter ;

call Titulos;