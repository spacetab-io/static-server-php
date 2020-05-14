IMAGE = spacetabio/static-server-php
VERSION = latest
FILE = Dockerfile

image:
	docker build -f $(FILE) -t $(IMAGE):$(VERSION) . --build-arg SERVER_VERSION=$(VERSION)

push:
	docker push $(IMAGE):$(VERSION)

run:
	docker run --rm -it --init -p 8088:8080 $(IMAGE):$(VERSION)
