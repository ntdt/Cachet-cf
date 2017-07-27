FROM ubuntu:latest

RUN apt update && apt install -y unzip ruby ruby-dev libssl-dev build-essential  && rm -rf /var/lib/apt/lists/*
RUN gem install cf-uaa-lib

