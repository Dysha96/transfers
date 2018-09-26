ds:
	sudo	service	nginx	stop;
	sudo	service	mysql	stop;
	docker-compose	up  --build  -d
	docker	exec	transfers_app_1	composer	install
	docker	exec	transfers_app_1	php	artisan 	migrate:fresh	--seed
	docker	exec	transfers_app_1	php	artisan 	config:cache
	docker	exec	transfers_app_1	php	artisan 	config:clear
	docker	exec	transfers_app_1	chmod 777 -R storage/