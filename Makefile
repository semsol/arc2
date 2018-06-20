default:
	@echo "Please run make test to start test environment."

test:
	@echo ""
	@echo "##########"
	@echo "Unit Tests"
	@echo "##########"
	@echo ""
	vendor/bin/phpunit --testsuite unit
	# run DB adapter depended test using different adapters each time
	# this allows us to check coverage using the same tests
	@echo ""
	@echo ""
	@echo "##############################################"
	@echo "mysqli adapter depended tests (Store uncached)"
	@echo "##############################################"
	@echo ""
	DB_ADAPTER=mysqli vendor/bin/phpunit --testsuite db_adapter_depended
	@echo ""
	@echo ""
	@echo "#########################################################"
	@echo "PDO adapter depended tests (Store uncached, PDO uncached)"
	@echo "#########################################################"
	@echo ""
	DB_ADAPTER=pdo DB_PDO_PROTOCOL=mysql vendor/bin/phpunit --testsuite db_adapter_depended
	@echo ""
	@echo ""
	@echo "#####################################################"
	@echo "PDO adapter depended tests (Store cached, PDO cached)"
	@echo "#####################################################"
	@echo ""
	DB_ADAPTER=pdo DB_PDO_PROTOCOL=mysql CACHE_ENABLED=true vendor/bin/phpunit --testsuite db_adapter_depended
