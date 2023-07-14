.PHONY: test-unit
test-unit:
	vendor/bin/phpunit

.PHONY: test-static
test-static:
	vendor/bin/psalm

.PHONY: test-coding-standard
test-coding-standard:
	vendor/bin/php-cs-fixer fix --verbose

.PHONY: update-doc
update-doc:
	php ./update-doc.php README.md

.PHONY: tests-all
tests-all: test-coding-standard test-unit test-static update-doc
