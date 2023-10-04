# Configure image collections with the Image Manager API

## Copyright Notice
(c) Copyright 2021 Akamai Technologies, Inc. Licensed under Apache 2 license.

## Overview
[Akamai Image Manager](https://www.akamai.com/products/image-and-video-manager) is a cloud service to transform and optimize images for end-user delivery. You may use the [image viewer](https://developer.akamai.com/image-manager/media-viewer) to showcase your images in the browser.

The image viewer supports usage of [collections](https://learn.akamai.com/en-us/webhelp/image-manager/image-manager/GUID-779AFD90-E367-42C3-BF47-EC50AE90F614.html) of images or videos and this code example is an admin page to create and publish image collection by using the [Image Manager API](https://developer.akamai.com/api/web_performance/image_manager/v2.html).

## Prerequisite
- The script uses a database to store image information and collection information to enable collection creation and publishing via a UI. The schema for the two tables are can be found in the php-code.
- Make sure to download the latest jQuery scripts appropriately before use.
