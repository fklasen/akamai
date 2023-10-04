# DataStream log exporter for Prometheus ingestion

## Copyright Notice
(c) Copyright 2021 Akamai Technologies, Inc. Licensed under Apache 2 license.

## Overview
This exporter is used as input for Prometheus API polling of aggregated logs from Akamai DataStream. Combine it with Grafana to visualize traffic statistics such as r/s, offload ratio and response codes in realtime.

<img width="1332" alt="258933" src="https://user-images.githubusercontent.com/51907605/132675637-d8f80337-3869-49f0-8c60-761f371fff99.png">

## Prerequisites
- A server
- PHP
- Any means of storing the stream ID's and API credential, database, external file or array in the exporter

## Note
DataStream v1 aggregated streams are slated for decommission. Raw log lines needs to be consumed in the future.
