IMAGE = spacetabio/static-server-php
VERSION = 3.2.1
FILE = Dockerfile

image:
	sed "s/{{ version }}/$(VERSION)/g" $(FILE) > /tmp/$(FILE)
	docker build -f /tmp/$(FILE) -t $(IMAGE):$(VERSION) .

push:
	docker push $(IMAGE):$(VERSION)

run:
	docker run --rm -it --init -p 8088:8080 $(IMAGE):$(VERSION)

test:
	docker run -w /app --entrypoint bash --rm -it --init -v `pwd`/:/app \
		spacetabio/static-server-php:3.1.0-xdebug -c "vendor/bin/phpunit"
