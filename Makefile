IMAGE = spacetabio/static-server-php
VERSION = latest
FILE = Dockerfile

image:
	sed "s/{{ version }}/$(VERSION)/g" $(FILE)
	docker build -f $(FILE) -t $(IMAGE):$(VERSION) .

push:
	docker push $(IMAGE):$(VERSION)

run:
	docker run --rm -it --init -p 8088:8080 $(IMAGE):$(VERSION)
