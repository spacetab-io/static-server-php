IMAGE = spacetabio/static-server-php
VERSION = 4.0.0
FILE = Dockerfile

image:
	sed "s/{{ version }}/$(VERSION)/g" $(FILE) > /tmp/$(FILE)
	docker build -f /tmp/$(FILE) -t $(IMAGE):$(VERSION) .

push:
	docker push $(IMAGE):$(VERSION)

run:
	docker run --rm -it --init -p 8088:8080 $(IMAGE):$(VERSION)

test:
	docker run -w /app --entrypoint sh --rm -it --init -v `pwd`/:/app \
		$(IMAGE):4.0.0-xdebug -c "vendor/bin/phpunit"

image_test:
	docker build -t $(IMAGE):4.0.0-xdebug -f test.dockerfile .

push_test:
	docker build -t $(IMAGE):4.0.0-xdebug -f test.dockerfile .

image_box:
	docker build -t spacetabio/box-php:1.0.0 -f box.dockerfile .

push_box:
	docker push spacetabio/box-php:1.0.0
