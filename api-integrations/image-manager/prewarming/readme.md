# Prewarm images to reduce latency

## Copyright Notice
(c) Copyright 2021 Akamai Technologies, Inc. Licensed under Apache 2 license.

## Overview
[Akamai Image Manager](https://www.akamai.com/products/image-and-video-manager) runs processes over a complex architecture that induces certain latencies for very first request hits of a new unseen image. The type of logic of this script utilizes can be used during content releases or cache purging to trigger parts of this processing to lower the effect of cold/missed hits giving the end-user a better experience.
