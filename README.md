# PHP App for SharePoint sample

**Table of Content
- [Overview](#overview)
- [Prerequisites and Configuration](#prerequisites)
- [Build](#build)
- [Project Files of Interest](#project)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Overview

The app model in SharePoint lets you build apps without tying you to a particular technology. You can use the language and tools of your preference to work on the components and pages that constitute your app. 

However, there are not enough resources or documentation to help you develop an app for SharePoint using PHP. We built this sample to get you started in building an app that uses PHP as the underlying technology. 

This sample is a provider-hosted app for SharePoint that gets an access token from SharePoint by using a TokenHelper class. A PHP page uses the token to issue an authenticated request to a REST endpoint in SharePoint. 

<image>

Simple, but it illustrates the basic concepts that you need to know to start building apps for SharePoint using your favorite technology, PHP. Below are the components of this sample.

## Prerequisites and Configuration

This sample requires the following:

- Web server with PHP version 5.3 or superior.
	- CURL library installed.
	- SSL certificate configured on the default web site.
- SharePoint site on Office 365. To get a trial site, see [Sign up for an Office 365 Developer Site](http://msdn.microsoft.com/library/office/fp179924.aspx#o365_signup "Sign up for an Office 365 Developer Site").

### Configure the sample

You must complete the following tasks to configure the sample:

1. Register your app in SharePoint.
2. Update the *client_id*, *client_secret*, and *redirect_uri* values in the application.ini configuration file.
3. Update the *client_id* value in the app manifest.
4. Deploy the app to the SharePoint site.

### Register your app in SharePoint

Since you're not installing the app from the Office Store, you must register your app in SharePoint before deploying it. Follow this procedure to register the app:

1. Open a browser and go to https://*yoursharepointsite*/_layouts/15/appregnew.aspx
2. Fill the following fields in the page:
	- Client Id - Click the Generate button to let SharePoint create a value for you.
	- Client Secret - Click the Generate button to let SharePoint create a value for you.
	- Title - Provide a title for the app.
	- App Domain - localhost
	- Redirect URI - https://localhost/eclipsetemplateweb/index.php
3. Copy the Client Id, Client Secret, and Redirect URI values and store for future reference. You'll need them to update the configuration and app manifest files.
4. Click Create.

### Update the configuration file

The sample requires the client_id, client_secret, and redirect_uri values from the app registration page in the previous task. To update the values in the application.ini file, follow this procedure:

1. Open the application.ini file provided in this sample in a text editor.
2. Copy the client_id, client_secret, and redirect_uri values that you got in the previous task to the placeholders in the file.
3. Save the application.ini file.

### Update the app.manifest file

The AppTemplate.app file is an app package that contains the app manifest file. You have to update the app manifest file with the client_id value obtained in the **Register your app in SharePoint** task. 

## Project Files of Interest

The sample includes the following components that help you configure and test the app in your environment.  

### TokenHelper class
This is a PHP class that exposes a function to get a token that you can use to issue authenticated requests. The class exposes the following members:
 
- Constructor: Initializes the values required to get the access token. Requires the SharePoint site URL and a JWT token. SharePoint provides both parameters when you start the app from the Site contents page in the SharePoint site. It also requires that the *client_id*, *client_secret*, and *redirect_uri* values are set in the **application.ini** configuration file.
- GetAccessToken function: Gets an access token from the token service.

### Example page
**Index.php** is an example that shows you how to use the TokenHelper class. The page does the following:

- Checks that the request is using the POST method. If the request is using GET instead it tells the user to start the app from the SharePoint site contents page.
- Initializes a TokenHelper instance with the SharePoint site URL and the JWT token obtained from SharePoint.
- Gets an access token using the GetAccessToken function of the TokenHelper class.
- Issues an authenticated request to a REST endpoint exposed by the SharePoint site.
- Prints the result to the browser.

### Configuration file
The TokenHelper class reads the following configuration values from the **application.ini** file:

- client_id
- client_secret
- redirect_uri

You can get this values from the app registration page in SharePoint. More information in the configuration section. 

### Example app package
The AppTemplate.app file is an app for SharePoint package that you can use to deploy and test your app.



## Troubleshooting

You may run into an authentication error after deploying and running if apps do not have the ability to access account information in the [Windows Privacy Settings](http://www.microsoft.com/security/online-privacy/windows.aspx) menu. Set **Let my apps access my name, picture, and other account info** to **On**. This setting can be reset by a Windows Update. 

Known issues as of 9/4
  - You need to use the same credentials to login with the app that were used to configure the connected service in Visual Studio. 
  - You cannot switch users when using the app.

## Copyright

Copyright (c) Microsoft. All rights reserved.

