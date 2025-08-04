# syntax=docker/dockerfile:1
# Create a base image with the python requirements installed - used for development
FROM python:3.11-slim-buster AS mlbase

ARG DEBIAN_FRONTEND=noninteractive

ENV TZ=Pacific/Auckland
ENV ML_MODELS_DIR=/etc/ml/data/models
ENV ML_LOGS_DIR=/etc/ml/data/train_logs
ENV FLASK_ENV=production
ENV ML_BIND=0.0.0.0:5000

WORKDIR /etc/ml

# Deprecated, unused
ENV ML_OFFENSIVE_LANGUAGE_LENGTH=1500
ENV ML_MODELS_GIT_HTTPS="1"

# Install GCC
RUN apt-get update && \
    apt-get --no-install-recommends install -y \
        libc6-dev \
        gcc && \
    apt-get autoremove -y && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/*

# Add the user & make them the owner of the ML directory
RUN mkdir -p /etc/ml/service && \
    useradd -mr mluser && \
    chown mluser /etc/ml

COPY requirements.txt /etc/ml/requirements.txt
COPY scripts /etc/ml/scripts
COPY start.sh /etc/ml

RUN chmod +x /etc/ml/scripts/entrypoint.sh && \
    chmod +x /etc/ml/start.sh && \
    chown mluser /etc/ml/scripts/entrypoint.sh && \
    chown mluser /etc/ml/start.sh

ENV PATH="$PATH:/home/mluser/.local/bin"
USER mluser

# Install pip
RUN pip install -U \
    pip \
    setuptools \
    wheel

# Install the packages
RUN pip install --user -r requirements.txt

# Download stopwords for this environment.
RUN python -m nltk.downloader stopwords

ENTRYPOINT [ "/bin/bash", "/etc/ml/scripts/entrypoint.sh" ]

# Built image with source - used for distribution
FROM mlbase AS mlservice

COPY ./service /etc/ml/service

EXPOSE 5000
