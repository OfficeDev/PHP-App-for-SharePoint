# PHP App for SharePoint sample

**Table of Content
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Configure the sample](#configure-the-sample)
- [Run the sample](#run-the-sample)
- [Project Files](#project-files)
- [Copyright](#copyright)

## Overview

The app model in SharePoint lets you build apps without tying you to a particular technology. You can use the language and tools of your preference to work on the components and pages that constitute your app. 

We built this sample to get you started in building an app for SharePoint that uses PHP. 

This sample is a [provider-hosted](http://msdn.microsoft.com/library/office/fp179887.aspx#SelfHosted) app for SharePoint that gets an access token by using a TokenHelper class. A PHP page uses the token to issue an authenticated request to a REST endpoint in SharePoint. 

<img alt="PHP App for SharePoint conceptual diagram" src="PHPAppSharePoint diagram.png">
Figure 1. PHP App for SharePoint conceptual diagram

Simple, but it illustrates the basic concepts that you need to know to start building apps for SharePoint using your favorite technology, PHP. Below are the components of this sample.

## Prerequisites

This sample requires the following:

- Web server with PHP version 5.3 or higher.
	- CURL library installed.
	- SSL certificate configured on the default web site.
- SharePoint site on Office 365. To get a trial site, see [Sign up for an Office 365 Developer Subscription](https://portal.office.com/Signup/Signup.aspx?OfferId=6881A1CB-F4EB-4db3-9F18-388898DAF510&DL=DEVELOPERPACK&ali=1).

## Configure the sample

You must complete the following tasks to configure the sample:

1. Create a web application in your web server.
2. Register your app in SharePoint.
3. Install the SharePoint Online SSL certificate
4. Update the *client_id*, *client_secret*, and *redirect_uri* values in the application.ini configuration file.
5. Update the *client_id* value in the app manifest.
6. Deploy the app to the SharePoint site.

### Create a web application in your web server

The sample is configured to use a web application that resolves to **PHPAppTemplateWeb**. To make it easier to configure your app we recommend that you use PHPAppTemplateWeb as your web application name. Follow this procedure to create the web application:
1. Clone or download the git repository to your web server.
2. Create a web application on your web server with a name of PHPAppTemplateWeb.
3. Point the web application to the **PHP** folder included in the sample.

### Install the SharePoint Online SSL certificate

The curl library validates the SSL certificate that other services, such as SharePoint Online, use to encrypt communication. To validate such certificate, install the certificate in a certificate store in the PHP server. In Windows, install the certificate in the Trusted Root Certification Authorities in Windows.

Alternatively, comment out the following lines of code in the TokenHelper.php and index.php files:

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

**Warning**: By commenting out the code above you set the **CURLOPT_SSL_VERIFYPEER** option to false. The curl library no longer verifies the certificate used in the communication, which can leave your server open to man-in-the-middle attacks.     

### Register your app in SharePoint

Since you're not installing the app from the [Office Store](http://office.microsoft.com/en-us/store-FX102759646.aspx), you must register your app in SharePoint before deploying it. Follow this procedure to register the app:

1. Open a browser and go to https://*yoursharepointsite*/_layouts/15/appregnew.aspx
2. Fill the following fields in the page:
	- Client Id - Click the Generate button to let SharePoint create a value for you.
	- Client Secret - Click the Generate button to let SharePoint create a value for you.
	- Title - PHPAppTemplate
	- App Domain - localhost
	- Redirect URI - https://localhost/phpapptemplateweb/index.php
3. Copy the Client Id, Client Secret, and Redirect URI values and store for future reference. You'll need them to update the configuration and app manifest files.
4. Click Create.

### Update the configuration file

The sample requires the client_id, client_secret, and redirect_uri values from the app registration page in the previous task. To update the values in the application.ini file, follow this procedure:

1. Open the **application.ini** file provided in this sample in a text editor.
2. Copy the client_id, client_secret, and redirect_uri values that you got in the previous task to the placeholders in the file.
3. Save the application.ini file.

### Update the app.manifest file

The AppTemplate.app file is an app package that contains the app manifest file. You have to update the app manifest file with the client_id value obtained in the **Register your app in SharePoint** task. Follow this procedure to update the app manifest file:

1. Rename the **AppTemplate.app** file provided in this sample to **AppTemplate.zip**
2. Open the zip file and extract the **AppManifest.xml** file.
3. Find the placeholder text *<your client_id value>* and replace with the Client Id value from the app registration page.
4. Replace the AppManifest.xml file in the zip file.
5. Rename the AppTemplate.zip file back to AppTemplate.app

### Deploy the app to the SharePoint site

You can deploy the app using the app catalog that lets administrators deploy business apps to SharePoint sites in the tenant.

1. If you don't have an app catalog site in your tenant, create one. For more information, see [Create an App Catalog site](http://office.microsoft.com/en-us/sharepoint-help/use-the-app-catalog-to-make-custom-business-apps-available-for-your-sharepoint-online-environment-HA102772362.aspx#_Toc347303048).
2. Add the AppTemplate.app file to the app catalog. For more information, see [Add custom apps to the App Catalog site](http://office.microsoft.com/en-us/sharepoint-help/use-the-app-catalog-to-make-custom-business-apps-available-for-your-sharepoint-online-environment-HA102772362.aspx#_Toc347303049).
3. Go to your SharePoint site. From the Settings menu (the gear in the top right corner of the page) choose **Add an app**.
4. Choose the PHPAppTemplate app. In the consent page, choose **Trust It**.

## Run the sample

To run the sample, simply click on the PHPAppTemplate app in the SharePoint site contents page. To find the contents page go to your site, click on the Settings menu (the gear in the top right corner of the page), and choose **Site contents**.

## Project Files

The sample includes the following components that help you configure and test the app in your environment.  

### TokenHelper class
This is a PHP class that exposes a function to get a token that you can use to issue authenticated requests. The class exposes the following members:
 
- Constructor: Initializes the values required to get the access token. Requires the SharePoint site URL and a context token. SharePoint provides both parameters when you start the app from the Site contents page in the SharePoint site. It also requires that the *client_id*, *client_secret*, and *redirect_uri* values are set in the **application.ini** configuration file.
- GetAccessToken function: Gets an access token from the token service.

### Example page
**Index.php** is an example that shows you how to use the TokenHelper class. The page does the following:

- Checks that the request is using the POST method. If the request is using GET instead it tells the user to start the app from the SharePoint site contents page.
- Initializes a TokenHelper instance with the SharePoint site URL and the context token obtained from SharePoint.
- Gets an access token using the GetAccessToken function of the TokenHelper class.
- Issues an authenticated request to a REST endpoint exposed by the SharePoint site.
- Prints the result to the browser.

### Configuration file
The TokenHelper class reads the following configuration values from the **application.ini** file:

- client_id
- client_secret
- redirect_uri

You can get this values from the app registration page in SharePoint. For more information, see [Update the configuration file](#update-the-configuration-file). 

### Example app package
The AppTemplate.app file is an app for SharePoint package that you can use to deploy and test your app.

## Copyright

Copyright (c) Microsoft. All rights reserved.
