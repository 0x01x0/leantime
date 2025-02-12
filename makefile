VERSION := $(shell grep "appVersion" ./config/appSettings.php |awk -F' = ' '{print substr($$2,2,length($$2)-3)}')
TARGET_DIR:= ./target/leantime
install-deps:
	npm install
	composer install --no-dev --optimize-autoloader

build-js: install-deps
	./node_modules/.bin/grunt Build-All

build: install-deps build-js
	mkdir -p $(TARGET_DIR)
	cp -R ./app $(TARGET_DIR)
	cp -R ./bin $(TARGET_DIR)
	cp -R ./config $(TARGET_DIR)
	cp -R ./logs $(TARGET_DIR)
	cp -R ./public $(TARGET_DIR)
	cp -R ./userfiles $(TARGET_DIR)
	cp -R ./vendor $(TARGET_DIR)
	cp  ./.htaccess $(TARGET_DIR)
	cp  ./LICENSE $(TARGET_DIR)
	cp  ./nginx*.conf $(TARGET_DIR)
	cp  ./updateLeantime.sh $(TARGET_DIR)

	rm $(TARGET_DIR)/config/configuration.php
	#Remove font for QR code generator (not needed if no label is used)
	rm -f $(TARGET_DIR)/vendor/endroid/qr-code/assets/fonts/noto_sans.otf

	#Remove DeepL.com and mltranslate engine (not needed in production)
	rm -rf $(TARGET_DIR)/vendor/mpdf/mpdf/ttfonts
	rm -rf $(TARGET_DIR)/vendor/lasserafn/php-initial-avatar-generator/src/fonts
	rm -rf $(TARGET_DIR)/vendor/lasserafn/php-initial-avatar-generator/tests/fonts

	#Remove local configuration, if any
	rm -rf $(TARGET_DIR)/custom/*/*
	rm -rf $(TARGET_DIR)/public/theme/*/css/custom.css

	#Remove userfiles
	rm -rf $(TARGET_DIR)/userfiles/*
	rm -rf $(TARGET_DIR)/public/userfiles/*

	#Removing unneeded items for release
	rm -R $(TARGET_DIR)/public/images/Screenshots

	#removing js directories
	find  $(TARGET_DIR)/app/domain/ -maxdepth 2 -name "js" -exec rm -r {} \;

    #removing uncompiled js files
	find $(TARGET_DIR)/public/js/ -mindepth 1 ! -name "*compiled*" -exec rm -f -r {} \;

package:
	cd target && zip -r -X "Leantime-v$(VERSION)$$1.zip" leantime
	cd target && tar -zcvf "Leantime-v$(VERSION)$$1.tar.gz" leantime

clean:
	rm -rf $(TARGET_DIR)

.PHONY: install-deps build-js build package clean
